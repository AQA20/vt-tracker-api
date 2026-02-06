<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProgressResource;
use App\Models\Unit;
use App\Services\ProgressService;
use OpenApi\Attributes as OA;

class ProgressController extends Controller
{
    #[OA\Get(
        path: '/api/units/{unitId}/progress',
        summary: 'Get Unit Progress',
        tags: ['Units'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'unitId', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Progress',
                content: new OA\JsonContent(ref: '#/components/schemas/Progress')
            ),
        ]
    )]
    public function show(Unit $unit)
    {
        ProgressService::calculate($unit);

        return new ProgressResource($unit);
    }
}
