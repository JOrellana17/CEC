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
        Schema::table('invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('invoices', 'reservation_id')) {
                $table->foreignId('reservation_id')->nullable()->after('booking_id')->constrained('reservations')->nullOnDelete();
            }
        });

        Schema::table('payments', function (Blueprint $table) {
            if (!Schema::hasColumn('payments', 'payment_date')) {
                $table->dateTime('payment_date')->nullable()->after('amount');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            if (Schema::hasColumn('payments', 'payment_date')) {
                $table->dropColumn('payment_date');
            }
        });

        Schema::table('invoices', function (Blueprint $table) {
            if (Schema::hasColumn('invoices', 'reservation_id')) {
                $table->dropForeign(['reservation_id']);
                $table->dropColumn('reservation_id');
            }
        });
    }
};