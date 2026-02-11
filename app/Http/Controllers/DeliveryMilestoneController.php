<?php

namespace App\Http\Controllers;

use App\Http\Resources\DeliveryMilestoneResource;
use App\Models\DeliveryMilestone;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class DeliveryMilestoneController extends Controller
{
    #[OA\Patch(
        path: '/api/delivery-milestones/{milestoneId}',
        summary: 'Update Delivery Milestone',
        tags: ['Delivery Tracking'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'milestoneId', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'actual_completion_date', type: 'string', format: 'date', nullable: true),
                    new OA\Property(property: 'planned_completion_date', type: 'string', format: 'date', nullable: true),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Milestone Updated',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/DeliveryMilestone'),
                    ]
                )
            ),
        ]
    )]
    public function update(Request $request, DeliveryMilestone $milestone): JsonResponse
    {
        $validated = $request->validate([
            'actual_completion_date' => 'nullable|date',
            'planned_completion_date' => 'nullable|date',
        ]);

        $milestone->update($validated);

        return response()->json([
            'data' => new DeliveryMilestoneResource($milestone),
        ]);
    }
}
