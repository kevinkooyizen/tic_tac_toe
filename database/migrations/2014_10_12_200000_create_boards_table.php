<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBoardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('boards', function (Blueprint $table) {
            $table->increments('id');
            $table->boolean('finished')->default(0);
            $table->string('top_left')->nullable();
            $table->string('top')->nullable();
            $table->string('top_right')->nullable();
            $table->string('left')->nullable();
            $table->string('center')->nullable();
            $table->string('right')->nullable();
            $table->string('bottom_left')->nullable();
            $table->string('bottom')->nullable();
            $table->string('bottom_right')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('boards');
    }
}
