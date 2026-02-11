<?php

namespace App\Http\Controllers;

use App\Http\Requests\DeliveryGroupItem\StoreDeliveryGroupItemRequest;
use App\Http\Resources\DeliveryGroupItemResource;
use App\Models\DeliveryGroup;
use App\Models\DeliveryGroupItem;
use OpenApi\Attributes as OA;

class DeliveryGroupItemController extends Controller
{
    #[OA\Get(
        path: '/api/delivery-groups/{delivery_group_id}/items',
        summary: 'List Items for Delivery Group',
        tags: ['Delivery Tracking'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'delivery_group_id', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of items in the delivery group',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/DeliveryGroupItem')
                )
            ),
        ]
    )]
    public function index(DeliveryGroup $deliveryGroup)
    {
        $items = $deliveryGroup->items()->with(['content.module'])->get();

        return DeliveryGroupItemResource::collection($items);
    }

    #[OA\Post(
        path: '/api/delivery-groups/{delivery_group_id}/items',
        summary: 'Add Item to Delivery Group',
        tags: ['Delivery Tracking'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'delivery_group_id', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['delivery_module_content_id', 'package_type'],
                properties: [
                    new OA\Property(property: 'delivery_module_content_id', type: 'string', format: 'uuid'),
                    new OA\Property(property: 'remarks', type: 'string', nullable: true),
                    new OA\Property(property: 'package_type', type: 'string', enum: ['Standard Packing', 'Sea Packing', 'Bark Free Packing']),
                    new OA\Property(property: 'special_delivery_address', type: 'string', nullable: true),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Item Added',
                content: new OA\JsonContent(ref: '#/components/schemas/DeliveryGroupItem')
            ),
        ]
    )]
    public function store(StoreDeliveryGroupItemRequest $request, DeliveryGroup $deliveryGroup)
    {
        $item = $deliveryGroup->items()->create($request->validated());

        return new DeliveryGroupItemResource($item->load(['content.module']));
    }

    #[OA\Delete(
        path: '/api/delivery-groups/{delivery_group_id}/items/{item_id}',
        summary: 'Remove Item from Delivery Group',
        tags: ['Delivery Tracking'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'delivery_group_id', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
            new OA\Parameter(name: 'item_id', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Item Removed'),
            new OA\Response(response: 404, description: 'Item not found in this group'),
        ]
    )]
    public function destroy(DeliveryGroup $deliveryGroup, DeliveryGroupItem $item)
    {
        if ($item->delivery_group_id !== $deliveryGroup->id) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $item->delete();

        return response()->noContent();
    }
}
