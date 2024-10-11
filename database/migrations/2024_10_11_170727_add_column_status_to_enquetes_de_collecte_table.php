<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnStatusToEnquetesDeCollecteTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(Schema::hasTable('enquetes_de_collecte')){
            Schema::table('enquetes_de_collecte', function (Blueprint $table) {

                if(!Schema::hasColumn('enquetes_de_collecte', 'statut')){
                    $table->boolean('statut')->default(0)->before("fin");
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
        if(Schema::hasTable('enquetes_de_collecte')){
            Schema::table('enquetes_de_collecte', function (Blueprint $table) {

                if(Schema::hasColumn('enquetes_de_collecte', 'statut')){
                    $table->dropColumn(['statut']);
                }

            });
        }
    }
}
