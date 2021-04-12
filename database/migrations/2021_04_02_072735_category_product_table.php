<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CategoryProductTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('category_product', function (Blueprint $table) {
            // Pivot table
            $table->foreignId('category_id')->constrained();
            $table->foreignId('product_id')->constrained();
            //also, we can use ...('users')->onDelete('cascade'), so
            //when user is deleted, all related entires would be delted too.
            //withoud 'cascade', SQL will trow exception, if we try to delelete
            //constrained entry.
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('category_product');
    }
}
