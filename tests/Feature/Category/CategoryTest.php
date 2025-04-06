<?php
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it("can create a category", function () {
    $category = Category::factory()->create();
    expect($category)->toBeInstanceOf(Category::class)
        ->and($category->name)->not->tobeNull();
});