<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserInfosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_infos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->unique();
            $table->string('tenant_id');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('middle_name')->default('')->nullable();
            $table->string('mobile_number')->nullable();
            $table->string('sex')->nullable();
            $table->string('profile_picture_url')->nullable();
            $table->string('birthday')->nullable();
            $table->string('home_address')->nullable();
            $table->string('barangay')->nullable();
            $table->string('city')->nullable();
            $table->string('region')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_infos');
    }
}
