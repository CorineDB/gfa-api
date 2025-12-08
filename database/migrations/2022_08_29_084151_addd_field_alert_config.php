<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AdddFieldAlertConfig extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('alerte_configs', function (Blueprint $table) {
            
            $table->integer('nombreDeJourAvant')->nullable()->change();

            $table->integer('frequence')->nullable()->change();

            $table->integer('frequenceRapport')->nullable();

            $table->integer('debutSuivi')->nullable();

            $table->enum('frequenceBackup', [
                'everyMinute',
                'everyTwoMinutes',
                'everyThreeMinutes',
                'everyFourMinutes',
                'everyFiveMinutes',
                'everyTenMinutes',
                'everyFifteenMinutes',
                'everyThirtyMinutes',
                'hourly',
                'everyTwoHours',
                'everyThreeHours',
                'everyFourHours',
                'everySixHours',
                'daily',
                'weekly',
                'monthly',
                'quarterly',
                'yearly',
            ])->default('daily');


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
