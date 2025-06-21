<?php

/**
 * Eloquent IFRS Accounting
 *
 * @author    Edward Mungai
 * @copyright Edward Mungai, 2020, Germany
 * @license   MIT
 */

use IFRS\Models\CostCenter;
use Illuminate\Database\Seeder;

class CostCenterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Common cost centers for different types of organizations
        $costCenters = [
            [
                'code' => 'ADM001',
                'name' => 'Administration',
                'description' => 'Administrative and general management activities',
                'active' => true,
            ],
            [
                'code' => 'FIN001',
                'name' => 'Finance Department',
                'description' => 'Financial planning and accounting activities',
                'active' => true,
            ],
            [
                'code' => 'HR001',
                'name' => 'Human Resources',
                'description' => 'Human resource management and payroll',
                'active' => true,
            ],
            [
                'code' => 'SAL001',
                'name' => 'Sales Department',
                'description' => 'Sales and marketing activities',
                'active' => true,
            ],
            [
                'code' => 'OPS001',
                'name' => 'Operations',
                'description' => 'Day-to-day operational activities',
                'active' => true,
            ],
            [
                'code' => 'IT001',
                'name' => 'Information Technology',
                'description' => 'IT infrastructure and support services',
                'active' => true,
            ],
            [
                'code' => 'FAC001',
                'name' => 'Facilities',
                'description' => 'Building maintenance and facility management',
                'active' => true,
            ],
        ];

        foreach ($costCenters as $costCenter) {
            CostCenter::create($costCenter);
        }
    }
}
