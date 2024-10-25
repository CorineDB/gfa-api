<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSoumissionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('soumissions', function (Blueprint $table) {
            $table->id();
            
            $table->bigInteger('evaluationId')->unsigned();
            $table->foreign('evaluationId')->references('id')->on('evaluations_de_gouvernance')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->bigInteger('formulaireDeGouvernanceId')->unsigned();
            $table->foreign('formulaireDeGouvernanceId')->references('id')->on('formulaires_de_gouvernance')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');

            $table->bigInteger('organisationId')->unsigned();
            $table->foreign('organisationId')->references('id')->on('organisations')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            
            $table->bigInteger('programmeId')->unsigned();
            $table->foreign('programmeId')->references('id')->on('programmes')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->enum('type', ['factuel', 'perception']);
            $table->json('comite_members')->nullable();
            $table->bigInteger('submittedBy')->unsigned()->nullable();
            $table->foreign('submittedBy')->references('id')->on('users')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->boolean('statut')->default(0);
            $table->longText('commentaire')->nullable();
            $table->datetime('submitted_at');
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
        Schema::dropIfExists('soumissions');
    }
}
