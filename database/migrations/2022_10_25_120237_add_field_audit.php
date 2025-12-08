<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldAudit extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('audits', function(Blueprint $table) {

            if (!Schema::hasColumn('audits', 'projetId')) {
			    $table->bigInteger('projetId')->unsigned()->nullable();

                $table->foreign('projetId')->references('id')->on('projets')
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
