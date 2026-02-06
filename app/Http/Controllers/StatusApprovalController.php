<?php

namespace App\Http\Controllers;

use App\Http\Requests\StatusApproval\StoreStatusApprovalRequest;
use App\Http\Requests\StatusApproval\UpdateStatusApprovalRequest;
use App\Http\Resources\StatusApprovalResource;
use App\Models\StatusApproval;
use App\Services\StatusApprovalService;
use OpenApi\Attributes as OA;

class StatusApprovalController extends Controller
{
    protected StatusApprovalService $service;

    public function __construct(StatusApprovalService $service)
    {
        $this->service = $service;
    }

    #[OA\Post(
        path: '/api/status-approvals',
        summary: 'Create Status Approval',
        tags: ['Status Approvals'],
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['status_update_id', 'approval_code'],
                properties: [
                    new OA\Property(property: 'status_update_id', type: 'string', format: 'uuid'),
                    new OA\Property(property: 'approval_code', type: 'string', enum: ['A', 'B']),
                    new OA\Property(property: 'approved_at', type: 'string', format: 'date-time', nullable: true),
                    new OA\Property(property: 'comment', type: 'string', nullable: true),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Status Approval Created',
                content: new OA\JsonContent(ref: '#/components/schemas/StatusApproval')
            ),
            new OA\Response(response: 422, description: 'Validation Error'),
        ]
    )]
    public function store(StoreStatusApprovalRequest $request)
    {
        $created = $this->service->create($request->validated());

        return new StatusApprovalResource($created);
    }

    #[OA\Patch(
        path: '/api/status-approvals/{id}',
        summary: 'Update Status Approval',
        tags: ['Status Approvals'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'approved_at', type: 'string', format: 'date-time', nullable: true),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Status Approval Updated',
                content: new OA\JsonContent(ref: '#/components/schemas/StatusApproval')
            ),
            new OA\Response(response: 404, description: 'Status Approval not found'),
            new OA\Response(response: 422, description: 'Validation Error'),
        ]
    )]
    public function update(UpdateStatusApprovalRequest $request, StatusApproval $statusApproval)
    {
        $updated = $this->service->update($statusApproval, $request->validated());

        return new StatusApprovalResource($updated);
    }
}
