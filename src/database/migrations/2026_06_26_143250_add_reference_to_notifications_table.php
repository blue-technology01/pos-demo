<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            // Add missing reference column
            $table->string('reference')->nullable()->after('message');

            // Add unique constraint to prevent duplicates
            $table->unique(['user_id', 'type', 'reference']);

            // Speed up unread queries
            $table->index('read_at');
        });
    }

    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'type', 'reference']);
            $table->dropIndex(['read_at']);
            $table->dropColumn('reference');
        });
    }
};
