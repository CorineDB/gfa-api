<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateObjectifSpecifiquesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('objectif_specifiques', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->longText('description');
            $table->bigInteger('objectifable_id')->unsigned();
            $table->string('objectifable_type');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('objectif_specifiques');
    }
}
