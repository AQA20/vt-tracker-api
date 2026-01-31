<?php

namespace App\Http\Controllers;

use App\Http\Requests\Project\StoreProjectRequest;
use App\Http\Requests\Project\UpdateProjectRequest;
use App\Models\Project;
use OpenApi\Attributes as OA;

class ProjectController extends Controller
{
    #[OA\Get(
        path: '/api/projects',
        summary: 'List Projects',
        tags: ['Projects'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of projects',
                content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: '#/components/schemas/Project'))
            ),
        ]
    )]
    public function index()
    {
        return Project::withCount('units')->get();
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
        return Project::create($request->validated());
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
        return $project->load('units');
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

        return $project;
    }
}
