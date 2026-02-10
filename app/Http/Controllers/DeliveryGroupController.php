<?php

namespace App\Http\Controllers;

use App\Http\Resources\DeliveryGroupResource;
use App\Http\Resources\DeliveryMilestoneResource;
use App\Models\DeliveryGroup;
use App\Models\Unit;
use App\Services\DeliveryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class DeliveryGroupController extends Controller
{
    public function __construct(protected DeliveryService $deliveryService) {}

    #[OA\Post(
        path: '/api/units/{unitId}/delivery-groups',
        summary: 'Create Delivery Group',
        tags: ['Delivery Tracking'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'unitId', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['group_name', 'group_number'],
                properties: [
                    new OA\Property(property: 'group_name', type: 'string'),
                    new OA\Property(property: 'group_number', type: 'integer'),
                    new OA\Property(property: 'notes', type: 'string', nullable: true),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Delivery Group Created',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/DeliveryGroup'),
                        new OA\Property(property: 'message', type: 'string'),
                    ]
                )
            ),
        ]
    )]
    public function store(Request $request, Unit $unit): JsonResponse
    {
        $validated = $request->validate([
            'group_name' => 'required|string|max:255',
            'group_number' => 'required|integer',
            'notes' => 'nullable|string',
        ]);

        $group = $this->deliveryService->createGroup(
            $unit,
            $validated['group_name'],
            $validated['group_number'],
            $validated['notes'] ?? null
        );

        return response()->json([
            'data' => new DeliveryGroupResource($group->load('milestones')),
            'message' => 'Delivery group created successfully',
        ], 201);
    }

    #[OA\Get(
        path: '/api/units/{unitId}/delivery-groups',
        summary: 'List Delivery Groups for Unit',
        tags: ['Delivery Tracking'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'unitId', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of delivery groups',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/DeliveryGroup')),
                    ]
                )
            ),
        ]
    )]
    public function index(Unit $unit): JsonResponse
    {
        $deliveryGroups = $unit->deliveryGroups()
            ->with('milestones')
            ->get();

        return response()->json([
            'data' => DeliveryGroupResource::collection($deliveryGroups),
        ]);
    }

    #[OA\Get(
        path: '/api/delivery-groups/{deliveryGroupId}/milestones',
        summary: 'List Milestones for Delivery Group',
        tags: ['Delivery Tracking'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'deliveryGroupId', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of milestones',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/DeliveryMilestone')),
                    ]
                )
            ),
        ]
    )]
    public function milestones(DeliveryGroup $deliveryGroup): JsonResponse
    {
        $milestones = $deliveryGroup->milestones()->get();

        return response()->json([
            'data' => DeliveryMilestoneResource::collection($milestones),
        ]);
    }
}
