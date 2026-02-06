<?php

namespace App\Http\Controllers;

use App\Http\Requests\Task\UpdateTaskRequest;
use App\Http\Resources\UnitTaskResource;
use App\Models\UnitStage;
use App\Models\UnitTask;
use App\Services\TaskService;
use OpenApi\Attributes as OA;

class TaskController extends Controller
{
    #[OA\Get(
        path: '/api/stages/{stageId}/tasks',
        summary: 'List Tasks for Stage',
        tags: ['Tasks'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'stageId', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of tasks',
                content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: '#/components/schemas/UnitTask'))
            ),
        ]
    )]
    public function index(UnitStage $stage)
    {
        return UnitTaskResource::collection(
            $stage->tasks()
                ->join('task_templates', 'unit_tasks.task_template_id', '=', 'task_templates.id')
                ->select('unit_tasks.*')
                ->orderBy('task_templates.order_index')
                ->with('template')
                ->get()
        );
    }

    #[OA\Put(
        path: '/api/tasks/{id}',
        summary: 'Update Task Status',
        tags: ['Tasks'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['status'],
                properties: [
                    new OA\Property(property: 'status', type: 'string', enum: ['pass', 'fail', 'pending']),
                    new OA\Property(property: 'notes', type: 'string'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Task Updated',
                content: new OA\JsonContent(ref: '#/components/schemas/UnitTask')
            ),
        ]
    )]
    public function update(UpdateTaskRequest $request, UnitTask $task)
    {
        $validated = $request->validated();

        TaskService::updateStatus(
            $task,
            $validated['status'],
            $validated['notes'] ?? null,
            auth()->id()
        );

        return new UnitTaskResource($task->fresh());
    }
}
