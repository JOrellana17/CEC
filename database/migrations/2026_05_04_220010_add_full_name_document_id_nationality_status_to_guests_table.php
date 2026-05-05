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
            if (!Schema::hasColumn('guests', 'full_name')) {
                $table->string('full_name')->nullable()->after('last_name');
            }
            if (!Schema::hasColumn('guests', 'document_id')) {
                $table->string('document_id')->nullable()->after('id_number');
            }
            if (!Schema::hasColumn('guests', 'nationality')) {
                $table->string('nationality')->nullable()->after('country');
            }
            if (!Schema::hasColumn('guests', 'status')) {
                $table->enum('status', ['active', 'inactive'])->default('active')->after('is_active');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('guests', function (Blueprint $table) {
            if (Schema::hasColumn('guests', 'status')) {
                $table->dropColumn('status');
            }
            if (Schema::hasColumn('guests', 'nationality')) {
                $table->dropColumn('nationality');
            }
            if (Schema::hasColumn('guests', 'document_id')) {
                $table->dropColumn('document_id');
            }
            if (Schema::hasColumn('guests', 'full_name')) {
                $table->dropColumn('full_name');
            }
        });
    }
};