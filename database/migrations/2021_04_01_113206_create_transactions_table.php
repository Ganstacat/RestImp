<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();

            $table->integer('quantity')->unsigned();
            $table->foreignId('buyer_id')->constrained('users');
            $table->foreignId('product_id')->constrained('users');
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
        Schema::dropIfExists('transactions');
    }
}
