<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateActionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('actions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('route_id');
            $table->uuid('service_id');
            $table->smallInteger('sequence')->default(0);
            $table->string('response_key', 100)->nullable();
            $table->string('destination');
            $table->text('options')->nullable();
            $table->timestamps();

            $table->foreign('route_id')->references('id')->on('routes')->onDelete('cascade');

            $table->unique(['route_id', 'response_key']);

            $table->index('route_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('actions');
    }
}
