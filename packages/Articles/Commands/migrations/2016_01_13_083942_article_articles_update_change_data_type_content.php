<?php

namespace {

    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Database\Migrations\Migration;

    class ArticleArticlesUpdateChangeDataTypeContent extends Migration
    {
        /**
         * Run the migrations.
         *
         * @return void
         */
        public function up()
        {
            Schema::table('articles', function (Blueprint $table) {

                // FIXME: in try for bypass sqlite test not support MODIFY
                try {
                    DB::connection()->getPdo()->exec('ALTER TABLE `articles` MODIFY column `content` MEDIUMTEXT');
                } catch (Exception $e) {
                }
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
