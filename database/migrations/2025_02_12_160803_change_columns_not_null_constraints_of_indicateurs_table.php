<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeColumnsNotNullConstraintsOfIndicateursTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(Schema::hasTable('indicateurs')){
            Schema::table('indicateurs', function (Blueprint $table) {

                if(Schema::hasColumn('indicateurs', 'methode_de_la_collecte')){
                    $table->string('methode_de_la_collecte')->nullable()->change();
                }

                if(Schema::hasColumn('indicateurs', 'frequence_de_la_collecte')){
                    $table->string('frequence_de_la_collecte')->nullable()->change();
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
        if(Schema::hasTable('indicateurs')){
            Schema::table('indicateurs', function (Blueprint $table) {

                if(Schema::hasColumn('indicateurs', 'methode_de_la_collecte')){
                    $table->string('methode_de_la_collecte')->nullable(false)->change();
                }

                if(Schema::hasColumn('indicateurs', 'frequence_de_la_collecte')){
                    $table->string('frequence_de_la_collecte')->nullable(false)->change();
                }

            });
        }
    }
}
