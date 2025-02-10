<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAddressColumnToOrganisationsTable extends Migration
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

                if(!Schema::hasColumn('organisations', 'addresse')){
                    $table->string('addresse')->nullable();
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
        if(Schema::hasTable('organisations')){
            Schema::table('organisations', function (Blueprint $table) {

                if(Schema::hasColumn('organisations', 'addresse')){
                    $table->dropColumn('addresse');
                }

            });
        }
    }
}
