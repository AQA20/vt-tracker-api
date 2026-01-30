<?php

namespace App\Services;

use App\Models\Unit;

class ProgressService
{
    public static function calculate(Unit $unit)
    {
        $stages = $unit->stages()
            ->with(['template', 'tasks'])
            ->get();

        if ($stages->isEmpty()) {
            return 0;
        }
        
        // Overall progress
        $overallProgress = static::calculateAccuracy($stages);

        // Group-based progress
        $groupedStages = $stages->groupBy('template.progress_group');

        $updateData = ['progress_percent' => $overallProgress];

        if ($installation = $groupedStages->get('installation')) {
            $updateData['installation_progress'] = static::calculateAccuracy($installation);
        }

        if ($commissioning = $groupedStages->get('commissioning')) {
            $updateData['commissioning_progress'] = static::calculateAccuracy($commissioning);
        }

        $unit->update($updateData);
        
        static::recalculateProject($unit->project);

        return $overallProgress;
    }

    protected static function calculateAccuracy($stages)
    {
        $totalWork = 0;
        $earnedWork = 0;

        foreach ($stages as $stage) {
            $tasks = $stage->tasks;
            if ($tasks->count() > 0) {
                $totalWork += $tasks->count();
                $earnedWork += $tasks->where('status', 'pass')->count();
            } else {
                // If a stage has no tasks, it counts as 1 unit of work
                $totalWork += 1;
                $earnedWork += ($stage->status === 'completed' ? 1 : 0);
            }
        }

        if ($totalWork === 0) {
            return 0;
        }

        if ($earnedWork === $totalWork) {
            return 100;
        }

        if ($earnedWork === 0) {
            return 0;
        }

        $percentage = ($earnedWork / $totalWork) * 100;
        
        // Use ceil to ensure "almost done" shows as 99%.
        return (int) max(1, min(99, ceil($percentage)));
    }

    public static function recalculateProject(\App\Models\Project $project)
    {
        $units = $project->units()->get();
        
        if ($units->isEmpty()) {
            $project->update([
                'installation_progress' => 0,
                'commissioning_progress' => 0,
            ]);
            return 0;
        }

        $avgInstallation = $units->avg('installation_progress');
        $avgCommissioning = $units->avg('commissioning_progress');
        $avgOverall = $units->avg('progress_percent');

        // Logic for project level: if any unit is not 100%, project is not 100%
        $projectInstallation = (int) ceil($avgInstallation);
        if ($projectInstallation >= 100 && $units->contains(fn($u) => $u->installation_progress < 100)) {
            $projectInstallation = 99;
        }

        $projectCommissioning = (int) ceil($avgCommissioning);
        if ($projectCommissioning >= 100 && $units->contains(fn($u) => $u->commissioning_progress < 100)) {
            $projectCommissioning = 99;
        }

        $project->update([
            'installation_progress' => $projectInstallation,
            'commissioning_progress' => $projectCommissioning,
        ]);

        $projectOverall = (int) ceil($avgOverall);
        if ($projectOverall >= 100 && $units->contains(fn($u) => $u->progress_percent < 100)) {
            $projectOverall = 99;
        }

        return $projectOverall;
    }
}
