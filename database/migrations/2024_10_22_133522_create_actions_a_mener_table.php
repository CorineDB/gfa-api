<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateActionsAMenerTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(!Schema::hasTable('actions_a_mener')){
            Schema::create('actions_a_mener', function (Blueprint $table) {
                $table->id();
                $table->longText('action');
                $table->nullableMorphs('actionable');
                $table->datetime('start_at')->nullable();
                $table->datetime('end_at')->nullable();
                $table->boolean('statut')->default(0);
                $table->boolean('est_valider')->default(0);
                $table->datetime('validated_at')->nullable();
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
        Schema::dropIfExists('actions_a_mener');
    }
}
