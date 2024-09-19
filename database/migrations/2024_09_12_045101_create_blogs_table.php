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
        Schema::create('blogs', function (Blueprint $table) {
            $table->id(); // id: int (PK)
            $table->string('name'); // name: string
            $table->string('description'); // description: string
            $table->unsignedBigInteger('owner_id')->unique(); // owner_id: int (FK vers User) avec unicité
            $table->timestamps(); // created_at: timestamp, updated_at: timestamp

            // Foreign key constraint
            $table->foreign('owner_id')->references('id')->on('users')->onDelete('cascade');
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blogs');
    }
};
