<?php

use App\Models\Post;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->bigIncrements('id');
            // $table->string('tag')->unique();
            // https://github.com/spatie/laravel-tags
            $table->string('slug')->nullable();
            $table->string('title');
            $table->string('seo_title')->nullable();
            $table->text('excerpt')->nullable();
            $table->mediumText('body');
            // ALTER TABLE posts MODIFY body MEDIUMTEXT;
            $table->text('meta_description')->nullable();
            $table->text('meta_keywords')->nullable();

            $table->enum('status', [Post::PUBLISHED, Post::DRAFT, Post::PENDING])->default(Post::DRAFT);

            $table->unsignedBigInteger('author_id')->unsigned()->nullable()->comment('公众号uid');
            $table->unsignedBigInteger('user_id')->unsigned()->comment('创建者');
            $table->unsignedBigInteger('modified_id')->unsigned()->comment('最后改动者');
            $table->unsignedBigInteger('category_id')->nullable()->unsigned()->comment('分类id');
            $table->integer('order')->default(1)->comment('分类weight');

            $table->string('image')->nullable();
            $table->string('image_url')->nullable();
            $table->string('mp3')->nullable();
            $table->string('mp3_url')->nullable();
            $table->string('mp4_url')->nullable();
            $table->string('youtube_vid')->nullable();
            //alert table posts rename column  youtube_url youtube_vid
            // ALTER TABLE `posts` CHANGE COLUMN `youtube_url` `youtube_vid` VARCHAR(255);
            // ALTER TABLE posts ADD COLUMN mp4_upyun_path VARCHAR(255) AFTER mp4_one_path;
            $table->string('mp4_one_path')->nullable();
            $table->string('mp4_upyun_path')->nullable();
            //http://onemedia.yongbuzhixi.com /mp4/2018/youtube_vid.mp4
            $table->string('qq_vid')->nullable();
            $table->string('pan_url')->nullable(); //分享链接或播放链接
            $table->string('pan_password')->nullable(); //百度分享链接密码
            $table->string('origin_url')->nullable();
            //reference!
            $table->nullableMorphs('target');

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
        Schema::dropIfExists('posts');
    }
}
