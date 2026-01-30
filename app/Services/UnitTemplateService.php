<?php

namespace App\Services;

use App\Models\Unit;

class UnitTemplateService
{
    public function applyTemplate(Unit $unit, string $type)
    {
        if ($type === 'KONE MonoSpace 700' || true) { // Defaulting to this for now as it's the only supported type
            $this->applyKoneMonoSpace700($unit);
        }
    }

    protected function applyKoneMonoSpace700(Unit $unit)
    {
        // The requirement says "Installation" repeats 6 times.
        // Let's define it as a list of objects or verify structure.
        
        $stageDefinitions = [
            ['name' => 'Installation', 'tasks' => ['Check shaft dimensions', 'Verify power supply', 'Install scaffolding']],
            ['name' => 'Installation', 'tasks' => ['Install guide rails (Car)', 'Install guide rails (CWT)', 'Align rails']],
            ['name' => 'Installation', 'tasks' => ['Install pit equipment', 'Install buffers', 'Install CWT frame']],
            ['name' => 'Installation', 'tasks' => ['Install car frame', 'Install platform', 'Install safety gear']],
            ['name' => 'Installation', 'tasks' => ['Install machine', 'Install ropes', 'Install governor']],
            ['name' => 'Installation', 'tasks' => ['Install doors', 'Install car enclosure', 'Wiring']],
            ['name' => 'Testing',      'tasks' => ['Safety gear test', 'Buffer test', 'Door interlock test', 'Functional test']],
            ['name' => 'Commissioning','tasks' => ['Final ride quality check', 'Client handover', 'Documentation handover']],
        ];

        foreach ($stageDefinitions as $index => $def) {
            $stage = $unit->stages()->create([
                'stage_name' => $def['name'],
                'stage_order' => $index + 1,
                'is_completed' => false,
            ]);

            foreach ($def['tasks'] as $taskDesc) {
                $stage->tasks()->create([
                    'description' => $taskDesc,
                    'is_completed' => false,
                ]);
            }
        }
    }
}
