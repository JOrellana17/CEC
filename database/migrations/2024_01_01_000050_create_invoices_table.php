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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->foreignId('booking_id')->constrained()->onDelete('cascade');
            $table->foreignId('guest_id')->constrained()->onDelete('cascade');
            $table->decimal('room_charges', 10, 2)->default(0);
            $table->decimal('service_charges', 10, 2)->default(0);
            $table->decimal('food_charges', 10, 2)->default(0);
            $table->decimal('other_charges', 10, 2)->default(0);
            $table->decimal('subtotal', 10, 2);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2);
            $table->decimal('paid_amount', 10, 2)->default(0);
            $table->decimal('due_amount', 10, 2)->default(0);
            $table->decimal('refund_amount', 10, 2)->default(0);
            $table->enum('status', ['draft', 'pending', 'paid', 'partial', 'cancelled', 'refunded'])->default('draft');
            $table->dateTime('issue_date');
            $table->dateTime('due_date')->nullable();
            $table->dateTime('paid_date')->nullable();
            $table->text('notes')->nullable();
            $table->text('terms')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            
            $table->index('status');
            $table->index('issue_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};