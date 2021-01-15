<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->integer('chat_id');
            $table->integer('group_id')->nullable();
            $table->string('username');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('spotify_api_token')->nullable();
            $table->integer('interests_set')->default(0);
            $table->string('interest_code_1')->nullable();
            $table->string('interest_name_1')->nullable();
            $table->string('interest_code_2')->nullable();
            $table->string('interest_name_2')->nullable();
            $table->string('interest_code_3')->nullable();
            $table->string('interest_name_3')->nullable();
            $table->string('interest_code_4')->nullable();
            $table->string('interest_name_4')->nullable();
            $table->string('interest_code_5')->nullable();
            $table->string('interest_name_5')->nullable();
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
        Schema::dropIfExists('users');
    }
}
