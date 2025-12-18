<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCategoriesFactuelDeGouvernanceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('categories_factuel_de_gouvernance', function (Blueprint $table) {
            $table->id();

            $table->integer('position')->default(0);
            $table->bigInteger('categorieFactuelDeGouvernanceId')->nullable()->unsigned();
            $table->foreign('categorieFactuelDeGouvernanceId', 'cfg_parent_fk')->references('id')->on('categories_factuel_de_gouvernance')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->morphs('categorieable', 'categorieFactuel');

            $table->bigInteger('formulaireFactuelId')->nullable()->unsigned();
            $table->foreign('formulaireFactuelId')->references('id')->on('formulaires_factuel_de_gouvernance')
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
        Schema::dropIfExists('categories_factuel_de_gouvernance');
    }
}
