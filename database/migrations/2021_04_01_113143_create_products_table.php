<?php

use App\Models\Product;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('description', 1000);
            $table->integer('quantity')->unsigned();
            $table->string('status')->default(Product::UNAVAILABLE_PRODUCT);
            $table->string('image');
            $table->foreignId('seller_id')->constrained('users');
            //also, we can use ...('users')->onDelete('cascade'), so
            //when user is deleted, all related entires would be delted too.
            //withoud 'cascade', SQL will trow exception, if we try to delelete
            //constrained entry.

            $table->timestamps();
            $table->softDeletes(); //deleted_at
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('products');
    }
}
