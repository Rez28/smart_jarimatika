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
        Schema::create('matchmaking_waiting', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('user_name');
            $table->timestamp('created_at');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unique('user_id');
        });

        Schema::create('matchmaking_games', function (Blueprint $table) {
            $table->id();
            $table->string('game_id')->unique();
            $table->unsignedBigInteger('player1_id');
            $table->unsignedBigInteger('player2_id');
            $table->timestamp('created_at');
            $table->foreign('player1_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('player2_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('matchmaking_games');
        Schema::dropIfExists('matchmaking_waiting');
    }
};
