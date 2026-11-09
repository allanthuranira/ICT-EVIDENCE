<?php

namespace Database\Seeders;

use App\Models\Campus;
use App\Models\PerformanceItem;
use App\Models\Recipient;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@ksg.ac.ke'],
            ['name' => 'System Administrator', 'password' => Hash::make('password')]
        );

        $campuses = [
            ['name' => 'Lower Kabete', 'slug' => 'lower-kabete'],
            ['name' => 'ELiTi', 'slug' => 'eliti'],
            ['name' => 'Embu', 'slug' => 'embu'],
            ['name' => 'Baringo', 'slug' => 'baringo'],
            ['name' => 'Mombasa', 'slug' => 'mombasa'],
            ['name' => 'Matuga', 'slug' => 'matuga'],
            ['name' => 'ICT HQ', 'slug' => 'hq', 'is_hq' => true],
        ];
        foreach ($campuses as $c) {
            Campus::updateOrCreate(['slug' => $c['slug']], $c + ['active' => true]);
        }

        // TODO: replace with the real campus heads / representatives list
        $hq = Campus::where('slug', 'hq')->first();
        Recipient::updateOrCreate(
            ['campus_id' => $hq->id, 'email' => 'representative.hq@ksg.ac.ke'],
            ['name' => 'HQ ICT Representative', 'role' => 'representative']
        );
        Recipient::updateOrCreate(
            ['campus_id' => $hq->id, 'email' => 'ddict@ksg.ac.ke'],
            ['name' => 'Deputy Director ICT', 'role' => 'ict_head']
        );

        $this->seedPerformanceItems();
    }

    private function seedPerformanceItems(): void
    {
        // PC targets FY 2026/2027 (from FINAL ADICT PC FY 2026-2027).
        // quarters = in which quarters evidence/status is required.
        $pc = [
            ['code' => 'A1',  'name' => 'Cost Cutting', 'q' => [1,2,3,4], 'unit' => '%', 'target' => 100],
            ['code' => 'B1.1','name' => 'Digitization of Government Services', 'q' => [1,2,3], 'unit' => '%', 'target' => 100],
            ['code' => 'B1.2','name' => 'Productivity Improvement', 'q' => [1,2,3,4], 'unit' => '%', 'target' => 100],
            ['code' => 'B1.3','name' => 'National Values and Principles of Governance', 'q' => [1,2,3,4], 'unit' => '%', 'target' => 100],
            ['code' => 'B1.4','name' => 'Asset Management', 'q' => [1,2,3,4], 'unit' => '%', 'target' => 100],
            ['code' => 'B1.5','name' => 'National Tree Growing and Restoration Campaign', 'q' => [1,2,3,4], 'unit' => '%', 'target' => 100],
            ['code' => 'B1.6','name' => 'Corruption Prevention', 'q' => [1,2,3,4], 'unit' => '%', 'target' => 100],
            ['code' => 'B1.7','name' => 'Zero Fault Audit', 'q' => [1,2,3,4], 'unit' => '%', 'target' => 100],
            ['code' => 'B2.1','name' => 'ICT Gaps Analysis Carried Out', 'q' => [1,2], 'unit' => '%', 'target' => 100],
            ['code' => 'B2.2','name' => 'Digitalized Systems Implemented', 'q' => [1,2,3,4], 'unit' => 'No.', 'target' => 3],
            ['code' => 'B2.3','name' => 'ICT Infrastructure Upgraded', 'q' => [2,3,4], 'unit' => '%', 'target' => 100],
            ['code' => 'C1.1','name' => 'Performance Management Culture Institutionalized', 'q' => [1,2,3,4], 'unit' => '%', 'target' => 100],
            ['code' => 'D1.1','name' => 'Implementation of Department Service Delivery Charter', 'q' => [1,2,3,4], 'unit' => '%', 'target' => 100],
            ['code' => 'D1.2','name' => 'Competence Development', 'q' => [1,2], 'unit' => '%', 'target' => 100],
            ['code' => 'D1.3','name' => 'Quality Management Systems Implemented', 'q' => [2,3,4], 'unit' => '%', 'target' => 100],
            ['code' => 'D1.4','name' => 'Risk Register Implemented', 'q' => [1,2,3,4], 'unit' => '%', 'target' => 100],
        ];
        foreach ($pc as $row) {
            PerformanceItem::updateOrCreate(
                ['type' => 'pc', 'code' => $row['code'], 'fy_start_year' => 2026],
                [
                    'name' => $row['name'],
                    'unit_of_measure' => $row['unit'],
                    'annual_target' => $row['target'],
                    'q1' => in_array(1, $row['q']), 'q2' => in_array(2, $row['q']),
                    'q3' => in_array(3, $row['q']), 'q4' => in_array(4, $row['q']),
                    'active' => true,
                ]
            );
        }

        // Workplan KRA items FY 2026/2027 (from ICT Departmental Work Plan).
        $kra = [
            ['code' => 'KRA 1.1','name' => 'Digital teaching tools, learning platforms and knowledge-sharing provided', 'q' => [1,2,3,4]],
            ['code' => 'KRA 1.2','name' => 'Digitized evaluation dashboard implemented', 'q' => [1,2,3,4]],
            ['code' => 'KRA 2.1','name' => 'Digital consultancy assignment tracking system implemented', 'q' => [1,2,3,4]],
            ['code' => 'KRA 3.1','name' => 'Research webpage upgraded', 'q' => [1,2]],
            ['code' => 'KRA 4.1','name' => 'Department risk register reviewed & mitigation monitored', 'q' => [1,2,3,4]],
            ['code' => 'KRA 4.2','name' => 'Department registers operationalized and monitored', 'q' => [1,2,3,4]],
            ['code' => 'KRA 4.3','name' => 'Legal compliance audit recommendations implemented', 'q' => [1,2,3,4]],
            ['code' => 'KRA 4.4','name' => 'Department Annual Work Plan developed and implemented', 'q' => [1,2,3,4]],
            ['code' => 'KRA 4.5','name' => 'Performance contract developed and implemented', 'q' => [1,2,3,4]],
            ['code' => 'KRA 4.6','name' => 'Bribery and Corruption Risk Mitigation Plan implemented', 'q' => [1,2,3,4]],
            ['code' => 'KRA 4.7','name' => 'Staff performance appraisal systems implemented', 'q' => [1,2,3,4]],
            ['code' => 'KRA 5.1','name' => 'Inventory, asset maintenance and disposal systems implemented', 'q' => [1,2,3,4]],
            ['code' => 'KRA 5.2','name' => 'ICT Strategy developed', 'q' => [1,2]],
            ['code' => 'KRA 5.3','name' => 'ICT Needs Assessment undertaken and recommendations implemented', 'q' => [1,2,3,4]],
            ['code' => 'KRA 5.4','name' => 'Technology-enabled solutions and digital tools implemented', 'q' => [1,2,3,4]],
            ['code' => 'KRA 5.5','name' => 'Digital platforms maintained and updated', 'q' => [1,2,3,4]],
            ['code' => 'KRA 5.6','name' => 'ICT infrastructure upgraded', 'q' => [2,3,4]],
            ['code' => 'KRA 5.7','name' => 'Integrated customer feedback and service tracking systems', 'q' => [1,2,3,4]],
            ['code' => 'KRA 5.8','name' => 'Staff sensitized on ICT systems management and digital literacy', 'q' => [1,2,3,4]],
            ['code' => 'KRA 5.9','name' => 'ICT security and data protection improved', 'q' => [1,2,3,4]],
            ['code' => 'KRA 5.10','name' => 'Institutional physical infrastructure modernized and maintained', 'q' => [1,2,3,4]],
            ['code' => 'KRA 5.11','name' => 'Quality standards, service consistency and operational excellence enhanced', 'q' => [1,2,3,4]],
            ['code' => 'KRA 5.12','name' => 'Citizens Service Delivery Charter reviewed and implemented', 'q' => [1,2,3,4]],
        ];
        foreach ($kra as $row) {
            PerformanceItem::updateOrCreate(
                ['type' => 'workplan', 'code' => $row['code'], 'fy_start_year' => 2026],
                [
                    'name' => $row['name'],
                    'q1' => in_array(1, $row['q']), 'q2' => in_array(2, $row['q']),
                    'q3' => in_array(3, $row['q']), 'q4' => in_array(4, $row['q']),
                    'active' => true,
                ]
            );
        }
    }
}
