<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRoute extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'routes',
            function (Blueprint $table) {
                $table->increments('id');
                $table->string('language_code', 2);
                $table->string('path')->index();
                $table->integer('routable_id')->unsigned()->nullable();
                $table->string('routable_type')->nullable();
                $table->boolean('is_active')->default(false);
                $table->foreign('language_code')->references('code')->on('languages')->onDelete('CASCADE');
                $table->unique(['language_code', 'path']); // Unique path in specific language
                $table->timestamps();
            }
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('routes');
    }

}
