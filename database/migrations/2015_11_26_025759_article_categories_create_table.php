<?php

namespace {

    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Database\Migrations\Migration;

    class ArticleCategoriesCreateTable extends Migration
    {
        /**
         * Run the migrations.
         *
         * @return void
         */
        public function up()
        {
            Schema::create('article_categories', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name');
                $table->string('alias')->nullable();
                $table->string('image')->nullable();
                $table->text('description')->nullable();
                $table->unsignedInteger('parent_id')->default(0);
                $table->integer('order')->default(0);
                $table->integer('status')->default(1);
                $table->softDeletes();
                $table->timestamps();
            });
        }

        /**
         * Reverse the migrations.
         *
         * @return void
         */
        public function down()
        {
            //
        }
    }
}
