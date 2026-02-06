<?php

namespace App\Http\Controllers;

use App\Http\Requests\StatusRevision\StoreStatusRevisionRequest;
use App\Http\Requests\StatusRevision\UpdateStatusRevisionRequest;
use App\Http\Resources\StatusRevisionResource;
use App\Models\StatusRevision;
use App\Services\StatusRevisionService;
use OpenApi\Attributes as OA;

class StatusRevisionController extends Controller
{
    protected StatusRevisionService $service;

    public function __construct(StatusRevisionService $service)
    {
        $this->service = $service;
    }

    #[OA\Post(
        path: '/api/status-revisions',
        summary: 'Create Status Revision',
        tags: ['Status Revisions'],
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['status_update_id', 'revision_number'],
                properties: [
                    new OA\Property(property: 'status_update_id', type: 'string', format: 'uuid'),
                    new OA\Property(property: 'revision_number', type: 'integer', minimum: 0, maximum: 9),
                    new OA\Property(property: 'revision_date', type: 'string', format: 'date-time', nullable: true),
                    new OA\Property(property: 'pdf_path', type: 'string', nullable: true),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Status Revision Created',
                content: new OA\JsonContent(ref: '#/components/schemas/StatusRevision')
            ),
            new OA\Response(response: 422, description: 'Validation Error'),
        ]
    )]
    public function store(StoreStatusRevisionRequest $request)
    {
        $created = $this->service->create($request->validated());

        return new StatusRevisionResource($created);
    }

    #[OA\Patch(
        path: '/api/status-revisions/{id}',
        summary: 'Update Status Revision',
        tags: ['Status Revisions'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'revision_date', type: 'string', format: 'date-time', nullable: true),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Status Revision Updated',
                content: new OA\JsonContent(ref: '#/components/schemas/StatusRevision')
            ),
            new OA\Response(response: 404, description: 'Status Revision not found'),
            new OA\Response(response: 422, description: 'Validation Error'),
        ]
    )]
    public function update(UpdateStatusRevisionRequest $request, StatusRevision $statusRevision)
    {
        $updated = $this->service->update($statusRevision, $request->validated());

        return new StatusRevisionResource($updated);
    }
}
