<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFoldersTable extends Migration
{
    public function up()
    {
        Schema::create('folders', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('folder_blog', function (Blueprint $table) {
            $table->foreignId('folder_id')->constrained()->onDelete('cascade');
            $table->foreignId('blog_id')->constrained()->onDelete('cascade');
            $table->primary(['folder_id', 'blog_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('folder_blog');
        Schema::dropIfExists('folders');
    }
}
