<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSlug extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('category', function (Blueprint $table) {
            $table->string('slug');
        });

        Schema::table('subcategory', function (Blueprint $table) {
            $table->string('slug');
        });

        Schema::table('account', function (Blueprint $table) {
            $table->string('slug');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('category', function (Blueprint $table) {
            $table->dropColumn('slug');
        });

        Schema::table('subcategory', function (Blueprint $table) {
            $table->dropColumn('slug');
        });

        Schema::table('account', function (Blueprint $table) {
            $table->dropColumn('slug');
        });
    }
}
