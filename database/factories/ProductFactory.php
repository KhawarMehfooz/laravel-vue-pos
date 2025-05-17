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

    // Keep track of used barcodes to ensure uniqueness
    protected static $usedBarcodes = [];

    /**
     * Generate a unique numeric barcode string
     * 
     * @return string
     */
    protected function generateUniqueBarcode(): string
    {
        do {
            // Simple 13-digit numeric barcode (common length for EAN-13)
            // Using numerify to ensure we get only numbers
            $barcode = $this->faker->numerify('###############');

        } while (in_array($barcode, self::$usedBarcodes));
        
        // Remember this barcode
        self::$usedBarcodes[] = $barcode;
        
        return $barcode;
    }

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            "name" => $this->faker->word,
            "user_id" => User::factory(),
            "category_id" => Category::factory(),
            "company_id" => Company::factory(),
            "shelf_number" => $this->faker->randomLetter() . $this->faker->numberBetween(1, 500),
            "purchase_price" => function () {
                return $price = $this->faker->randomFloat(2, 1, 100); // between 1.00 and 100.00
            },
            "retail_price" => function (array $attributes) {
                $markup = $this->faker->randomFloat(2, 1.1, 1.5); // markup between 10% and 50%
                return round($attributes['purchase_price'] * $markup, 2);
            },
            "barcode" => function() {
                return $this->generateUniqueBarcode();
            },
        ];
    }
}