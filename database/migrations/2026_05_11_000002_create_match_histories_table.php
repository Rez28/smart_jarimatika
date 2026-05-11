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
        Schema::create('match_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id_1');
            $table->unsignedBigInteger('user_id_2')->nullable();
            $table->unsignedBigInteger('winner_id')->nullable();
            $table->string('mode')->default('classic'); // classic, tebak, hitung
            $table->integer('score_1')->default(0);
            $table->integer('score_2')->default(0);
            $table->timestamps();

            // Foreign Keys
            $table->foreign('user_id_1')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('user_id_2')
                ->references('id')
                ->on('users')
                ->onDelete('set null');

            $table->foreign('winner_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null');

            // Indexes
            $table->index('user_id_1');
            $table->index('user_id_2');
            $table->index('winner_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('match_histories');
    }
};
