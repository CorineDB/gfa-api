<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIndicateursDeGouvernanceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('indicateurs_de_gouvernance', function (Blueprint $table) {
			$table->id();
			$table->string('nom')->unique();
			$table->longText('description')->nullable();
			$table->enum('type', ['factuel', 'perception']);
			$table->boolean('can_have_multiple_reponse');
			$table->morphs('principeable', 'principe'); // id and models type d'un principe de gouvernance ou d'un critere de gouvernance
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
        Schema::dropIfExists('indicateurs_de_gouvernance');
    }
}
