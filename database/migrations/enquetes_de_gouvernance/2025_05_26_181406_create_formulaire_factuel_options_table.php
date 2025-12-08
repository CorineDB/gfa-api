<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFormulaireFactuelOptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('formulaire_factuel_options', function (Blueprint $table) {
            $table->id();
            $table->float('point');

            $table->boolean('preuveIsRequired')->default(false);
            $table->boolean('sourceIsRequired')->default(false);
            $table->boolean('descriptionIsRequired')->default(false);

            $table->bigInteger('optionId')->unsigned();
            $table->foreign('optionId')->references('id')->on('options_de_reponse_gouvernance')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->bigInteger('formulaireFactuelId')->unsigned();
            $table->foreign('formulaireFactuelId')->references('id')->on('formulaires_factuel_de_gouvernance')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->bigInteger('programmeId')->unsigned();
            $table->foreign('programmeId')->references('id')->on('programmes')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->unique(['point', 'optionId', 'formulaireFactuelId', 'programmeId'],
            'ffopt_point_option_formulaire_programme_unique');
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
        Schema::dropIfExists('formulaire_factuel_options');
    }
}
