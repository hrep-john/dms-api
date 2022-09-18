<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReportBuildersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('report_builders', function (Blueprint $table) {
            $table->id();
            $table->string('module');
            $table->string('name');
            $table->string('slug');
            $table->text('format');

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('report_builders', function (Blueprint $table) {
            $table->dropForeign('report_builders_created_by_foreign');
            $table->dropForeign('report_builders_updated_by_foreign');
        });

        Schema::dropIfExists('report_builders');
    }
}
