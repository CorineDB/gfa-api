<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEvaluationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(!Schema::hasTable('evaluations')){
            Schema::create('evaluations', function (Blueprint $table) {
                $table->id();
                $table->string('code')->unique();
                $table->date('start_at');
                $table->date('submitted_at');
                $table->bigInteger('enqueteId')->unsigned();
                $table->foreign('enqueteId')->references('id')->on('enquetes_de_gouvernance')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');
                $table->bigInteger('organisationId')->unsigned();
                $table->foreign('organisationId')->references('id')->on('organisations')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');
                $table->text('commentaire');
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
        Schema::dropIfExists('evaluations');
    }
}
