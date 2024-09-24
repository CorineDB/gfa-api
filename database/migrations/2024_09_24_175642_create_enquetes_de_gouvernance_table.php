<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEnquetesDeGouvernanceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(!Schema::hasTable('enquetes_de_gouvernance')){
            Schema::create('enquetes_de_gouvernance', function (Blueprint $table) {
                $table->id();
                $table->string('code')->unique();
                $table->string('title')->unique();
                $table->mediumText('objectif');
                $table->longText('description')->nullable();
                $table->date('debut');
                $table->date('fin');
                $table->jsonb('planning');
                $table->bigInteger('programmeId')->unsigned();
                $table->foreign('programmeId')->references('id')->on('programmes')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');
                $table->bigInteger('enqueteId')->unsigned()->nullable();
                /*$table->foreign('enqueteId')->references('id')->on('enquetes')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');*/
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
        Schema::dropIfExists('enquetes');
    }
}
