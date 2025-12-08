<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateFichesDeSyntheseTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('fiches_de_synthese');

        if(!Schema::hasTable('fiches_de_synthese')){
            Schema::create('fiches_de_synthese', function (Blueprint $table) {
                $table->id();

                if (!Schema::hasColumn('fiches_de_synthese', 'indice_de_gouvernance')) {
                    $table->float('indice_de_gouvernance')->default(0);
                }
                if (!Schema::hasColumn('fiches_de_synthese', 'resultats')) {
                    $table->jsonb("resultats")->nullable();
                }

                $table->enum('type', ['factuel', 'perception']);
                $table->jsonb("synthese");
                $table->dateTime('evaluatedAt')->default(now());

                $table->morphs('formulaireDeGouvernance', 'formulaire');

                if(!Schema::hasColumn('fiches_de_synthese', 'evaluationDeGouvernanceId')){
                    $table->bigInteger('evaluationDeGouvernanceId')->nullable()->unsigned();
                    $table->foreign('evaluationDeGouvernanceId')->references('id')->on('evaluations_de_gouvernance')
                        ->onDelete('cascade')
                        ->onUpdate('cascade');
                }

                if(!Schema::hasColumn('fiches_de_synthese', 'organisationId')){
                    $table->bigInteger('organisationId')->nullable()->unsigned();
                    $table->foreign('organisationId')->references('id')->on('organisations')
                        ->onDelete('cascade')
                        ->onUpdate('cascade');
                }

                if(!Schema::hasColumn('fiches_de_synthese', 'programmeId')){
                    $table->bigInteger('programmeId')->nullable()->unsigned();
                    $table->foreign('programmeId')->references('id')->on('programmes')
                        ->onDelete('cascade')
                        ->onUpdate('cascade');
                }

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
        Schema::dropIfExists('fiches_de_synthese');
        /* Schema::table('fiches_de_synthese', function (Blueprint $table) {
            // Remove polymorphic columns
            $table->dropMorphs('formulaireDeGouvernance');

            // Add back the original column and foreign key
            $table->unsignedBigInteger('formulaireDeGouvernanceId');
            $table->foreign('formulaireDeGouvernanceId')
                ->references('id')->on('formulaires_de_gouvernance') // or your original table
                ->onDelete('cascade');
        }); */
    }
}
