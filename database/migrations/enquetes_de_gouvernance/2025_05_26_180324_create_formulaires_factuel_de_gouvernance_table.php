<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFormulairesFactuelDeGouvernanceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('formulaires_factuel_de_gouvernance', function (Blueprint $table) {
            $table->id();
            $table->longText('libelle')->unique();
            $table->longText('description')->nullable();
            $table->bigInteger('created_by')->unsigned();
            $table->foreign('created_by')->references('id')->on('users')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->bigInteger('programmeId')->unsigned();
            $table->foreign('programmeId')->references('id')->on('programmes')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->unique(['libelle', 'programmeId']);
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
        Schema::dropIfExists('formulaires_factuel_de_gouvernance');
    }
}
