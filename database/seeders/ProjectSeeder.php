<?php

namespace Database\Seeders;

use App\Enums\ApprovalCode;
use App\Enums\Status;
use App\Enums\UnitCategory;
use App\Models\Project;
use App\Models\StatusApproval;
use App\Models\StatusRevision;
use App\Models\Unit;
use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Skyline Tower (2 Units)
        $skyline = Project::create(['name' => 'Skyline Tower', 'client_name' => 'Emaar', 'location' => 'Dubai']);
        $u1 = Unit::create(['project_id' => $skyline->id, 'unit_type' => 'KONE MonoSpace 700', 'equipment_number' => 'SKY-001', 'category' => UnitCategory::ELEVATOR]);
        \App\Services\UnitService::generateStagesAndTasks($u1);
        $this->randomizeStatusUpdates($u1);

        $u2 = Unit::create(['project_id' => $skyline->id, 'unit_type' => 'KONE MonoSpace 700', 'equipment_number' => 'SKY-002', 'category' => UnitCategory::ELEVATOR]);
        \App\Services\UnitService::generateStagesAndTasks($u2);
        $this->randomizeStatusUpdates($u2);

        $this->completeStages($u1, [1, 2, 3]); // Complete first 3 stages
        $this->completeStages($u2, [1, 2, 3, 4, 5, 6, 7]); // Ready for Ride Comfort

        // 2. City Mall (1 Unit)
        $mall = Project::create(['name' => 'City Mall', 'client_name' => 'Westfield', 'location' => 'London']);
        $u3 = Unit::create(['project_id' => $mall->id, 'unit_type' => 'KONE MonoSpace 700', 'equipment_number' => 'CM-E1', 'category' => UnitCategory::ELEVATOR]);
        \App\Services\UnitService::generateStagesAndTasks($u3);
        $this->randomizeStatusUpdates($u3);

        $this->completeStages($u3, [1, 2, 3, 4, 5, 6, 7]);
        // Add Ride Comfort Result (Completes Stage 8)
        \App\Models\RideComfortResult::create([
            'unit_id' => $u3->id,
            'vibration_value' => 0.45,
            'noise_db' => 48.5,
            'jerk_value' => 0.75,
            'passed' => true,
            'device_used' => 'eva_625',
        ]);

        // 3. The Grand Hotel (3 Units)
        $grand = Project::create(['name' => 'The Grand Hotel', 'client_name' => 'Hilton', 'location' => 'New York']);
        $u4 = Unit::create(['project_id' => $grand->id, 'unit_type' => 'KONE MonoSpace 700', 'equipment_number' => 'GH-01', 'category' => UnitCategory::ELEVATOR]);
        \App\Services\UnitService::generateStagesAndTasks($u4);
        $this->randomizeStatusUpdates($u4);
        $this->completeStages($u4, [1]); // Just started

        $u5 = Unit::create(['project_id' => $grand->id, 'unit_type' => 'KONE MonoSpace 700', 'equipment_number' => 'GH-02', 'category' => UnitCategory::ELEVATOR]); // 0 progress
        \App\Services\UnitService::generateStagesAndTasks($u5);
        $this->randomizeStatusUpdates($u5);

        $u6 = Unit::create(['project_id' => $grand->id, 'unit_type' => 'KONE MonoSpace 700', 'equipment_number' => 'GH-03', 'category' => UnitCategory::ELEVATOR]); // 0 progress
        \App\Services\UnitService::generateStagesAndTasks($u6);
        $this->randomizeStatusUpdates($u6);

        // 4. Tech Park (2 Units)
        $tech = Project::create(['name' => 'Tech Park', 'client_name' => 'Google', 'location' => 'Singapore']);
        $u7 = Unit::create(['project_id' => $tech->id, 'unit_type' => 'KONE MonoSpace 700', 'equipment_number' => 'TP-A1', 'category' => UnitCategory::ELEVATOR]);
        \App\Services\UnitService::generateStagesAndTasks($u7);
        $this->randomizeStatusUpdates($u7);
        $this->completeStages($u7, [1, 2]);

        $u8 = Unit::create(['project_id' => $tech->id, 'unit_type' => 'KONE MonoSpace 700', 'equipment_number' => 'TP-B1', 'category' => UnitCategory::ELEVATOR]);
        \App\Services\UnitService::generateStagesAndTasks($u8);
        $this->randomizeStatusUpdates($u8);
        $this->completeStages($u8, [1, 2, 3, 4]);

        // 5. Ocean View Residency (1 Unit)
        $ocean = Project::create(['name' => 'Ocean View Residency', 'client_name' => 'Damac', 'location' => 'Miami']);
        $u9 = Unit::create(['project_id' => $ocean->id, 'unit_type' => 'KONE MonoSpace 700', 'equipment_number' => 'OV-101', 'category' => UnitCategory::ELEVATOR]);
        \App\Services\UnitService::generateStagesAndTasks($u9);
        $this->randomizeStatusUpdates($u9);
        // Complete all including Ride Comfort
        $this->completeStages($u9, [1, 2, 3, 4, 5, 6, 7]);
        \App\Models\RideComfortResult::create([
            'unit_id' => $u9->id,
            'vibration_value' => 0.3,
            'noise_db' => 45.0,
            'jerk_value' => 0.6,
            'passed' => true,
            'device_used' => 'eva_625',
        ]);

        // 6. Central Station (2 Units)
        $station = Project::create(['name' => 'Central Station', 'client_name' => 'Transport Auth', 'location' => 'Berlin']);
        $u10 = Unit::create(['project_id' => $station->id, 'unit_type' => 'KONE MonoSpace 700', 'equipment_number' => 'CS-L1', 'category' => UnitCategory::ELEVATOR]);
        \App\Services\UnitService::generateStagesAndTasks($u10);
        $this->randomizeStatusUpdates($u10);
        $this->completeStages($u10, [1, 2, 3, 4, 5]);

        $u11 = Unit::create(['project_id' => $station->id, 'unit_type' => 'KONE MonoSpace 700', 'equipment_number' => 'CS-L2', 'category' => UnitCategory::ELEVATOR]);
        \App\Services\UnitService::generateStagesAndTasks($u11);
        $this->randomizeStatusUpdates($u11);
        // No progress

        // 7-18. Additional Projects
        for ($i = 7; $i <= 18; $i++) {
            $p = Project::create([
                'name' => 'Project '.$i,
                'client_name' => 'Client '.$i,
                'location' => 'Location '.$i,
            ]);
            $u = Unit::create([
                'project_id' => $p->id,
                'unit_type' => 'KONE MonoSpace 500',
                'equipment_number' => 'UNIT-'.$i,
                'category' => UnitCategory::ELEVATOR,
            ]);
            \App\Services\UnitService::generateStagesAndTasks($u);
            $this->randomizeStatusUpdates($u);

            // Randomly complete some stages
            $completeCount = rand(0, 5);
            if ($completeCount > 0) {
                $this->completeStages($u, range(1, $completeCount));
            }
        }
    }

    private function randomizeStatusUpdates(Unit $unit)
    {
        foreach ($unit->statusUpdates as $update) {
            // 20% chance to stay null
            if (rand(1, 100) <= 20) {
                continue;
            }

            $statuses = Status::cases();
            $status = $statuses[array_rand($statuses)];
            $update->update(['status' => $status]);

            // Add revisions
            if ($status !== Status::IN_PROGRESS) {
                // Add some submitted revisions
                $subRevCount = rand(0, 3);
                for ($i = 0; $i < $subRevCount; $i++) {
                    StatusRevision::create([
                        'status_update_id' => $update->id,
                        'category' => \App\Enums\StatusRevisionCategory::SUBMITTED,
                        'revision_number' => $i,
                        'pdf_path' => null,
                        'revision_date' => now()->subDays(rand(5, 10)),
                    ]);
                }

                // Add some rejected revisions if needed or random
                $rejRevCount = rand(0, 2);
                for ($i = 0; $i < $rejRevCount; $i++) {
                    StatusRevision::create([
                        'status_update_id' => $update->id,
                        'category' => \App\Enums\StatusRevisionCategory::REJECTED,
                        'revision_number' => $i,
                        'pdf_path' => null,
                        'revision_date' => now()->subDays(rand(1, 5)),
                    ]);
                }
            }

            // Add approval if approved or rejected
            if ($status === Status::APPROVED || $status === Status::REJECTED) {
                StatusApproval::create([
                    'status_update_id' => $update->id,
                    'approval_code' => $status === Status::APPROVED ? ApprovalCode::A : ApprovalCode::B,
                    'pdf_path' => null,
                    'comment' => $status === Status::APPROVED ? 'Looks good.' : 'Missing information.',
                    'approved_at' => now()->subDays(rand(0, 5)),
                ]);
            }
        }
    }

    private function completeStages(Unit $unit, array $stageNumbers)
    {
        foreach ($stageNumbers as $stageNum) {
            // Fetch fresh stage instance to ensure we have latest state
            $stage = $unit->stages()->whereHas('template', function ($q) use ($stageNum) {
                $q->where('stage_number', $stageNum);
            })->first();
            if ($stage) {
                // Sort tasks by template order_index to allow sequential completion
                $tasks = $stage->tasks->sortBy(fn ($t) => $t->template->order_index);

                foreach ($tasks as $task) {
                    \App\Services\TaskService::updateStatus(
                        $task,
                        'pass',
                        'Seeder Auto',
                        1
                    );
                }
                // Stage check logic is handled by TaskService now
            }
        }
    }
}
