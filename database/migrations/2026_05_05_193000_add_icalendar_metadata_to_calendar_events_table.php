<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('calendar_events', function (Blueprint $table) {
            if (! Schema::hasColumn('calendar_events', 'source')) {
                $table->string('source')->default('local')->after('event_uid');
            }

            if (! Schema::hasColumn('calendar_events', 'external_url')) {
                $table->string('external_url')->nullable()->after('source');
            }

            if (! Schema::hasColumn('calendar_events', 'last_synced_at')) {
                $table->dateTime('last_synced_at')->nullable()->after('end_date');
            }

            if (! Schema::hasColumn('calendar_events', 'raw_payload')) {
                $table->json('raw_payload')->nullable()->after('last_synced_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('calendar_events', function (Blueprint $table) {
            foreach (['raw_payload', 'last_synced_at', 'external_url', 'source'] as $column) {
                if (Schema::hasColumn('calendar_events', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
