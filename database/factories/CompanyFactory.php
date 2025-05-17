<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Company;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Company>
 */
class CompanyFactory extends Factory
{
    protected $model = Company::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            "name"=>$this->faker->company,
            'user_id' => User::factory(),
            "email"=>$this->faker->unique()->safeEmail,
            "phone_number"=>$this->faker->phoneNumber,
            "website"=>$this->faker->url
        ];
    }
}
