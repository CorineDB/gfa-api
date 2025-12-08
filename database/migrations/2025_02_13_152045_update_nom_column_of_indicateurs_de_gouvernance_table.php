<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateNomColumnOfIndicateursDeGouvernanceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(Schema::hasTable('indicateurs_de_gouvernance')){
            Schema::table('indicateurs_de_gouvernance', function (Blueprint $table) {
                if(Schema::hasColumn('indicateurs_de_gouvernance', 'nom')){
                    $table->longText('nom')->nullable(false)->change();
                }
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
        if(Schema::hasTable('indicateurs_de_gouvernance')){
            Schema::table('indicateurs_de_gouvernance', function (Blueprint $table) {
                if(Schema::hasColumn('indicateurs_de_gouvernance', 'nom')){
                    $table->string('nom')->nullable(false)->change();
                }
            });
        }
    }
}
