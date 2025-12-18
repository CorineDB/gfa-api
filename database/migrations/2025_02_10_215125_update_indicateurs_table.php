<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateIndicateursTable extends Migration
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

                if(Schema::hasColumn('indicateurs', 'anneeDeBase')){
                    $table->string('anneeDeBase')->nullable()->change();
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

                if(Schema::hasColumn('indicateurs', 'anneeDeBase')){
                    $table->string('anneeDeBase')->nullable(false)->change();
                }

            });
        }
    }
}
