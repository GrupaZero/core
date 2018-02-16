<?php

use Gzero\Core\Models\Language;
use Gzero\Core\Models\OptionCategory;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddToUserLanguageAndTimezone extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('language_code', 2)->nullable();
            $table->string('timezone', 20)->nullable();
            $table->foreign('language_code')->references('code')->on('languages')->onDelete('SET NULL');
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
            $table->dropColumn('language_code');
            $table->dropColumn('timezone');
        });
    }
}
