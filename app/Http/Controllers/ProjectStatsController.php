<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProjectStatsResource;
use App\Models\Project;
use OpenApi\Attributes as OA;

class ProjectStatsController extends Controller
{
    #[OA\Get(
        path: '/api/projects/{id}/stats',
        summary: 'Get Project Statistics',
        description: 'Returns status distribution percentages per category for all units in the project.',
        tags: ['Projects'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Project statistics',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            additionalProperties: new OA\AdditionalProperties(
                                type: 'object',
                                additionalProperties: new OA\AdditionalProperties(type: 'number')
                            ),
                            example: [
                                'tech' => ['approved' => 50, 'rejected' => 50],
                                'layout' => ['in_progress' => 100],
                            ]
                        ),
                    ]
                )
            ),
        ]
    )]
    public function index(Project $project)
    {
        // Pre-fill result with all categories and statuses set to 0
        $result = [];
        foreach (\App\Enums\StatusCategory::cases() as $category) {
            foreach (\App\Enums\Status::cases() as $status) {
                $result[$category->value][$status->value] = 0;
            }
        }

        $project->units()
            ->with('statusUpdates')
            ->get()
            ->flatMap(function ($unit) {
                return $unit->statusUpdates;
            })
            ->whereNotNull('status') // Only aggregate units that have a status set
            ->groupBy('category')
            ->each(function ($updates, $category) use (&$result) {
                $total = $updates->count();
                $updates->groupBy('status')
                    ->each(function ($statusUpdates, $status) use (&$result, $category, $total) {
                        $result[$category][$status] = round(($statusUpdates->count() / $total) * 100, 2);
                    });
            });

        return new ProjectStatsResource($result);
    }
}
