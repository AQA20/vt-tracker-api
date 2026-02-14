<?php

namespace App\Http\Controllers;

use App\Http\Requests\Unit\StoreUnitRequest;
use App\Http\Requests\Unit\UpdateUnitRequest;
use App\Http\Resources\UnitResource;
use App\Models\Project;
use App\Models\Unit;
use App\Services\UnitService;
use Illuminate\Http\Request;
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
            new OA\Parameter(
                name: 'include',
                in: 'query',
                description: 'Comma-separated relations to include (statusUpdates, statusUpdates.revisions, statusUpdates.approvals)',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(name: 'page', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of units',
                content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: '#/components/schemas/Unit'))
            ),
        ]
    )]
    public function index(Request $request, Project $project)
    {

        $query = $project->units()->orderByDesc('created_at');

        // Search by Equipment Number, Type, Category, or id
        if ($request->has('search') && $search = $request->input('search')) {
            $searchTerm = strtolower($search);
            $query->where(function ($q) use ($searchTerm, $search) {
                $q->whereRaw('LOWER(equipment_number) LIKE ?', ["%{$searchTerm}%"])
                    ->orWhereRaw('LOWER(unit_type) LIKE ?', ["%{$searchTerm}%"])
                    ->orWhereRaw('LOWER(category) LIKE ?', ["%{$searchTerm}%"])
                    ->orWhereRaw('LOWER(sl_reference_no) LIKE ?', ["%{$searchTerm}%"])
                    ->orWhereRaw('LOWER(fl_unit_name) LIKE ?', ["%{$searchTerm}%"]);
                // Only search by id if search is a valid UUID
                if (preg_match('/^[0-9a-fA-F-]{36}$/', $search)) {
                    $q->orWhere('id', $search);
                }
            });
        }

        if ($request->has('include')) {
            $includes = $this->allowedUnitIncludes($request);
            $query->with($includes);
        }

        return UnitResource::collection($query->paginate(5));
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

        return new UnitResource($unit);
    }

    #[OA\Get(
        path: '/api/units/{id}',
        summary: 'Show Unit',
        tags: ['Units'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
            new OA\Parameter(
                name: 'include',
                in: 'query',
                description: 'Comma-separated relations to include (statusUpdates, statusUpdates.revisions, statusUpdates.approvals, stages.tasks)',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Unit Details',
                content: new OA\JsonContent(ref: '#/components/schemas/Unit')
            ),
        ]
    )]
    public function show(Request $request, Unit $unit)
    {
        $includes = ['stages.tasks'];
        if ($request->has('include')) {
            $includes = array_merge($includes, $this->allowedUnitIncludes($request));
        }

        return new UnitResource($unit->load($includes));
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

        return new UnitResource($unit);
    }

    #[OA\Delete(
        path: '/api/units/{id}',
        summary: 'Delete Unit',
        tags: ['Units'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(
                response: 204,
                description: 'Unit Deleted'
            ),
            new OA\Response(
                response: 404,
                description: 'Unit Not Found'
            ),
        ]
    )]
    public function destroy(Unit $unit)
    {
        $unit->delete();

        return response()->noContent();
    }

    private function allowedUnitIncludes(Request $request): array
    {
        $allowed = [
            'statusUpdates',
            'statusUpdates.revisions',
            'statusUpdates.approvals',
            'stages.tasks',
            'deliveryGroups',
            'deliveryGroups.milestones',
        ];

        $includes = array_map('trim', explode(',', $request->query('include', '')));
        $includes = array_filter($includes);

        return array_values(array_intersect($allowed, $includes));
    }
}
