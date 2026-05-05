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
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->string('room_number')->unique();
            $table->foreignId('floor_id')->constrained()->onDelete('cascade');
            $table->foreignId('room_type_id')->constrained()->onDelete('cascade');
            $table->string('floor')->nullable();
            $table->string('building')->nullable();
            $table->enum('status', ['available', 'occupied', 'reserved', 'maintenance', 'blocked'])->default('available');
            $table->enum('room_status', ['clean', 'dirty', 'inspected'])->default('clean');
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_smoking')->default(false);
            $table->boolean('has_balcony')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};