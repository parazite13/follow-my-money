<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableAccount extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('account', function(Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->string('name');
        });

        Schema::table('transaction', function (Blueprint $table) {
            $table->string('account_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('account');
        
        Schema::table('transaction', function (Blueprint $table) {
            $table->dropColumn('account_id');
        });
    }
}
