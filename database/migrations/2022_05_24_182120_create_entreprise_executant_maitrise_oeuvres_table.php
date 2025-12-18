<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEntrepriseExecutantMaitriseOeuvresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('entreprise_executant_maitrise_oeuvres', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('maitriseOeuvreId')->unsigned();
            $table->bigInteger('entrepriseExecutantId')->unsigned();
            $table->foreign('maitriseOeuvreId')->references('id')->on('maitrise_oeuvres')
						->onDelete('cascade')
						->onUpdate('cascade');
            $table->foreign('entrepriseExecutantId', 'e_executant_id_foreign')->references('id')->on('entreprise_executants')
						->onDelete('cascade')
						->onUpdate('cascade');
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
        Schema::dropIfExists('entreprise_executant_maitrise_oeuvres');
    }
}
