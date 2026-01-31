<?php

namespace App\Observers;

use App\Models\UnitTask;
use App\Services\ProgressService;
use App\Services\StageService;

class UnitTaskObserver
{
    /**
     * Handle the UnitTask "saved" event.
     */
    public function saved(UnitTask $unitTask): void
    {
        $this->recalculate($unitTask);
    }

    /**
     * Handle the UnitTask "deleted" event.
     */
    public function deleted(UnitTask $unitTask): void
    {
        $this->recalculate($unitTask);
    }

    /**
     * Trigger recalculation of stage and unit progress.
     */
    protected function recalculate(UnitTask $unitTask): void
    {
        $stage = $unitTask->unitStage;

        // 1. Recalculate Stage progress/status
        StageService::checkStageCompletion($stage);

        // 2. Recalculate Unit progress (ProgressService::calculate also triggers Project recalculation)
        ProgressService::calculate($stage->unit);
    }
}
