<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use App\Services\ProgressService;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class ProgressController extends Controller
{
    #[OA\Get(
        path: "/api/units/{unitId}/progress",
        summary: "Get Unit Progress",
        tags: ["Units"],
        security: [["sanctum" => []]],
        parameters: [
            new OA\Parameter(name: "unitId", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Progress",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "progress", type: "integer")
                    ]
                )
            )
        ]
    )]
    public function show(Unit $unit)
    {
        $progress = ProgressService::calculate($unit);
        return response()->json(['progress' => $progress]);
    }
}
