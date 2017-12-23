<?php

use Gzero\Core\Models\FileType;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFilesTable extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'file_types',
            function (Blueprint $table) {
                $table->increments('id');
                $table->string('name')->unique();
                $table->timestamps();
            }
        );

        Schema::create(
            'files',
            function (Blueprint $table) {
                $table->increments('id');
                $table->integer('type_id')->unsigned()->nullable();
                $table->string('extension')->nullable();
                $table->integer('size')->nullable();
                $table->string('mime_type')->nullable();
                $table->text('info')->nullable();
                $table->integer('author_id')->unsigned()->nullable();
                $table->boolean('is_active')->default(false);
                $table->timestamps();
                $table->foreign('author_id')->references('id')->on('users')->onDelete('SET NULL');
                $table->foreign('type_id')->references('id')->on('file_types')->onDelete('SET NULL');
            }
        );

        Schema::create(
            'file_translations',
            function (Blueprint $table) {
                $table->increments('id');
                $table->string('language_code', 2);
                $table->integer('file_id')->unsigned();
                $table->integer('author_id')->unsigned()->nullable();
                $table->string('title')->nullable();
                $table->text('description')->nullable();
                $table->timestamps();
                $table->unique(['file_id', 'language_code']);
                $table->foreign('file_id')->references('id')->on('files')->onDelete('CASCADE');
                $table->foreign('author_id')->references('id')->on('users')->onDelete('SET NULL');
                $table->foreign('language_code')->references('code')->on('languages')->onDelete('CASCADE');
            }
        );

        Schema::create(
            'uploadables',
            function (Blueprint $table) {
                $table->integer('file_id')->unsigned()->index();
                $table->integer('uploadable_id')->unsigned();
                $table->string('uploadable_type');
                $table->integer('weight')->default(0);
                $table->timestamps();
                $table->foreign('file_id')->references('id')->on('files')->onDelete('CASCADE');
            }
        );

        // Seed file types
        $this->seedFileTypes();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('uploadables');
        Schema::dropIfExists('file_translations');
        Schema::dropIfExists('files');
        Schema::dropIfExists('file_types');
    }

    /**
     * Seed file types
     *
     * @return void
     */
    private function seedFileTypes()
    {
        foreach (['image', 'document', 'video', 'music'] as $type) {
            FileType::firstOrCreate(['name' => $type]);
        }
    }

}
