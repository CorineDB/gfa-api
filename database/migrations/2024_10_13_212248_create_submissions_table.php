<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSubmissionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('submissions', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('enqueteDeCollecteId')->unsigned();
            $table->foreign('enqueteDeCollecteId')->references('id')->on('enquete_de_collete_forms')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->bigInteger('organisationId')->unsigned()->after("enqueteDeCollecteId");
            $table->foreign('organisationId')->references('id')->on('organisations')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->bigInteger('submittedBy')->unsigned();
            $table->foreign('submittedBy')->references('id')->on('users')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->boolean('statut')->default(0);
            $table->longText('commentaire')->nullable();
            $table->datetime('submitted_at');
            $table->timestamps();
            $table->softDeletes();
        });

        if(Schema::hasTable('reponses_collecter')){
            Schema::table('reponses_collecter', function (Blueprint $table) {
                $table->bigInteger('submissionId')->nullable()->unsigned();
                $table->foreign('submissionId')->references('id')->on('submissions')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');
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
        Schema::dropIfExists('submissions');
    }
}
