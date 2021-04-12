<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
    	DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        // \App\Models\User::factory(10)->create();
        User::truncate();
        Category::truncate();
        Product::truncate();
        Transaction::truncate();
        DB::table('category_product')->truncate();

        User::flushEventListeners(); // So it wont send emails when we're using seeder
        Category::flushEventListeners();
        Product::flushEventListeners();
        Transaction::flushEventListeners();

        User::factory()->count(1000)->create();
        Category::factory()->count(30)->create();

        Product::factory()->count(1000)->create()->each(
        	function($product){
        		$categories = Category::all()->random(mt_rand(1, 5))->pluck('id');
        		$product->categories()->attach($categories);
        	}
        );
        
        Transaction::factory()->count(1000)->create();  
    }
}
