<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use App\Models\UnitStage;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

use App\Http\Requests\Stage\UpdateStageRequest;

class StageController extends Controller
{
    #[OA\Get(
        path: "/api/units/{unitId}/stages",
        summary: "List Stages for Unit",
        tags: ["Stages"],
        security: [["sanctum" => []]],
        parameters: [
            new OA\Parameter(name: "unitId", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "List of stages",
                content: new OA\JsonContent(type: "array", items: new OA\Items(ref: "#/components/schemas/UnitStage"))
            )
        ]
    )]
    public function index(Unit $unit)
    {
        return $unit->stages()
            ->join('stage_templates', 'unit_stages.stage_template_id', '=', 'stage_templates.id')
            ->select('unit_stages.*')
            ->orderBy('stage_templates.stage_number')
            ->with('template')
            ->get();
    }

    #[OA\Put(
        path: "/api/stages/{id}",
        summary: "Update Stage Status",
        tags: ["Stages"],
        security: [["sanctum" => []]],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["status"],
                properties: [
                    new OA\Property(property: "status", type: "string", enum: ["pending", "in_progress", "completed"])
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Stage Updated",
                content: new OA\JsonContent(ref: "#/components/schemas/UnitStage")
            )
        ]
    )]
    public function update(UpdateStageRequest $request, UnitStage $stage)
    {
        $validated = $request->validated();
        $oldStatus = $stage->status;

        if (in_array($validated['status'], ['in_progress', 'completed'])) {
            $previousStage = \App\Services\StageService::getPreviousStage($stage);
            if ($previousStage && $previousStage->status !== 'completed') {
                $prevTitle = $previousStage->template->title;
                $prevNum = $previousStage->template->stage_number;
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'status' => ["Cannot start or complete this stage. Stage $prevNum ($prevTitle) must be completed first."]
                ]);
            }
        }

        // Reversion Protection
        if ($oldStatus === 'completed' && in_array($validated['status'], ['pending', 'in_progress'])) {
            $blockReason = \App\Services\StageService::getSubsequentWorkInfo($stage);
            if ($blockReason) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'status' => ["Cannot mark stage as incomplete because $blockReason."]
                ]);
            }
        }

        $updateData = [
            'status' => $validated['status'],
            'completed_at' => $validated['status'] === 'completed' ? now() : null,
        ];

        if ($validated['status'] === 'in_progress' && !$stage->started_at) {
            $updateData['started_at'] = now();
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($stage, $updateData, $oldStatus, $validated) {
            $stage->update($updateData);

            // Auto-complete tasks if completing
            if ($oldStatus !== 'completed' && $validated['status'] === 'completed') {
                $stage->tasks()->update([
                    'status' => 'pass',
                    'checked_by' => auth()->id(),
                    'checked_at' => now(),
                ]);
                \App\Services\ProgressService::calculate($stage->unit);
            }

            // Auto-reset tasks if uncompleting
            if ($oldStatus === 'completed' && in_array($validated['status'], ['pending', 'in_progress'])) {
                $stage->tasks()->update([
                    'status' => 'pending',
                    'checked_by' => null,
                    'checked_at' => null,
                ]);
                \App\Services\ProgressService::calculate($stage->unit);
            }
        });

        return response()->json($stage->fresh(['template']));
    }

    #[OA\Get(
        path: "/api/stages/{id}",
        summary: "Show Stage Details with Tasks",
        tags: ["Stages"],
        security: [["sanctum" => []]],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Stage Details",
                content: new OA\JsonContent(
                    allOf: [
                        new OA\Schema(ref: "#/components/schemas/UnitStage"),
                        new OA\Schema(
                            properties: [
                                new OA\Property(property: "tasks", type: "array", items: new OA\Items(ref: "#/components/schemas/UnitTask"))
                            ]
                        )
                    ]
                )
            )
        ]
    )]
    public function show(UnitStage $stage)
    {
        return $stage->load([
            'tasks' => function ($query) {
                $query->join('task_templates', 'unit_tasks.task_template_id', '=', 'task_templates.id')
                    ->select('unit_tasks.*')
                    ->orderBy('task_templates.order_index');
            },
            'tasks.template',
            'template'
        ]);
    }
}
