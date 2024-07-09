<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class PausableJobMigration extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table(config('queue.connections.database.table'), function (Blueprint $table) {
            $table->nullableMorphs('paused_by');
            $table->timestamp('paused_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table(config('queue.connections.database.table'), function (Blueprint $table) {
            $table->dropColumn([
                'paused_by_id',
                'paused_by_type',
                'paused_at',
            ]);
        });
    }
};
