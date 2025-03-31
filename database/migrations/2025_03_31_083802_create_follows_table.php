<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('follows', function (Blueprint $table) {
            $table->id();
			$table->foreignId('follower_id')->constrained('users')->onDelete('cascade')->onUpdate('cascade');
			$table->foreignId('following_id')->constrained('users')->onDelete('cascade')->onUpdate('cascade');
			$table->unique(['follower_id', 'following_id']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('follows');
    }
};
