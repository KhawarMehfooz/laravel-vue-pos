<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Company;
use App\Models\User;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        if($users->isEmpty()){
            User::factory(1)->create();
            $users = User::all();
        }
        Company::factory()
        ->count(300)
        ->make()
        ->each(function($company) use ($users){
            $company->user_id = $users->random()->id;
            $company->save();
        });
    }
}
