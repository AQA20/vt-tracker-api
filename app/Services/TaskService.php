<?php

namespace App\Services;

use App\Models\UnitTask;
use Illuminate\Validation\ValidationException;

class TaskService
{
    public static function updateStatus(UnitTask $task, string $status, ?string $notes = null, ?int $userId = null)
    {
        // Check stage dependencies ONLY when marking as 'pass'
        if ($status === 'pass') {
            $previousStage = StageService::getPreviousStage($task->unitStage);
            if ($previousStage && $previousStage->status !== 'completed') {
                $prevTitle = $previousStage->template->title;
                $prevNum = $previousStage->template->stage_number;
                throw ValidationException::withMessages(['error' => "Cannot update task. Stage $prevNum ($prevTitle) must be completed first."]);
            }

            // Strict Task Ordering Check: Cannot mark as 'pass' if previous tasks are not 'pass'
            $currentOrder = $task->template->order_index;
            $hasIncompletePrevious = $task->unitStage->tasks()
                ->join('task_templates', 'unit_tasks.task_template_id', '=', 'task_templates.id')
                ->where('task_templates.order_index', '<', $currentOrder)
                ->where('unit_tasks.status', '!=', 'pass')
                ->exists();

            if ($hasIncompletePrevious) {
                throw ValidationException::withMessages(['error' => 'Cannot mark task as complete because earlier tasks in this stage are not yet completed.']);
            }
        }

        // Robust Task Reversion Logic
        if ($status === 'pending' && $task->status === 'pass') {
            // 1. Check if any subsequent task in the SAME stage is already 'pass'
            $blockingTask = StageService::getLaterTaskInSameStage($task);
            if ($blockingTask) {
                $code = $blockingTask->template->task_code;
                $title = $blockingTask->template->title;
                throw ValidationException::withMessages(['error' => "Cannot mark task as incomplete because Task $code ($title) is already completed."]);
            }

            // 2. Check for subsequent work in the SAME progress group
            $blockReason = StageService::getSubsequentWorkInfo($task->unitStage);
            if ($blockReason) {
                throw ValidationException::withMessages(['error' => "Cannot mark task as incomplete because $blockReason."]);
            }
        }

        $task->update([
            'status' => $status,
            'notes' => $notes,
            'checked_by' => $userId,
            'checked_at' => now(),
        ]);

        StageService::checkStageCompletion($task->unitStage);
    }
}
