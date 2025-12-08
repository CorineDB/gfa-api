<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateActionablesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(!Schema::hasTable('actionables')){
            Schema::create('actionables', function (Blueprint $table) {
                $table->id();
                $table->bigInteger('action_a_mener_id')->unsigned();
                $table->foreign('action_a_mener_id')->references('id')->on('actions_a_mener')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');
                $table->morphs('actionable');
                $table->bigInteger('programmeId')->unsigned();
                $table->foreign('programmeId')->references('id')->on('programmes')
                            ->onDelete('cascade')
                            ->onUpdate('cascade');
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('actionables');
    }
}
