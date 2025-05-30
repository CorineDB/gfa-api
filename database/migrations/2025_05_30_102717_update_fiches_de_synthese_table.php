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
        Schema::table('fiches_de_synthese', function (Blueprint $table) {
            // Step 1: Drop foreign key constraint
            $table->dropForeign(['formulaireDeGouvernanceId']);

            // Step 2: Drop old column
            $table->dropColumn('formulaireDeGouvernanceId');

            // Step 3: Add polymorphic columns
            $table->morphs('formulaireDeGouvernance', 'formulaire');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('fiches_de_synthese', function (Blueprint $table) {
            // Remove polymorphic columns
            $table->dropMorphs('formulaireDeGouvernance');

            // Add back the original column and foreign key
            $table->unsignedBigInteger('formulaireDeGouvernanceId');
            $table->foreign('formulaireDeGouvernanceId')
                ->references('id')->on('formulaires_de_gouvernance') // or your original table
                ->onDelete('cascade');
        });
    }
}
