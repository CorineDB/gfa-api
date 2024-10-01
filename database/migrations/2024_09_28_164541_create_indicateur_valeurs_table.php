<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIndicateurValeursTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(!Schema::hasTable("indicateur_valeurs")){
            Schema::create('indicateur_valeurs', function (Blueprint $table) {
                $table->id();
                $table->morphs('indicateur_valueable', 'valueable');
                $table->bigInteger('indicateurValueKeyId')->unsigned();
                $table->foreign('indicateurValueKeyId')->references('id')->on('indicateur_value_keys')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');
                $table->string('value')->nullable();
                $table->text('commentaire')->nullable();
                $table->bigInteger('indicateurId')->unsigned()->nullable();
                $table->foreign('indicateurId')->references('id')->on('indicateurs')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');
                $table->timestamps();
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
        Schema::dropIfExists('indicateur_valeurs');
    }
}
