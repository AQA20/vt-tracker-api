<?php

namespace Database\Seeders;

use App\Models\DeliveryModule;
use App\Models\DeliveryModuleContent;
use Illuminate\Database\Seeder;

class DeliveryCatalogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $catalog = [
            'Module_0_Documentation' => [
                '0_Documentation',
            ],
            'Module_1_Machinery_unit' => [
                '1. Machinery module',
                '1.1 Machinery',
                '1.1 Machinery Bedplate',
            ],
            'Module_2_Guide_rails' => [
                '2. Guide rail module',
                '2.1 Guide rails up to floor XX',
            ],
            'Module_3_Shaft_fixing_equipment' => [
                '3. Shaft fixing equipment module',
                '3.1.A Shaft mechanics package',
                '3.1.B Pit Equipment',
                '3.1.C Guide rail Fixings and Fish plates',
                '3.1.D Ropes',
                '3.1.E Installation Platform',
                '3.2.A Installation accessories',
                '3.2.A Plumbing template',
                '3.2.B Buffers',
                '3.2.B Buffer Extensions',
                '3.2.B Compensation Chain Guides',
                '3.2.B Pit accessories',
                '3.2.C Guide rail Fixings and and Fish plates',
                '3.2.C Rest of guide rail fixings',
                '3.2.C Machine room slab penetration protection plates',
                '3.2.C Rope hole silencers',
                '3.2.C Sway protection',
                '3.2.D Suspension ropes',
                '3.2.D Compensation ropes or chains',
                '3.2.D OSG rope',
                '3.2.E Installation Platform',
            ],
            'Module_4_Sling_and_safety_system' => [
                '4. Sling and safety system module',
                '4.1 Car Sling',
                '4.1 Counterweight',
                '4.1 Overspeed Governor and tension weight',
                '4.2 Car Sling frame',
                '4.2 Counterweight CWT frame',
                '4.2 Safety Gear for Sling and CWT (when applicable)',
                '4.2 Overspeed Governor package',
                '4.3 Temporary Car Sling',
                '4.3 OSG and tension weight with temporary OSG',
                '4.3 Car Sling frame',
                '4.3 Counterweight CWT frame',
                '4.3 Safety Gear for Sling and CWT (when applicable)',
                '4.3 Final OSG',
                '4.4. Pit ladder',
            ],
            'Module_5_Filler_bits' => [
                '5 Filler bits module',
                '5.1 50% of Filler bits',
                '5.1 Filler bits',
            ],
            'Module_6_Car' => [
                '6. Car module',
                '6.1 Raw Car',
                '6.1 Car decorations',
                '6.1 Car Ceiling',
                '6.2 Car floor',
                '6.2 Car walls and roof',
                '6.2 Car Ceiling',
                '6.3 Car',
                '6.3 Alta car Fairings (RC3)',
            ],
            'Module_7_Electrical_system' => [
                '7. Electrical system module',
                '7.1 Drive',
                '7.1 Control Panel / MAP',
                '7.1 Traveling cables',
                '7.1 Electrification package',
                '7.2 Shaft bundle',
                '7.2 Machine room & shaft trunking',
                '7.2 Control panel',
                '7.2 Drive',
                '7.2 ETS / NTS ramp',
                '7.2 Emergency battery drive (EBD)',
                '7.2 Destination Control system cabling /LAN',
                '7.2 Destination: SW updates',
                '7.2 Destination: Konexion',
                '7.2 Destination: ACU',
                '7.2 Destination: Intercom unit',
                '7.2 UltraRope: Machineroom RAD',
                '7.2 UltraRope: Pit RAD',
            ],
            'Module_8_Signalisation' => [
                '8. Signalisation module',
                '8.1.A Landing Signalisation',
                '8.1.A Car Signalisation',
                '8.2.A Signalisation back boxes',
                '8.2.A Landing Signalisation Faceplates',
                '8.2.B Car Signalisation',
                '8.3.A Landing Call Stations',
                '8.3.A Hall Lanterns or Indicators',
                '8.3.A Other Landing Signal Devices',
                '8.3.A Destination: DOP',
                '8.3.A Destination: EID',
                '8.3.A Destination: DIN',
                '8.3.B Main COP',
                '8.3.B Auxialiry COP',
                '8.3.B Other Car Signal Devices',
                '8.3.B Destination: Door Jamb',
                '8.X CTU COP',
                '8.X CTU Landing Signalisation',
            ],
            'Module_9_Doors' => [
                '9. Door module',
                '9.1.A Car Door',
                '9.1.B Landing Doors up to floor XX',
                '9.2.A CTU Car Door panels',
                '9.2.A CTU Car Door sill',
                '9.2.A Final Car Door sill',
                '9.2.A Final Car Door panel',
                '9.2.B CTU Landing doors',
                '9.2.B Final Landing door panels',
                '9.2.B Final Landing Door sills',
                '9.3.A Car Door panels',
                '9.3.A Car Door sills',
                '9.3.A Curtain of light',
                '9.3.B Frames or Fronts',
                '9.3.B Architraves, Transoms',
                '9.3.B Landing door Railing',
                '9.3.B Landing door panels',
                '9.3.B Landing Door Sills',
                '9.3.B Shaft panels',
                '9.3.B KONE SAS',
            ],
            'Module_10_Door_Operator' => [
                '10. Door Operator',
            ],
            'Module_11_Electrical_accessories' => [
                '11. Electrical accessories module',
                '11.1. I – link',
                '11.1. PFI: E – link system',
                '11.1. PFI: KONE Access system',
                '11.1. PFI: KONE InfoScreen system',
                '11.1. PFI: KONE InfoScreen displays',
                '11.1. PFI: KONE Remote Call system',
                '11.1. PFI: 3rd party ELI interface',
                '11.1. PFI: Final Software',
                '11.1 Commercial multimedia system',
                '11.1 Commercial multimedia displays',
            ],
            'Module_12_PU_pulley_assembly' => [
                '12 PU pulley assembly',
            ],
            'Module_13_Rope_compensator' => [
                '13 Rope compensator',
            ],
            'Module_14_Dividing_beams_and_shaft_dividing_screens' => [
                '14.1 Shaft dividing beams and screens module',
                '14.1 Dividing beams',
                '14.1 Dividing screens',
                '14.1 Vertical beams',
                '14.2 Dividing screens up to floor XX',
                '14.2 Vertical beams up to floor XX',
                '14.2 Dividing beams up to floor XX',
            ],
            'Module_Jump' => [
                'Cathead',
                'Cathead Lifting Beams',
                'IT-platform',
                'Pit Plate',
                'Temporary guide brackets',
                'Rope Clamps and Anchorages',
                'OSG Rope Device',
                'Sling & CWT Temporary pulleys',
                'JL Electrification package',
                'Hoist packages',
                'Reel Stands',
                'Load Bearing Platform',
                'Protection decks',
                'Installation Training',
            ],
        ];

        foreach ($catalog as $moduleName => $contents) {
            $module = DeliveryModule::firstOrCreate(['name' => $moduleName]);
            foreach ($contents as $contentName) {
                DeliveryModuleContent::firstOrCreate([
                    'delivery_module_id' => $module->id,
                    'name' => $contentName,
                ]);
            }
        }
    }
}
