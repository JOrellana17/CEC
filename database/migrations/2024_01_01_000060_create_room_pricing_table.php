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
        Schema::create('room_pricing', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_type_id')->constrained()->onDelete('cascade');
            $table->string('season_name')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->decimal('price', 10, 2);
            $table->decimal('extra_person_price', 10, 2)->default(0);
            $table->decimal('child_price', 10, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['start_date', 'end_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('room_pricing');
    }
};