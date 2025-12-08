<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCategoriesDeGouvernanceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('categories_de_gouvernance', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('categorieDeGouvernanceId')->nullable()->unsigned();
            $table->foreign('categorieDeGouvernanceId')->references('id')->on('categories_de_gouvernance')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->morphs('categorieable', 'categorie');
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
        Schema::dropIfExists('categories_de_gouvernance');
    }
}
