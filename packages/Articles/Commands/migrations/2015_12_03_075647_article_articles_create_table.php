<?php

namespace {

    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Database\Migrations\Migration;

    class ArticleArticlesCreateTable extends Migration
    {
        /**
         * Run the migrations.
         *
         * @return void
         */
        public function up()
        {
            Schema::create('articles', function (Blueprint $table) {

                $table->increments('id');
                $table->unsignedInteger('user_id');
                $table->unsignedInteger('category_id');
                $table->text('title');
                $table->text('content');
                $table->string('alias')->nullable();
                $table->string('image')->nullable();
                $table->text('description')->nullable();
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
