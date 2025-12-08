<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateColNamePta extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('projets', function (Blueprint $table) {
            $table->longText('nom')->change();
            $table->longText('description')->nullable()->change();
        });

        Schema::table('composantes', function (Blueprint $table) {
            $table->longText('nom')->change();
            $table->longText('description')->nullable()->change();
        });

        Schema::table('activites', function (Blueprint $table) {
            $table->longText('nom')->change();
            $table->longText('description')->nullable()->change();
        });

        Schema::table('taches', function (Blueprint $table) {
            $table->longText('nom')->change();
            $table->longText('description')->nullable()->change();
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
