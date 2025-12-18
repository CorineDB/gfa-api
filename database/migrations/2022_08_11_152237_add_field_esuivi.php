<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldEsuivi extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('e_suivies', function(Blueprint $table) {

            if (!Schema::hasColumn('e_suivies', 'formulaireId')) {
                $table->bigInteger('formulaireId')->unsigned();

                $table->foreign('formulaireId')->references('id')->on('formulaires')
                            ->onDelete('cascade')
                            ->onUpdate('cascade');
            }
		});
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
