<?php

namespace App\Observers;

use App\Models\UnitStage;
use App\Services\ProgressService;

class UnitStageObserver
{
    /**
     * Handle the UnitStage "saved" event.
     */
    public function saved(UnitStage $unitStage): void
    {
        ProgressService::calculate($unitStage->unit);
    }

    /**
     * Handle the UnitStage "deleted" event.
     */
    public function deleted(UnitStage $unitStage): void
    {
        ProgressService::calculate($unitStage->unit);
    }
}
