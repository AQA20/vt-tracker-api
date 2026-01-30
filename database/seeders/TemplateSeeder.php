<?php

namespace Database\Seeders;

use App\Enums\UnitCategory;
use App\Models\StageTemplate;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $unitType = 'KONE MonoSpace 700';
        
        $stages = [
            [
                'stage_number' => 1,
                'title' => 'Plumbing',
                'description' => 'Initial inspection of plumbing',
                'tasks' => [
                    ['code' => '1.1', 'title' => 'Check shaft level', 'req_meas' => true, 'min' => 0, 'max' => 10],
                    ['code' => '1.2', 'title' => 'Check shaft verticality', 'req_meas' => true, 'min' => 0, 'max' => 15],
                    ['code' => '1.3', 'title' => 'Confirm plumbing clearances', 'req_meas' => true, 'min' => 20, 'max' => 100],
                    ['code' => '1.4', 'title' => 'Verify sump pit dimensions', 'req_meas' => true, 'min' => 1000, 'max' => 3000],
                    ['code' => '1.5', 'title' => 'Verify overhead clearance', 'req_meas' => true, 'min' => 3500, 'max' => 5000],
                ]
            ],
            [
                'stage_number' => 2,
                'title' => 'First Guide Rails',
                'description' => 'Inspect first set of guide rails',
                'tasks' => [
                    ['code' => '2.1', 'title' => 'Distance between car guiderails (DBG -0/+1 mm)', 'req_meas' => true, 'min' => 0, 'max' => 1], // Assuming relative measurement
                    ['code' => '2.2', 'title' => 'Distance between counterweight guiderails', 'req_meas' => true, 'min' => 0, 'max' => 5],
                    ['code' => '2.3', 'title' => 'Diagonal distance between counterweight guiderails', 'req_meas' => true, 'min' => 0, 'max' => 5],
                    ['code' => '2.4', 'title' => 'Guide rails parallel', 'req_meas' => false],
                    ['code' => '2.5', 'title' => 'All fixings tight', 'req_meas' => false],
                    ['code' => '2.6', 'title' => 'Buffers straight and same height', 'req_meas' => false],
                    ['code' => '2.7', 'title' => 'Overspeed governor tension weight', 'req_meas' => false],
                ]
            ],
            [
                'stage_number' => 3,
                'title' => 'Car Sling & Car/Counterweight',
                'description' => 'Inspect car sling and counterweight',
                'tasks' => [
                    ['code' => '3.1', 'title' => 'Car sling horizontal alignment', 'req_meas' => false],
                    ['code' => '3.2', 'title' => 'Car sling vertical alignment', 'req_meas' => false],
                    ['code' => '3.3', 'title' => 'Counterweight installation', 'req_meas' => false],
                    ['code' => '3.4', 'title' => 'Suspension ropes correct groove', 'req_meas' => false],
                    ['code' => '3.5', 'title' => 'Rope twist limit', 'req_meas' => false],
                    ['code' => '3.6', 'title' => 'Car leveling at floors', 'req_meas' => false],
                ]
            ],
            [
                'stage_number' => 4,
                'title' => 'Rest of Guide Rails',
                'description' => 'Remaining guide rails inspection',
                'tasks' => [
                    ['code' => '4.1', 'title' => 'Remaining car guide rails alignment', 'req_meas' => false],
                    ['code' => '4.2', 'title' => 'Remaining counterweight guide rails alignment', 'req_meas' => false],
                    ['code' => '4.3', 'title' => 'Fixings tight', 'req_meas' => false],
                    ['code' => '4.4', 'title' => 'Buffers checked', 'req_meas' => false],
                    ['code' => '4.5', 'title' => 'Governor rope tension', 'req_meas' => false],
                ]
            ],
            [
                'stage_number' => 5,
                'title' => 'Landing/Car Doors',
                'description' => 'Check doors functionality',
                'tasks' => [
                    ['code' => '5.1', 'title' => 'Car door alignment', 'req_meas' => false],
                    ['code' => '5.2', 'title' => 'Landing door alignment', 'req_meas' => false],
                    ['code' => '5.3', 'title' => 'Safety edges function', 'req_meas' => false],
                    ['code' => '5.4', 'title' => 'Door sensors check', 'req_meas' => false],
                    ['code' => '5.5', 'title' => 'Interlocks working', 'req_meas' => false],
                ]
            ],
            [
                'stage_number' => 6,
                'title' => 'Before Commissioning',
                'description' => 'Final pre-commission checks',
                'tasks' => [
                    ['code' => '6.1', 'title' => 'Suspension ropes in correct grooves', 'req_meas' => false],
                    ['code' => '6.2', 'title' => 'Rope twist limits checked', 'req_meas' => false],
                    ['code' => '6.3', 'title' => 'Rope lubrication', 'req_meas' => false],
                    ['code' => '6.4', 'title' => 'Machine room check', 'req_meas' => false],
                    ['code' => '6.5', 'title' => 'Motor room check', 'req_meas' => false],
                    ['code' => '6.6', 'title' => 'Controller wiring verified', 'req_meas' => false],
                    ['code' => '6.7', 'title' => 'Safety gear lever', 'req_meas' => false],
                    ['code' => '6.8', 'title' => 'Counterweight guides', 'req_meas' => false],
                    ['code' => '6.9', 'title' => 'Buffer inspection', 'req_meas' => false],
                    ['code' => '6.10', 'title' => 'Overspeed governor', 'req_meas' => false],
                    ['code' => '6.11', 'title' => 'Door operators', 'req_meas' => false],
                    ['code' => '6.12', 'title' => 'Brake test', 'req_meas' => false],
                    ['code' => '6.13', 'title' => 'Emergency stop test', 'req_meas' => false],
                    ['code' => '6.14', 'title' => 'Emergency light', 'req_meas' => false],
                    ['code' => '6.15', 'title' => 'Alarm system', 'req_meas' => false],
                    ['code' => '6.16', 'title' => 'Fire service operation', 'req_meas' => false],
                    ['code' => '6.17', 'title' => 'Inspection certificate', 'req_meas' => false],
                    ['code' => '6.18', 'title' => 'Car lighting', 'req_meas' => false],
                    ['code' => '6.19', 'title' => 'Car emergency communication', 'req_meas' => false],
                    ['code' => '6.20', 'title' => 'Hoistway switches', 'req_meas' => false],
                    ['code' => '6.21', 'title' => 'Landing positions', 'req_meas' => false],
                    ['code' => '6.22', 'title' => 'Floor leveling', 'req_meas' => false],
                    ['code' => '6.23', 'title' => 'Control signals', 'req_meas' => false],
                    ['code' => '6.24', 'title' => 'Limit switches', 'req_meas' => false],
                    ['code' => '6.25', 'title' => 'Inspection of ropes', 'req_meas' => false],
                    ['code' => '6.26', 'title' => 'Rope terminations', 'req_meas' => false],
                    ['code' => '6.27', 'title' => 'Door sills and thresholds', 'req_meas' => false],
                    ['code' => '6.28-6.32', 'title' => 'Insulation resistance (electrical)', 'req_meas' => true, 'min' => 0.5, 'max' => 1000],
                    ['code' => '6.33-6.49', 'title' => 'Protective earth continuity', 'req_meas' => true, 'min' => 0, 'max' => 0.5],
                ]
            ],
            [
                'stage_number' => 7,
                'title' => 'Commissioning',
                'description' => 'Commissioning tasks',
                'tasks' => [
                    ['code' => '7.1', 'title' => 'Initial trial runs', 'req_meas' => false],
                    ['code' => '7.2', 'title' => 'Speed test', 'req_meas' => true, 'min' => 0.9, 'max' => 1.1],
                    ['code' => '7.3', 'title' => 'Floor leveling test', 'req_meas' => true, 'min' => -5, 'max' => 5],
                    ['code' => '7.4', 'title' => 'Load test', 'req_meas' => true, 'min' => 100, 'max' => 125],
                    ['code' => '7.5', 'title' => 'Emergency braking test', 'req_meas' => true, 'min' => 0, 'max' => 2],
                    ['code' => '7.6', 'title' => 'Door operation under load', 'req_meas' => false],
                    ['code' => '7.7', 'title' => 'Acceleration & deceleration smoothness', 'req_meas' => false],
                    ['code' => '7.8', 'title' => 'Safety tests', 'req_meas' => true, 'min' => 0, 'max' => 1],
                    ['code' => '7.9', 'title' => 'Final sign-off', 'req_meas' => false],
                ]
            ],
            [
                'stage_number' => 8,
                'title' => 'Ride Comfort',
                'description' => 'Special measurements: vibration, noise, jerk',
                'tasks' => []
            ],
        ];

        foreach ($stages as $stageData) {
            $progressGroup = null;
            if ($stageData['stage_number'] >= 1 && $stageData['stage_number'] <= 6) {
                $progressGroup = 'installation';
            } elseif ($stageData['stage_number'] >= 7 && $stageData['stage_number'] <= 8) {
                $progressGroup = 'commissioning';
            }

            $stage = StageTemplate::create([
                'unit_type' => $unitType,
                'category' => UnitCategory::ELEVATOR,
                'stage_number' => $stageData['stage_number'],
                'title' => $stageData['title'],
                'description' => $stageData['description'],
                'order_index' => $stageData['stage_number'],
                'progress_group' => $progressGroup,
            ]);

            $taskIndex = 1;
            foreach ($stageData['tasks'] as $taskData) {
                $stage->taskTemplates()->create([
                    'task_code' => $taskData['code'],
                    'title' => $taskData['title'],
                    'description' => $taskData['title'],
                    'order_index' => $taskIndex++,
                ]);
            }
        }
    }
}
