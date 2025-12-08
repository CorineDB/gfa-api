<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            //
            $table->boolean('first_connexion')->default(0);
            $table->boolean('statut')->default(0);
            $table->timestamp('account_verification_request_sent_at')->nullable();
            $table->timestamp('password_update_at')->nullable();
            $table->string('last_password_remember')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([ "first_connexion", "statut", "account_verification_request_sent_at", "password_update_at", "last_password_remember" ]);
        });
    }
}
