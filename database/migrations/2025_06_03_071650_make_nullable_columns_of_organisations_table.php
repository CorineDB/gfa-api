<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeNullableColumnsOfOrganisationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(Schema::hasTable('organisations')){
            Schema::table('organisations', function (Blueprint $table) {
                if(Schema::hasColumn('organisations', 'pays')){
                    $table->string('pays')->default('Bénin')->nullable()->change();
                }
                if(Schema::hasColumn('organisations', 'departement')){
                    $table->string('departement')->default('Litoral')->nullable()->change();
                }
                if(Schema::hasColumn('organisations', 'commune')){
                    $table->string('commune')->default('Cotonou')->nullable()->change();
                }
                if(Schema::hasColumn('organisations', 'arrondissement')){
                    $table->string('arrondissement')->default('Sènadé')->nullable()->change();
                }
                if(Schema::hasColumn('organisations', 'quartier')){
                    $table->string('quartier')->default('Sènadé')->nullable()->change();
                }
                if(Schema::hasColumn('organisations', 'secteurActivite')){
                    $table->string('secteurActivite')->default('Environnement')->nullable()->change();
                }
                if(Schema::hasColumn('organisations', 'longitude')){
                    $table->string('longitude')->nullable()->change();
                }
                if(Schema::hasColumn('organisations', 'latitude')){
                    $table->string('latitude')->nullable()->change();
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
        //
    }
}
