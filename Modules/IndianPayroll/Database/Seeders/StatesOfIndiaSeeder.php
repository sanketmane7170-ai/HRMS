<?php

namespace Modules\IndianPayroll\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\IndianPayroll\Entities\IpState;

class StatesOfIndiaSeeder extends Seeder
{
    /**
     * pt_applicable / lwf_applicable reflect which states currently levy Professional Tax /
     * Labour Welfare Fund. Both lists change by state notification from time to time —
     * the slab amounts themselves live in ip_pt_slabs / ip_lwf_rules (admin-editable),
     * this flag only controls whether the calculators run for that state at all.
     */
    public function run(): void
    {
        $states = [
            ['name' => 'Andhra Pradesh', 'code' => 'AP', 'region_type' => 'state', 'pt' => true, 'lwf' => true],
            ['name' => 'Arunachal Pradesh', 'code' => 'AR', 'region_type' => 'state', 'pt' => false, 'lwf' => false],
            ['name' => 'Assam', 'code' => 'AS', 'region_type' => 'state', 'pt' => true, 'lwf' => false],
            ['name' => 'Bihar', 'code' => 'BR', 'region_type' => 'state', 'pt' => true, 'lwf' => false],
            ['name' => 'Chhattisgarh', 'code' => 'CG', 'region_type' => 'state', 'pt' => false, 'lwf' => false],
            ['name' => 'Goa', 'code' => 'GA', 'region_type' => 'state', 'pt' => true, 'lwf' => true],
            ['name' => 'Gujarat', 'code' => 'GJ', 'region_type' => 'state', 'pt' => true, 'lwf' => true],
            ['name' => 'Haryana', 'code' => 'HR', 'region_type' => 'state', 'pt' => false, 'lwf' => true],
            ['name' => 'Himachal Pradesh', 'code' => 'HP', 'region_type' => 'state', 'pt' => false, 'lwf' => false],
            ['name' => 'Jharkhand', 'code' => 'JH', 'region_type' => 'state', 'pt' => false, 'lwf' => true],
            ['name' => 'Karnataka', 'code' => 'KA', 'region_type' => 'state', 'pt' => true, 'lwf' => true],
            ['name' => 'Kerala', 'code' => 'KL', 'region_type' => 'state', 'pt' => true, 'lwf' => true],
            ['name' => 'Madhya Pradesh', 'code' => 'MP', 'region_type' => 'state', 'pt' => true, 'lwf' => false],
            ['name' => 'Maharashtra', 'code' => 'MH', 'region_type' => 'state', 'pt' => true, 'lwf' => true],
            ['name' => 'Manipur', 'code' => 'MN', 'region_type' => 'state', 'pt' => false, 'lwf' => false],
            ['name' => 'Meghalaya', 'code' => 'ML', 'region_type' => 'state', 'pt' => true, 'lwf' => false],
            ['name' => 'Mizoram', 'code' => 'MZ', 'region_type' => 'state', 'pt' => false, 'lwf' => false],
            ['name' => 'Nagaland', 'code' => 'NL', 'region_type' => 'state', 'pt' => false, 'lwf' => false],
            ['name' => 'Odisha', 'code' => 'OD', 'region_type' => 'state', 'pt' => true, 'lwf' => true],
            ['name' => 'Punjab', 'code' => 'PB', 'region_type' => 'state', 'pt' => false, 'lwf' => true],
            ['name' => 'Rajasthan', 'code' => 'RJ', 'region_type' => 'state', 'pt' => false, 'lwf' => false],
            ['name' => 'Sikkim', 'code' => 'SK', 'region_type' => 'state', 'pt' => true, 'lwf' => false],
            ['name' => 'Tamil Nadu', 'code' => 'TN', 'region_type' => 'state', 'pt' => false, 'lwf' => true],
            ['name' => 'Telangana', 'code' => 'TG', 'region_type' => 'state', 'pt' => true, 'lwf' => true],
            ['name' => 'Tripura', 'code' => 'TR', 'region_type' => 'state', 'pt' => true, 'lwf' => false],
            ['name' => 'Uttar Pradesh', 'code' => 'UP', 'region_type' => 'state', 'pt' => false, 'lwf' => false],
            ['name' => 'Uttarakhand', 'code' => 'UK', 'region_type' => 'state', 'pt' => false, 'lwf' => false],
            ['name' => 'West Bengal', 'code' => 'WB', 'region_type' => 'state', 'pt' => true, 'lwf' => true],
            ['name' => 'Andaman and Nicobar Islands', 'code' => 'AN', 'region_type' => 'union_territory', 'pt' => false, 'lwf' => false],
            ['name' => 'Chandigarh', 'code' => 'CH', 'region_type' => 'union_territory', 'pt' => false, 'lwf' => false],
            ['name' => 'Dadra and Nagar Haveli and Daman and Diu', 'code' => 'DN', 'region_type' => 'union_territory', 'pt' => false, 'lwf' => false],
            ['name' => 'Delhi', 'code' => 'DL', 'region_type' => 'union_territory', 'pt' => false, 'lwf' => true],
            ['name' => 'Jammu and Kashmir', 'code' => 'JK', 'region_type' => 'union_territory', 'pt' => false, 'lwf' => false],
            ['name' => 'Ladakh', 'code' => 'LA', 'region_type' => 'union_territory', 'pt' => false, 'lwf' => false],
            ['name' => 'Lakshadweep', 'code' => 'LD', 'region_type' => 'union_territory', 'pt' => false, 'lwf' => false],
            ['name' => 'Puducherry', 'code' => 'PY', 'region_type' => 'union_territory', 'pt' => false, 'lwf' => true],
        ];

        foreach ($states as $state) {
            IpState::updateOrCreate(
                ['code' => $state['code']],
                [
                    'name' => $state['name'],
                    'region_type' => $state['region_type'],
                    'pt_applicable' => $state['pt'],
                    'lwf_applicable' => $state['lwf'],
                    'is_active' => true,
                ]
            );
        }
    }
}
