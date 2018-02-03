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
            $table->foreign('language_code')->references('code')->on('languages');
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

    /**
     * Create options based on given array.
     *
     * @param array $options
     *
     * @return void
     */
    public function createOptions(array $options)
    {

        // Propagate Lang options based on gzero config
        foreach ($options as $categoryKey => $category) {
            foreach ($options[$categoryKey] as $key => $option) {
                foreach (Language::all()->toArray() as $lang) {
                    $options[$categoryKey][$key][$lang['code']] = config('gzero.' . $categoryKey . '.' . $key);
                }
            }
        }

        // Seed options
        foreach ($options as $category => $option) {
            foreach ($option as $key => $value) {
                OptionCategory::find($category)->options()->create(
                    ['key' => $key, 'value' => $value]
                );
            }
        }
    }
}
