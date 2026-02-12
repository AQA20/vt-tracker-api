<?php

namespace App\Http\Controllers;

use App\Http\Requests\Project\StoreProjectRequest;
use App\Http\Requests\Project\UpdateProjectRequest;
use App\Http\Resources\ProjectResource;
use App\Models\Project;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class ProjectController extends Controller
{
    #[OA\Get(
        path: '/api/projects',
        summary: 'List Projects',
        tags: ['Projects'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(
                name: 'include',
                in: 'query',
                description: 'Comma-separated list of relationships to include (e.g., units.statusUpdates.revisions,units.statusUpdates.approvals)',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'search',
                in: 'query',
                description: 'Search projects by KONE project ID, name, client name, or location',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'page',
                in: 'query',
                description: 'Page number',
                required: false,
                schema: new OA\Schema(type: 'integer')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Paginated list of projects',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Project')),
                        new OA\Property(property: 'links', type: 'object'),
                        new OA\Property(property: 'meta', type: 'object'),
                    ]
                )
            ),
        ]
    )]
    public function index(Request $request)
    {
        $query = Project::withCount('units')->orderBy('created_at', 'desc');

        if ($request->has('include')) {
            $includes = $this->allowedProjectIncludes($request);
            $query->with($includes);
        }

        if ($request->has('search') && ! empty($request->search)) {
            $searchTerm = strtolower($request->search);
            $query->where(function ($q) use ($searchTerm) {
                $q->whereRaw('LOWER(kone_project_id) LIKE ?', ["%{$searchTerm}%"])
                    ->orWhereRaw('LOWER(name) LIKE ?', ["%{$searchTerm}%"])
                    ->orWhereRaw('LOWER(client_name) LIKE ?', ["%{$searchTerm}%"])
                    ->orWhereRaw('LOWER(location) LIKE ?', ["%{$searchTerm}%"]);
            });
        }

        return ProjectResource::collection($query->paginate(9));
    }

    #[OA\Post(
        path: '/api/projects',
        summary: 'Create Project',
        tags: ['Projects'],
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'client_name', 'location'],
                properties: [
                    new OA\Property(property: 'name', type: 'string'),
                    new OA\Property(property: 'kone_project_id', type: 'string', nullable: true),
                    new OA\Property(property: 'client_name', type: 'string'),
                    new OA\Property(property: 'location', type: 'string'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Project Created',
                content: new OA\JsonContent(ref: '#/components/schemas/Project')
            ),
        ]
    )]
    public function store(StoreProjectRequest $request)
    {
        $project = Project::create($request->validated());

        return new ProjectResource($project);
    }

    #[OA\Get(
        path: '/api/projects/{id}',
        summary: 'Show Project',
        tags: ['Projects'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Project Details',
                content: new OA\JsonContent(ref: '#/components/schemas/Project')
            ),
        ]
    )]
    public function show(Project $project)
    {
        return new ProjectResource($project->load('units'));
    }

    #[OA\Put(
        path: '/api/projects/{id}',
        summary: 'Update Project',
        tags: ['Projects'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'name', type: 'string'),
                    new OA\Property(property: 'kone_project_id', type: 'string', nullable: true),
                    new OA\Property(property: 'client_name', type: 'string'),
                    new OA\Property(property: 'location', type: 'string'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Project Updated',
                content: new OA\JsonContent(ref: '#/components/schemas/Project')
            ),
        ]
    )]
    public function update(UpdateProjectRequest $request, Project $project)
    {
        $project->update($request->validated());

        return new ProjectResource($project);
    }

    private function allowedProjectIncludes(Request $request): array
    {
        $allowed = [
            'units',
            'units.statusUpdates',
            'units.statusUpdates.revisions',
            'units.statusUpdates.approvals',
            'units.deliveryGroups',
            'units.deliveryGroups.milestones',
        ];

        $includes = array_map('trim', explode(',', $request->query('include', '')));
        $includes = array_filter($includes);

        return array_values(array_intersect($allowed, $includes));
    }
}
