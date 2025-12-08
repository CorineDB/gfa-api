<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIndicateurValueKeysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('indicateur_value_keys', function (Blueprint $table) {
            $table->id();
            $table->string('libelle')->unique()->default("");
            $table->string('key')->unique()->default("moy");
            $table->longText('description')->nullable();
            $table->string('type')->default("int");
            $table->bigInteger('uniteeMesureId')->unsigned();
			$table->foreign('uniteeMesureId')->references('id')->on('unitees')
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
        Schema::dropIfExists('indicateur_value_keys');
    }
}
