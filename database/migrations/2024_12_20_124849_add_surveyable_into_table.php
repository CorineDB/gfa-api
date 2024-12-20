<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSurveyableIntoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(Schema::hasTable('survey_forms')){
            Schema::table('survey_forms', function (Blueprint $table) {
                // Modify the columns to be nullable

                if(!Schema::hasColumn('survey_forms', 'survey_form_type')){
                    $table->string('survey_form_type')->nullable();
                }

                if(!Schema::hasColumn('survey_forms', 'survey_form_id')){
                    $table->unsignedBigInteger('survey_form_id')->nullable();
                }
            });
        }

        if(Schema::hasTable('surveys')){
            Schema::table('surveys', function (Blueprint $table) {
                // Modify the columns to be nullable

                if(!Schema::hasColumn('surveys', 'survey_type')){
                    $table->string('survey_type')->nullable();
                }

                if(!Schema::hasColumn('surveys', 'survey_id')){
                    $table->unsignedBigInteger('survey_id')->nullable();
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
        if(Schema::hasTable('survey_forms')){
            Schema::table('survey_forms', function (Blueprint $table) {
                // Revert the columns to NOT NULL
                if(Schema::hasColumn('survey_forms', 'survey_form_type')){
                    $table->dropColumn('survey_form_type');
                }

                if(Schema::hasColumn('survey_forms', 'survey_form_id')){
                    $table->dropColumn('survey_form_id');
                }
            });
        }
        
        if(Schema::hasTable('surveys')){
            Schema::table('surveys', function (Blueprint $table) {
                // Revert the columns to NOT NULL
                if(Schema::hasColumn('surveys', 'survey_type')){
                    $table->dropColumn('survey_type');
                }

                if(Schema::hasColumn('surveys', 'survey_id')){
                    $table->dropColumn('survey_id');
                }
            });
        }
    }
}
