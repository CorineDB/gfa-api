<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCadreLogiqueIndicateursTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cadre_logique_indicateurs', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('sourceDeVerification');
            $table->longText('hypothese');
            $table->bigInteger('indicatable_id')->unsigned();
            $table->string('indicatable_type');
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
        Schema::dropIfExists('cadre_logique_indicateurs');
    }
}
