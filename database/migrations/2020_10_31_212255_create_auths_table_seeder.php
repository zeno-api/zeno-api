<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAuthsTableSeeder extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('auths', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('driver', 50);
            $table->boolean('cache')->default(false);
            $table->integer('cache_ttl')->nullable();
            $table->text('options');
            $table->timestamps();
        });

        Schema::table('routes', function (Blueprint $table) {
            $table->uuid('auth_id')->nullable();

            $table->foreign('auth_id')->references('id')->on('auths');

            $table->index('auth_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('routes', function (Blueprint $table) {
            $table->dropColumn(['auth_id']);
        });

        Schema::dropIfExists('auths');
    }
}
