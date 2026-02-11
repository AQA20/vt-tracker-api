<?php

namespace App\Http\Controllers;

use App\Http\Resources\DeliveryModuleResource;
use App\Models\DeliveryModule;
use OpenApi\Attributes as OA;

class DeliveryModuleController extends Controller
{
    #[OA\Get(
        path: '/api/delivery-modules',
        summary: 'List Delivery Modules (Catalog)',
        tags: ['Delivery Tracking'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of delivery modules with their content',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/DeliveryModule')
                )
            ),
        ]
    )]
    public function index()
    {
        $modules = DeliveryModule::with('contents')->get();

        return DeliveryModuleResource::collection($modules);
    }
}
