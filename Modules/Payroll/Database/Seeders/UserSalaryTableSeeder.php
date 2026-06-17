<?php

namespace Modules\Payroll\Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\Payroll\Entities\UserSalary;

class UserSalaryTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();
        // UserSalary::truncate();
        // User::pluck('id')->each(function ($user_id) {
        //     UserSalary::factory(1)->create([
        //         'user_id' => $user_id
        //     ]);
        // });
        User::all()->each(function ($user) {
            UserSalary::firstOrCreate([
                'user_id' => $user->id
            ], UserSalary::factory()->make()->toArray());
        });
    }
}
