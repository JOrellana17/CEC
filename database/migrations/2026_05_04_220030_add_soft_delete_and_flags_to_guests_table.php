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
        Schema::table('guests', function (Blueprint $table) {
            if (!Schema::hasColumn('guests', 'is_frequent')) {
                $table->boolean('is_frequent')->default(false)->after('is_vip');
            }
            if (!Schema::hasColumn('guests', 'is_blacklisted')) {
                $table->boolean('is_blacklisted')->default(false)->after('is_frequent');
            }
            if (!Schema::hasColumn('guests', 'incident_notes')) {
                $table->text('incident_notes')->nullable()->after('notes');
            }
            if (!Schema::hasColumn('guests', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('guests', function (Blueprint $table) {
            if (Schema::hasColumn('guests', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
            if (Schema::hasColumn('guests', 'incident_notes')) {
                $table->dropColumn('incident_notes');
            }
            if (Schema::hasColumn('guests', 'is_blacklisted')) {
                $table->dropColumn('is_blacklisted');
            }
            if (Schema::hasColumn('guests', 'is_frequent')) {
                $table->dropColumn('is_frequent');
            }
        });
    }
};