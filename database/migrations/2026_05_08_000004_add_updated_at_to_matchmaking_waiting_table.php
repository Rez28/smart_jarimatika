<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Menambahkan kolom updated_at untuk tracking kapan user last active
     * Digunakan untuk detect dan cleanup offline matchmaking records
     */
    public function up(): void
    {
        Schema::table('matchmaking_waiting', function (Blueprint $table) {
            $table->timestamp('updated_at')->nullable()->after('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('matchmaking_waiting', function (Blueprint $table) {
            $table->dropColumn('updated_at');
        });
    }
};
