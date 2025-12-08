<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSourcesDeDonneeColumnToSuiviIndicateursTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(Schema::hasTable('suivi_indicateurs')){
            Schema::table('suivi_indicateurs', function (Blueprint $table) {
                if(!Schema::hasColumn('suivi_indicateurs', 'sources_de_donnee')){
                    $table->longText('sources_de_donnee')->nullable();
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
        if(Schema::hasTable('suivi_indicateurs')){
            Schema::table('suivi_indicateurs', function (Blueprint $table) {
                if(Schema::hasColumn('suivi_indicateurs', 'sources_de_donnee')){
                    $table->dropColumn('sources_de_donnee');
                }
            });
        }
    }
}
