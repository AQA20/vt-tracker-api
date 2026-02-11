<?php

namespace App\Http\Controllers;

use App\Models\DeliveryGroup;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class SupplyChainReferenceController extends Controller
{
    #[OA\Patch(
        path: '/api/delivery-groups/{deliveryGroupId}/supply-chain-reference',
        summary: 'Update Supply Chain Reference for Delivery Group',
        tags: ['Delivery Groups'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'deliveryGroupId', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'dir_reference', type: 'string', maxLength: 255, nullable: true),
                    new OA\Property(property: 'csp_reference', type: 'string', maxLength: 255, nullable: true),
                    new OA\Property(property: 'source', type: 'string', maxLength: 255, nullable: true),
                    new OA\Property(property: 'delivery_terms', type: 'string', maxLength: 255, nullable: true),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Reference Updated',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/SupplyChainReference'),
                        new OA\Property(property: 'message', type: 'string'),
                    ]
                )
            ),
        ]
    )]
    public function update(Request $request, DeliveryGroup $deliveryGroup): JsonResponse
    {
        $validated = $request->validate([
            'dir_reference' => 'nullable|string|max:255',
            'csp_reference' => 'nullable|string|max:255',
            'source' => 'nullable|string|max:255',
            'delivery_terms' => 'nullable|string|max:255',
        ]);

        $reference = $deliveryGroup->supplyChainReference()->updateOrCreate(
            ['delivery_group_id' => $deliveryGroup->id],
            [
                'dir_reference' => $validated['dir_reference'],
                'csp_reference' => $validated['csp_reference'],
                'source' => $validated['source'] ?? null,
                'delivery_terms' => $validated['delivery_terms'] ?? null,
            ]
        );

        return response()->json([
            'data' => $reference,
            'message' => 'Supply chain references updated successfully',
        ]);
    }
}
