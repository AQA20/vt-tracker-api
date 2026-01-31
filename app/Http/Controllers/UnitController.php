<?php

namespace App\Http\Controllers;

use App\Http\Requests\Unit\StoreUnitRequest;
use App\Http\Requests\Unit\UpdateUnitRequest;
use App\Models\Project;
use App\Models\Unit;
use App\Services\UnitService;
use OpenApi\Attributes as OA;

class UnitController extends Controller
{
    #[OA\Get(
        path: '/api/projects/{projectId}/units',
        summary: 'List Units for Project',
        tags: ['Units'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'projectId', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of units',
                content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: '#/components/schemas/Unit'))
            ),
        ]
    )]
    public function index(Project $project)
    {
        return $project->units()->orderBy('equipment_number')->get();
    }

    #[OA\Post(
        path: '/api/projects/{projectId}/units',
        summary: 'Create Unit',
        tags: ['Units'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'projectId', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['unit_type', 'equipment_number', 'category'],
                properties: [
                    new OA\Property(property: 'unit_type', type: 'string'),
                    new OA\Property(property: 'equipment_number', type: 'string'),
                    new OA\Property(property: 'category', type: 'string', enum: ['elevator', 'escalator', 'travelator', 'dumbwaiter']),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Unit Created',
                content: new OA\JsonContent(ref: '#/components/schemas/Unit')
            ),
        ]
    )]
    public function store(StoreUnitRequest $request, Project $project)
    {
        $unit = $project->units()->create($request->validated());

        UnitService::generateStagesAndTasks($unit);

        return response()->json($unit, 201);
    }

    #[OA\Get(
        path: '/api/units/{id}',
        summary: 'Show Unit',
        tags: ['Units'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Unit Details',
                content: new OA\JsonContent(ref: '#/components/schemas/Unit')
            ),
        ]
    )]
    public function show(Unit $unit)
    {
        return $unit->load('stages.tasks');
    }

    #[OA\Put(
        path: '/api/units/{id}',
        summary: 'Update Unit',
        tags: ['Units'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'equipment_number', type: 'string'),
                    new OA\Property(property: 'category', type: 'string'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Unit Updated',
                content: new OA\JsonContent(ref: '#/components/schemas/Unit')
            ),
        ]
    )]
    public function update(UpdateUnitRequest $request, Unit $unit)
    {
        $unit->update($request->validated());

        return $unit;
    }
}
