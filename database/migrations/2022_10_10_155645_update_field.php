<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateField extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reponses', function(Blueprint $table) {

            if (Schema::hasColumn('reponses', 'date')) {
			    $table->datetime('date')->change();
            }
		});

        Schema::table('e_suivies', function(Blueprint $table) {

            if (Schema::hasColumn('e_suivies', 'date')) {
			    $table->datetime('date')->change();
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
