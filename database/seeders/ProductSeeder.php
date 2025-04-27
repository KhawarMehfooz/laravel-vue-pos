<?php
namespace Database\Seeders;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Fetch the first registered user (or any user you want to assign products to)
        $user = User::first(); // You can change this to any specific user if needed

        // Ensure we have a valid user
        if ($user) {
            // Generate products for this user
            Product::factory()
                ->count(10) // You can adjust this number based on the maximum number of products you want
                ->create([
                    'user_id' => $user->id, // All products will be assigned to this user
                ]);
        } else {
            // If no user exists, create a new one for testing
            $user = User::factory()->create();
            Product::factory()
                ->count(10)
                ->create([
                    'user_id' => $user->id,
                ]);
        }
    }
}
