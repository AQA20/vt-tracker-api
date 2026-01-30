<?php

namespace App\Services;

use App\Models\UnitStage;

class StageService
{
    public static function checkStageCompletion(UnitStage $stage)
    {
        $tasks = $stage->tasks()->get();
        $totalTasks = $tasks->count();

        if ($totalTasks > 0 && $tasks->every(fn($t) => $t->status === 'pass')) {
            if ($stage->status !== 'completed') {
                $stage->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                ]);
            }
        } else {
             $hasProgress = $tasks->contains(fn($t) => $t->status === 'pass');
             $newStatus = $hasProgress ? 'in_progress' : 'pending';
             
             if ($stage->status !== $newStatus || $stage->completed_at !== null) {
                 $stage->update([
                     'status' => $newStatus,
                     'completed_at' => null,
                 ]);
             }
        }
    }

    public static function canStartStage(UnitStage $stage): bool
    {
        $previousStage = static::getPreviousStage($stage);
        
        if (!$previousStage) {
            return true;
        }

        return $previousStage->status === 'completed';
    }

    public static function getPreviousStage(UnitStage $stage): ?UnitStage
    {
        $stageNumber = $stage->template->stage_number;
        $progressGroup = $stage->template->progress_group;
        
        return $stage->unit->stages()
            ->reorder()
            ->join('stage_templates', 'unit_stages.stage_template_id', '=', 'stage_templates.id')
            ->where('stage_templates.stage_number', '<', $stageNumber)
            ->where('stage_templates.progress_group', $progressGroup)
            ->orderByDesc('stage_templates.stage_number')
            ->select('unit_stages.*')
            ->with('template')
            ->first();
    }

    /**
     * Returns details about any work in subsequent stages within the same progress group.
     */
    public static function getSubsequentWorkInfo(UnitStage $stage): ?string
    {
        $stageNumber = $stage->template->stage_number;
        $progressGroup = $stage->template->progress_group;

        // Check for completed stages
        $laterStage = $stage->unit->stages()
            ->join('stage_templates', 'unit_stages.stage_template_id', '=', 'stage_templates.id')
            ->where('stage_templates.progress_group', $progressGroup)
            ->where('stage_templates.stage_number', '>', $stageNumber)
            ->where('unit_stages.status', 'completed')
            ->select('unit_stages.*')
            ->with('template')
            ->first();

        if ($laterStage) {
            $num = $laterStage->template->stage_number;
            $title = $laterStage->template->title;
            return "Stage $num ($title) is already completed";
        }

        // Check for 'pass' tasks in any later stage
        $laterTask = \App\Models\UnitTask::join('unit_stages', 'unit_tasks.unit_stage_id', '=', 'unit_stages.id')
            ->join('stage_templates', 'unit_stages.stage_template_id', '=', 'stage_templates.id')
            ->where('unit_stages.unit_id', $stage->unit_id)
            ->where('stage_templates.progress_group', $progressGroup)
            ->where('stage_templates.stage_number', '>', $stageNumber)
            ->where('unit_tasks.status', 'pass')
            ->select('unit_tasks.*')
            ->with(['template', 'unitStage.template'])
            ->first();

        if ($laterTask) {
            $sNum = $laterTask->unitStage->template->stage_number;
            $sTitle = $laterTask->unitStage->template->title;
            $tCode = $laterTask->template->task_code;
            $tTitle = $laterTask->template->title;
            return "Task $tCode ($tTitle) in Stage $sNum ($sTitle) is already completed";
        }

        return null;
    }

    /**
     * Finds the first 'pass' task that appears after a given task in the same stage.
     */
    public static function getLaterTaskInSameStage(\App\Models\UnitTask $task): ?\App\Models\UnitTask
    {
        return $task->unitStage->tasks()
            ->join('task_templates', 'unit_tasks.task_template_id', '=', 'task_templates.id')
            ->where('task_templates.order_index', '>', $task->template->order_index)
            ->where('unit_tasks.status', 'pass')
            ->select('unit_tasks.*')
            ->with('template')
            ->orderBy('task_templates.order_index')
            ->first();
    }
}
