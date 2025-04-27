<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;
use App\Models\User;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $categoriesList = [
            'Electronics',
            'Furniture',
            'Clothing',
            'Books',
            'Groceries',
            'Toys',
            'Sports',
            'Beauty',
            'Automotive',
            'Health',
            'Jewelry',
            'Appliances',
            'Office Supplies',
            'Tools',
            'Art',
            'Music',
            'Movies',
            'Video Games',
            'Pets',
            'Home Decor',
            'Food',
            'Kitchenware',
            'Baby Products',
            'Garden',
            'Outdoor',
            'Computers',
            'Mobile Phones',
            'Laptops',
            'Cameras',
            'Gaming Consoles',
            'Home Improvement',
            'Furniture Accessories',
            'Cleaning Products',
            'Lighting',
            'Bags',
            'Shoes',
            'Watches',
            'Eyewear',
            'Hats',
            'Socks',
            'Gloves',
            'Scarves',
            'Belts',
            'Wallets',
            'Handbags',
            'Accessories',
            'Smartwatches',
            'Headphones',
            'Smartphones',
            'Tablets',
            'Printers',
            'Lawn Care',
            'Tents',
            'Backpacks',
            'Camping Gear',
            'Fishing',
            'Hiking',
            'Bicycles',
            'Motorcycles',
            'Cars',
            'Boats',
            'SUVs',
            'Trucks',
            'Vans',
            'Electronics Accessories',
            'Cables',
            'Headsets',
            'Chairs',
            'Desks',
            'Beds',
            'Sofas',
            'Dressers',
            'Coffee Tables',
            'Rugs',
            'Curtains',
            'Wall Art',
            'Storage',
            'Mattresses',
            'Lamps',
            'Outdoor Furniture',
            'Pet Supplies',
            'Pet Food',
            'Dog Toys',
            'Cat Toys',
            'Aquarium Supplies',
            'Bird Accessories',
            'Pet Grooming',
            'Animal Care',
            'Organic Products',
            'Natural Supplements',
            'Herbal Medicine',
            'Vitamins',
            'Medical Equipment',
            'First Aid',
            'Dietary Supplements',
            'Sports Nutrition',
            'Yoga Mats',
            'Exercise Equipment',
            'Fitness Trackers',
            'Sports Apparel',
            'Weight Loss',
            'Running Shoes',
            'Cycling Gear',
            'Swimwear',
            'Ski Equipment',
            'Winter Clothing',
            'Camping Equipment',
            'Survival Gear',
            'Tactical Gear',
            'Boxing Equipment',
            'Martial Arts Gear',
            'Golf Equipment',
            'Tennis Equipment',
            'Fishing Rods',
            'Hunting Gear',
            'Backpacking',
            'Rock Climbing',
            'Hiking Boots'
        ];

        $userIds = User::all()->pluck('id')->toArray();

        if (empty($userIds)) {
            return;
        }

        $faker = Faker::create();

        foreach ($categoriesList as $categoryName) {
            $userId = $faker->randomElement($userIds);

            DB::table('categories')->insert([
                'user_id' => $userId,
                'name' => $categoryName,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
