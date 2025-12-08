<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProfilesDeGouvernanceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(!Schema::hasTable("profiles_de_gouvernance")){
            Schema::create('profiles_de_gouvernance', function (Blueprint $table) {
                $table->id();
                /*
                    $table->float('indice_factuel')->default(0);
                    $table->float('indice_de_perception')->default(0);
                    $table->float('indice_synthetique')->default(0);
                    $table->bigInteger('principeDeGouvernanceId')->unsigned();
                    $table->foreign('principeDeGouvernanceId')->references('id')->on('principes_de_gouvernance')
                        ->onDelete('cascade')
                        ->onUpdate('cascade');
                */
                $table->jsonb('resultat_synthetique')->nullable();
                $table->bigInteger('evaluationOrganisationId')->unsigned();
                $table->foreign('evaluationOrganisationId')->references('id')->on('evaluation_organisations')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');
                $table->bigInteger('organisationId')->unsigned();
                $table->foreign('organisationId')->references('id')->on('organisations')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');
                $table->bigInteger('evaluationDeGouvernanceId')->unsigned();
                $table->foreign('evaluationDeGouvernanceId')->references('id')->on('evaluations_de_gouvernance')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');
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
        Schema::dropIfExists('profiles_de_gouvernance');
    }
}
