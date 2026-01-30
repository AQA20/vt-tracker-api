<?php

namespace App\Services;

use App\Models\StageTemplate;
use App\Models\Unit;
use App\Models\UnitStage;
use App\Models\UnitTask;

class UnitService
{
    public static function generateStagesAndTasks(Unit $unit)
    {
        $stages = StageTemplate::where('unit_type', $unit->unit_type)
            ->where('category', $unit->category)
            ->orderBy('order_index')
            ->get();

        foreach ($stages as $stageTemplate) {
            $unitStage = UnitStage::create([
                'unit_id' => $unit->id,
                'stage_template_id' => $stageTemplate->id,
                'status' => 'pending',
                'started_at' => null,
                'completed_at' => null,
            ]);

            $tasks = $stageTemplate->taskTemplates()
                ->orderBy('order_index')
                ->get();

            foreach ($tasks as $taskTemplate) {
                UnitTask::create([
                    'unit_stage_id' => $unitStage->id,
                    'task_template_id' => $taskTemplate->id,
                    'status' => 'pending',
                    'notes' => null,
                    'checked_by' => null,
                    'checked_at' => null,
                ]);
            }
        }
    }
}
