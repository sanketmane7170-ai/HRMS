<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\Department;
use App\Models\Designation;
use App\Models\User;
use App\Models\UserDependent;
use App\Models\UserDocument;
use App\Models\UserProfile;
use App\Models\UserWorkDetail;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $countries = Country::limit(5)->get();
        $department = Department::first();
        $designation = Designation::where('department_id', $department->id)->get();

        // User::factory(1)->create([
        //     'department_id' => $department->id,
        //     'designation_id' => $designation->random(1)->first()->id
        // ])->each(function ($user) use ($countries) {
        //     UserProfile::factory(1)->create([
        //         'user_id' => $user->id,
        //         'country_id' => $countries->random(1)->first()->id
        //     ]);
        //     UserWorkDetail::factory(1)->create([
        //         'user_id' => $user->id,
        //     ]);
        //     UserDependent::factory(rand(1, 3))->create([
        //         'user_id' => $user->id
        //     ]);

        //     UserDocument::factory(rand(1, 2))->create([
        //         'user_id' => $user->id
        //     ]);
        // });
    }
}
