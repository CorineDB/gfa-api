<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateSubmittedAtColumnOfSurveyReponsesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(Schema::hasTable('survey_reponses')){
            Schema::table('survey_reponses', function (Blueprint $table) {
                $table->datetime('submitted_at')->nullable()->default(null)->change();
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
        if (Schema::hasTable('survey_reponses')) {
            Schema::table('survey_reponses', function (Blueprint $table) {

                // Check if the column exists
                if(Schema::hasColumn('survey_reponses', 'submitted_at')){
                    $table->datetime('submitted_at')->nullable(false)->change();
                }
                
            });
        }
    }
}
