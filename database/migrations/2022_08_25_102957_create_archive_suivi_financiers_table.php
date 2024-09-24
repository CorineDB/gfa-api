<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateArchiveSuiviFinanciersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('archive_suivi_financiers', function (Blueprint $table) {
            $table->id();
			$table->bigInteger('consommer');
			$table->integer('trimestre');
			$table->morphs('archive_suivi_financierable', 'archive_financierable');
			$table->bigInteger('activiteId')->unsigned();
			$table->bigInteger('ptabScopeId')->unsigned();
			$table->bigInteger('parentId')->unsigned();
			$table->integer('annee');

            $table->foreign('parentId')->references('id')->on('suivi_financiers')
            ->onDelete('cascade')
            ->onUpdate('cascade');
            $table->foreign('activiteId')->references('id')->on('archive_activites')
            ->onDelete('cascade')
            ->onUpdate('cascade');
            $table->foreign('ptabScopeId')->references('id')->on('ptab_scopes')
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
        Schema::dropIfExists('archive_suivi_financiers');
    }
}
