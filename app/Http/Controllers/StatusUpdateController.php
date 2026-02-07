<?php

namespace App\Http\Controllers;

use App\Http\Requests\StatusUpdate\UpdateStatusRequest;
use App\Http\Resources\StatusUpdateResource;
use App\Models\StatusUpdate;
use App\Services\StatusUpdateService;
use OpenApi\Attributes as OA;

class StatusUpdateController extends Controller
{
    protected StatusUpdateService $service;

    protected \App\Services\UnitStatusCopyService $copyService;

    public function __construct(StatusUpdateService $service, \App\Services\UnitStatusCopyService $copyService)
    {
        $this->service = $service;
        $this->copyService = $copyService;
    }

    #[OA\Patch(
        path: '/api/status-updates/{id}',
        summary: 'Update Status of a Status Update',
        tags: ['Status Updates'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['status'],
                properties: [
                    new OA\Property(property: 'status', type: 'string', enum: ['submitted', 'in_progress', 'rejected', 'approved']),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Status Updated',
                content: new OA\JsonContent(ref: '#/components/schemas/StatusUpdate')
            ),
            new OA\Response(response: 404, description: 'Status Update not found'),
            new OA\Response(response: 422, description: 'Validation Error'),
        ]
    )]
    public function update(UpdateStatusRequest $request, StatusUpdate $statusUpdate)
    {
        $status = \App\Enums\Status::from($request->status);

        $updated = $this->service->updateStatus($statusUpdate, $status);

        return new StatusUpdateResource($updated);
    }

    #[OA\Post(
        path: '/api/status-updates/{statusUpdate}/upload-pdf',
        summary: 'Upload PDF for a Status Update',
        tags: ['Status Updates'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'statusUpdate', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'pdf', type: 'string', format: 'binary'),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'PDF Uploaded',
                content: new OA\JsonContent(ref: '#/components/schemas/StatusUpdate')
            ),
            new OA\Response(response: 404, description: 'Status Update not found'),
            new OA\Response(response: 422, description: 'Validation Error'),
        ]
    )]
    public function uploadPdf(\Illuminate\Http\Request $request, StatusUpdate $statusUpdate)
    {
        $request->validate([
            'pdf' => 'required|file|mimes:pdf|max:10240', // 10MB limit
        ]);

        $updated = $this->service->uploadPdf($statusUpdate, $request->file('pdf'));

        return new StatusUpdateResource($updated);
    }

    #[OA\Post(
        path: '/api/units/{unit}/statuses/{category}/copy-from',
        summary: 'Copy Status from another unit',
        tags: ['Status Updates'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'unit', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
            new OA\Parameter(name: 'category', in: 'path', required: true, schema: new OA\Schema(type: 'string')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['source_unit_id', 'source_status_key'],
                properties: [
                    new OA\Property(property: 'source_unit_id', type: 'string', format: 'uuid'),
                    new OA\Property(property: 'source_status_key', type: 'string'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Status Copied',
                content: new OA\JsonContent(ref: '#/components/schemas/StatusUpdate')
            ),
            new OA\Response(response: 404, description: 'Unit or Status not found'),
            new OA\Response(response: 422, description: 'Validation Error'),
        ]
    )]
    public function copyFrom(\Illuminate\Http\Request $request, \App\Models\Unit $unit, string $category)
    {
        $request->validate([
            'source_unit_id' => 'required|exists:units,id',
            'source_status_key' => 'required|string',
        ]);

        $sourceUnit = \App\Models\Unit::findOrFail($request->source_unit_id);

        if ($unit->project_id !== $sourceUnit->project_id) {
            return response()->json(['message' => 'Source unit must belong to the same project'], 422);
        }

        if ($unit->id === $sourceUnit->id) {
            return response()->json(['message' => 'Source unit and target unit must be different'], 422);
        }

        $updated = $this->copyService->copyStatus(
            $unit,
            $category,
            $sourceUnit,
            $request->source_status_key
        );

        return new StatusUpdateResource($updated);
    }
}
