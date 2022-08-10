<?php

use App\Enums\UserDefinedFieldSection;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Query\Expression;

class CreateUserDefinedFieldsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_defined_fields', function (Blueprint $table) {
            $table->id();
            $table->integer('tenant_id');
            $table->string('entitable_type');
            $table->string('label');
            $table->string('key');
            $table->integer('section')->default(UserDefinedFieldSection::List);
            $table->smallInteger('type');
            $table->boolean('visible');
            $table->json('settings')->default(new Expression('(JSON_ARRAY())'));
            $table->timestamps();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
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
        Schema::table('user_defined_fields', function (Blueprint $table) {
            $table->dropForeign('user_defined_fields_created_by_foreign');
            $table->dropForeign('user_defined_fields_updated_by_foreign');
        });

        Schema::dropIfExists('user_defined_fields');
    }
}
