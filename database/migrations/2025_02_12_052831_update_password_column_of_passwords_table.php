<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdatePasswordColumnOfPasswordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(Schema::hasTable('passwords')){
            Schema::table('passwords', function (Blueprint $table) {

                if(Schema::hasColumn('passwords', 'password')){
                    $table->string('password')->nullable()->change();
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
        if(Schema::hasTable('passwords')){
            Schema::table('passwords', function (Blueprint $table) {

                if(Schema::hasColumn('passwords', 'password')){
                    $table->string('password')->nullable(false)->change();
                }

            });
        }
    }
}
