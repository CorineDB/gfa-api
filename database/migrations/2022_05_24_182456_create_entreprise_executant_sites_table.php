<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEntrepriseExecutantSitesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('entreprise_executant_sites', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('entrepriseExecutantId')->unsigned();
            $table->bigInteger('siteId')->unsigned();
            $table->bigInteger('programmeId')->unsigned();
            $table->foreign('entrepriseExecutantId')->references('id')->on('entreprise_executants')
						->onDelete('cascade')
						->onUpdate('cascade');
            $table->foreign('siteId')->references('id')->on('sites')
						->onDelete('cascade')
						->onUpdate('cascade');
            $table->foreign('programmeId')->references('id')->on('programmes')
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
        Schema::dropIfExists('entreprise_executant_sites');
    }
}
