<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateColumnCategorieIdFromIndicateursTable extends Migration
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
                if(Schema::hasColumn('indicateurs', 'categorieId')){
                    $table->bigInteger('categorieId')->unsigned()->nullable()->change();
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
                if(Schema::hasColumn('indicateurs', 'categorieId')){
                    $table->bigInteger('categorieId')->unsigned()->nullable(false)->change();
                }
            });
        }
    }
}
