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
        $projectNames = [
            'Skyline Tower', 'City Mall', 'The Grand Hotel', 'Tech Park',
            'Ocean View Residency', 'Central Station', 'Marina Plaza',
            'Golden Gate Complex', 'Riverside Towers', 'Phoenix Mall',
            'Crystal Heights', 'Diamond Square', 'Silver Lake Apartments',
            'Atlantic Business Center', 'Metropolitan Plaza', 'Royal Gardens',
            'Vista Boulevard', 'Empire State Complex', 'Horizon Towers',
            'Liberty Square', 'Victory Plaza', 'Heritage Center',
            'Innovation Hub', 'Summit Residences', 'Parkside Towers',
            'Waterfront Complex', 'Continental Building',
        ];

        $clientNames = [
            'Emaar', 'Westfield', 'Hilton', 'Google', 'Damac',
            'Transport Auth', 'Brookfield', 'Related Companies', 'Tishman Speyer',
            'Boston Properties', 'Vornado Realty', 'SL Green', 'Equity Residential',
            'AvalonBay', 'Simon Property Group', 'Prologis', 'Digital Realty',
            'Alexandria Real Estate', 'Welltower', 'Ventas', 'Duke Realty',
            'Kimco Realty', 'Regency Centers', 'Federal Realty', 'Brixmor',
            'Weingarten Realty', 'Acadia Realty',
        ];

        $locations = [
            'Dubai', 'London', 'New York', 'Singapore', 'Miami',
            'Berlin', 'Hong Kong', 'Tokyo', 'Paris', 'Sydney',
            'Toronto', 'Los Angeles', 'Chicago', 'San Francisco', 'Boston',
            'Seattle', 'Washington DC', 'Dallas', 'Atlanta', 'Houston',
            'Denver', 'Phoenix', 'Las Vegas', 'Portland', 'Austin',
            'Nashville', 'Orlando',
        ];

        // Create exactly 27 projects
        for ($i = 0; $i < 27; $i++) {
            $project = Project::create([
                'name' => $projectNames[$i],
                'kone_project_id' => 'KP'.str_pad(1584 + $i, 6, '0', STR_PAD_LEFT),
                'client_name' => $clientNames[$i],
                'location' => $locations[$i],
            ]);

            // Create 5-10 units per project
            $unitCount = rand(5, 10);
            for ($j = 1; $j <= $unitCount; $j++) {
                $unit = Unit::create([
                    'project_id' => $project->id,
                    'unit_type' => 'KONE MonoSpace '.(rand(0, 1) ? '700' : '500'),
                    'equipment_number' => strtoupper(substr($project->name, 0, 3)).'-'.str_pad($j, 3, '0', STR_PAD_LEFT),
                    'category' => UnitCategory::ELEVATOR,
                    'sl_reference_no' => 'SL-REF-'.($i * 100 + $j),
                    'fl_unit_name' => 'L'.$j,
                    'unit_description' => 'Unit '.$j.' for '.$project->name,
                ]);

                \App\Services\UnitService::generateStagesAndTasks($unit);
                $this->randomizeStatusUpdates($unit);

                // Randomly complete some stages (0-7 stages)
                $completeCount = rand(0, 7);
                if ($completeCount > 0) {
                    $this->completeStages($unit, range(1, $completeCount));
                }

                // Occasionally add a ride comfort result if stage 7 is complete
                if ($completeCount == 7 && rand(1, 100) <= 30) { // 30% chance
                    \App\Models\RideComfortResult::create([
                        'unit_id' => $unit->id,
                        'vibration_value' => round(rand(20, 50) / 100, 2),
                        'noise_db' => round(rand(40, 55) + (rand(0, 9) / 10), 1),
                        'jerk_value' => round(rand(50, 90) / 100, 2),
                        'passed' => rand(0, 1) == 1,
                        'device_used' => 'eva_625',
                    ]);
                }
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
