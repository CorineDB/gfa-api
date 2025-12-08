<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEnquetesDeCollecteTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(!Schema::hasTable('enquetes_de_collecte')){
            Schema::create('enquetes_de_collecte', function (Blueprint $table) {
                $table->id();
                $table->string('nom')->unique();
                $table->mediumText('objectif');
                $table->longText('description')->nullable();
                $table->date('debut');
                $table->date('fin');
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
        Schema::dropIfExists('enquetes');
    }
}
