<?php

namespace App\Http\Controllers;

use App\Http\Requests\RideComfort\StoreRideComfortRequest;
use App\Models\Unit;
use OpenApi\Attributes as OA;

class RideComfortController extends Controller
{
    #[OA\Get(
        path: '/api/units/{unitId}/ride-comfort',
        summary: 'List Ride Comfort Results for Unit',
        tags: ['Ride Comfort'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'unitId', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of results',
                content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: '#/components/schemas/RideComfortResult'))
            ),
        ]
    )]
    public function index(Unit $unit)
    {
        return $unit->rideComfortResults;
    }

    #[OA\Post(
        path: '/api/units/{unitId}/ride-comfort',
        summary: 'Submit Ride Comfort Result',
        tags: ['Ride Comfort'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'unitId', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['vibration_value', 'noise_db', 'jerk_value'],
                properties: [
                    new OA\Property(property: 'vibration_value', type: 'number', format: 'float'),
                    new OA\Property(property: 'noise_db', type: 'number', format: 'float'),
                    new OA\Property(property: 'jerk_value', type: 'number', format: 'float'),
                    new OA\Property(property: 'device_used', type: 'string', enum: ['eva_625', 'vibxpert_ii', 'lms_test_lab', 'kone_ride_check', 'bruel_kjaer_2250', 'other_certified']),
                    new OA\Property(property: 'notes', type: 'string'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Result Created',
                content: new OA\JsonContent(ref: '#/components/schemas/RideComfortResult')
            ),
        ]
    )]
    public function store(StoreRideComfortRequest $request, Unit $unit)
    {
        // Find Stage 8 (Ride Comfort) to check dependency
        $rideComfortStage = $unit->stages()
            ->whereHas('template', function ($query) {
                $query->where('title', 'Ride Comfort');
            })->first();

        if ($rideComfortStage && ! \App\Services\StageService::canStartStage($rideComfortStage)) {
            $requiredStage = 7;

            return response()->json([
                'message' => "Cannot submit Ride Comfort results. Stage $requiredStage must be completed first.",
                'errors' => ['error' => ["Stage $requiredStage must be completed first."]],
            ], 422);
        }

        $data = $request->validated();

        // Basic pass criteria: Vibration < 1.0, Noise < 60, Jerk < 1.5
        $data['passed'] = (
            $data['vibration_value'] < 1.0 &&
            $data['noise_db'] < 60 &&
            $data['jerk_value'] < 1.5
        );
        $data['measured_at'] = now();

        $result = $unit->rideComfortResults()->create($data);

        // Autocomplete Ride Comfort stage only if passed
        if ($data['passed']) {
            $rideComfortStage = $unit->stages()
                ->whereHas('template', function ($query) {
                    $query->where('title', 'Ride Comfort');
                })->first();

            /** @var \App\Models\UnitStage $rideComfortStage */
            if ($rideComfortStage && $rideComfortStage->status !== 'completed') {
                $rideComfortStage->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                    'started_at' => $rideComfortStage->started_at ?? now(),
                ]);

                \App\Services\ProgressService::calculate($unit);
            }
        }

        return response()->json($result, 201);
    }
}
