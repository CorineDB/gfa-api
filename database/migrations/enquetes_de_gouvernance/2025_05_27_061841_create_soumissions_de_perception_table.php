<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSoumissionsDePerceptionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('soumissions_de_perception', function (Blueprint $table) {
            $table->id();

            $table->string('identifier_of_participant')->nullable();
            $table->enum('categorieDeParticipant', ['membre_de_conseil_administration', 'employe_association', 'membre_association', 'partenaire'])->nullable();
            $table->string('sexe')->nullable();
            $table->string('age')->nullable();
            $table->bigInteger('submittedBy')->unsigned()->nullable();
            $table->foreign('submittedBy', 'sdp_submittedby_fk')->references('id')->on('users')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->boolean('statut')->default(0);
            $table->longText('commentaire')->nullable();
            $table->datetime('submitted_at')->nullable();
            $table->bigInteger('evaluationId')->unsigned();
            $table->foreign('evaluationId', 'sdp_eval_fk')->references('id')->on('evaluations_de_gouvernance')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->bigInteger('formulaireDePerceptionId')->unsigned();
            $table->foreign('formulaireDePerceptionId', 'sdp_formperc_fk')->references('id')->on('formulaires_de_perception_de_gouvernance')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');
            $table->bigInteger('organisationId')->unsigned();
            $table->foreign('organisationId', 'sdp_org_fk')->references('id')->on('organisations')
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

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('soumissions_de_perception');
    }
}
