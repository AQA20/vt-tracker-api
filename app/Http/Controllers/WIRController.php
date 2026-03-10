<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreWIRUploadRequest;
use App\Http\Resources\WIRUploadResource;
use App\Models\Unit;
use App\Models\WIRUpload;
use App\Services\WIRService;
use OpenApi\Attributes as OA;

class WIRController extends Controller
{
    public function __construct(private WIRService $wirService) {}

    #[OA\Post(
        path: '/api/units/{unitId}/wir-uploads',
        summary: 'Upload WIR Image',
        tags: ['WIR'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'unitId', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['file', 'progress_group'],
                properties: [
                    new OA\Property(property: 'file', type: 'string', format: 'binary'),
                    new OA\Property(property: 'progress_group', type: 'string', enum: ['installation', 'commissioning']),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'WIR uploaded successfully',
                content: new OA\JsonContent(ref: '#/components/schemas/WIRUpload')
            ),
            new OA\Response(response: 422, description: 'Validation Error'),
        ]
    )]
    public function store(StoreWIRUploadRequest $request, Unit $unit)
    {
        $wirUpload = $this->wirService->uploadWIR(
            $unit,
            $request->file('file'),
            $request->input('progress_group')
        );

        return (new WIRUploadResource($wirUpload))->response()->setStatusCode(201);
    }

    #[OA\Get(
        path: '/api/units/{unitId}/wir-uploads',
        summary: 'List WIR Uploads',
        tags: ['WIR'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'unitId', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of WIR uploads',
                content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: '#/components/schemas/WIRUpload'))
            ),
        ]
    )]
    public function index(Unit $unit)
    {
        return WIRUploadResource::collection($unit->wirUploads()->get());
    }

    #[OA\Get(
        path: '/api/units/{unitId}/wir-uploads/{progressGroup}',
        summary: 'Get WIR Upload by Progress Group',
        tags: ['WIR'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'unitId', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
            new OA\Parameter(name: 'progressGroup', in: 'path', required: true, schema: new OA\Schema(type: 'string', enum: ['installation', 'commissioning'])),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'WIR upload details',
                content: new OA\JsonContent(ref: '#/components/schemas/WIRUpload')
            ),
            new OA\Response(response: 404, description: 'Not found'),
        ]
    )]
    public function show(Unit $unit, string $progressGroup)
    {
        $wir = $this->wirService->getWIRByGroup($unit, $progressGroup);

        if (! $wir) {
            abort(404, 'WIR upload not found');
        }

        return new WIRUploadResource($wir);
    }

    #[OA\Delete(
        path: '/api/wir-uploads/{id}',
        summary: 'Delete WIR Upload',
        tags: ['WIR'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(response: 204, description: 'WIR deleted successfully'),
            new OA\Response(response: 404, description: 'Not found'),
        ]
    )]
    public function destroy(WIRUpload $wir)
    {
        $this->wirService->deleteWIR($wir);

        return response()->noContent();
    }
}
