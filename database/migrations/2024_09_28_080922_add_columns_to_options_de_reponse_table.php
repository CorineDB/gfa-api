<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToOptionsDeReponseTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(Schema::hasTable('options_de_reponse')){
            Schema::table('options_de_reponse', function (Blueprint $table) {

                if(!Schema::hasColumn('options_de_reponse', 'note')){
                    $table->string('note');
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
        if(Schema::hasTable('options_de_reponse')){
            Schema::table('options_de_reponse', function (Blueprint $table) {
                if(Schema::hasColumn('options_de_reponse', 'note')){
                    $table->dropColumn('note');
                }
            });
        }
    }
}
