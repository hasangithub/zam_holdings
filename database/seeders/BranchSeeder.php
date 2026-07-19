<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Branch;

class BranchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $branches = [
            [
                'name' => 'Head Office 1',
                'code' => 'HO1',
            ],
            [
                'name' => 'Head Office 2',
                'code' => 'HO2',
            ],
            [
                'name' => 'Head Office 3',
                'code' => 'HO3',
            ],
            [
                'name' => 'Jaela Factory',
                'code' => 'JFL',
            ],
            [
                'name' => 'Ragama Factory',
                'code' => 'RFL',
            ],
            [
                'name' => 'Market',
                'code' => 'MKT',
            ],
            [
                'name' => 'Zam Com',
                'code' => 'ZCM',
            ],
            [
                'name' => 'Zam Wadi',
                'code' => 'ZWD',
            ],
            [
                'name' => 'MRE',
                'code' => 'MRE',
            ],
            [
                'name' => 'MRS',
                'code' => 'MRS',
            ],
            [
                'name' => 'MRK',
                'code' => 'MRK',
            ],
        ];

        foreach ($branches as $branch) {
            Branch::updateOrCreate(
                ['code' => $branch['code']],
                $branch
            );
        }
    }
}