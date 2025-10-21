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
       Schema::create('friends', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // The sender or owner
            $table->foreignId('friend_id')->constrained('users')->onDelete('cascade'); // The other user
            $table->enum('status', ['pending', 'accepted', 'blocked', 'restricted'])->default('pending');
            $table->timestamps();

            $table->unique(['user_id', 'friend_id']); // Prevent duplicate entries
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('friends');
    }
};
