<?php

use Gzero\Core\Models\Language;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLanguage extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'languages',
            function (Blueprint $table) {
                $table->string('code', 2);
                $table->string('i18n', 5);
                $table->boolean('is_enabled')->default(false);
                $table->boolean('is_default')->default(false);
                $table->primary('code');
                $table->timestamps();
            }
        );

        // Seed languages
        $this->seedLangs();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('languages');
    }

    /**
     * Seed langs
     *
     * @return void
     */
    private function seedLangs()
    {
        Language::firstOrCreate(
            [
                'code'       => 'en',
                'i18n'       => 'en_US',
                'is_enabled' => true
            ]
        );

        Language::firstOrCreate(
            [
                'code'       => 'pl',
                'i18n'       => 'pl_PL',
                'is_enabled' => true
            ]
        );

        Language::firstOrCreate(
            [
                'code'       => 'de',
                'i18n'       => 'de_DE',
                'is_enabled' => false
            ]
        );

        Language::firstOrCreate(
            [
                'code'       => 'fr',
                'i18n'       => 'fr_FR',
                'is_enabled' => false
            ]
        );

        Language::where('code', config('app.locale'))->update(['is_default' => true]);
    }
}
