<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Company;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            "name"=>$this->faker->word,
            "user_id"=>User::factory(),
            "category_id"=>Category::factory(),
            "company_id"=>Company::factory(),
            "shelf_number" => $this->faker->randomLetter() . $this->faker->numberBetween(1, 500), 
        ];
    }
}
