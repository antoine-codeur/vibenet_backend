<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id(); // id: int (PK)
            $table->unsignedBigInteger('blog_id'); // blog_id: int (FK vers Blog)
            $table->unsignedBigInteger('owner_id'); // owner_id: int (FK vers User)
            $table->string('content', 2000)->nullable(); // Limite à 2000 caractères
            $table->string('image_url')->nullable(); // URL de l'image
            $table->string('type')->default('text'); // Type de contenu (text, image, poll, etc.)
            $table->timestamps(); // created_at: timestamp, updated_at: timestamp

            // Foreign key constraints
            $table->foreign('blog_id')->references('id')->on('blogs')->onDelete('cascade');
            $table->foreign('owner_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
