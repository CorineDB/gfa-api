<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveSlugColumnFromSourcesDeVerificationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(Schema::hasTable('sources_de_verification')){
            Schema::table('sources_de_verification', function (Blueprint $table) {
                if(Schema::hasColumn('sources_de_verification', 'slug')){
                    $table->dropColumn('slug');
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
        if(Schema::hasTable('sources_de_verification')){
            Schema::table('sources_de_verification', function (Blueprint $table) {
                if(!Schema::hasColumn('sources_de_verification', 'slug')){
                    $table->string('slug')->unique();
                }
            });
        }
    }
}
