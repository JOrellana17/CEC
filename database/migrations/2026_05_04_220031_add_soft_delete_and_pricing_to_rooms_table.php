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
        Schema::table('rooms', function (Blueprint $table) {
            if (!Schema::hasColumn('rooms', 'price_per_night')) {
                $table->decimal('price_per_night', 10, 2)->nullable()->after('building');
            }
            if (!Schema::hasColumn('rooms', 'capacity')) {
                $table->integer('capacity')->default(2)->after('price_per_night');
            }
            if (!Schema::hasColumn('rooms', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            if (Schema::hasColumn('rooms', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
            if (Schema::hasColumn('rooms', 'capacity')) {
                $table->dropColumn('capacity');
            }
            if (Schema::hasColumn('rooms', 'price_per_night')) {
                $table->dropColumn('price_per_night');
            }
        });
    }
};