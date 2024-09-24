<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateFieldFichiers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(Schema::hasTable('fichiers')) {
            Schema::table('fichiers', function (Blueprint $table) {
                if (Schema::hasColumn('fichiers', 'source')) {
                    $table->dropColumn('source');

                    $table->bigInteger('sharedId')->unsigned()->nullable();
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
