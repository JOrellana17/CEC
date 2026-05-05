<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            if (! Schema::hasColumn('services', 'price_type')) {
                $table->string('price_type')->default('per_unit')->after('price');
            }
            if (! Schema::hasColumn('services', 'available_from')) {
                $table->time('available_from')->nullable()->after('is_available_24h');
            }
            if (! Schema::hasColumn('services', 'available_to')) {
                $table->time('available_to')->nullable()->after('available_from');
            }
            if (! Schema::hasColumn('services', 'icon')) {
                $table->string('icon')->nullable()->after('available_to');
            }
            if (! Schema::hasColumn('services', 'notes')) {
                $table->text('notes')->nullable()->after('icon');
            }
            if (! Schema::hasColumn('services', 'created_by')) {
                $table->foreignId('created_by')->nullable()->after('notes')->constrained('users')->nullOnDelete();
            }
        });

        Schema::table('reservation_services', function (Blueprint $table) {
            if (! Schema::hasColumn('reservation_services', 'unit_price')) {
                $table->decimal('unit_price', 10, 2)->default(0)->after('quantity');
            }
            if (! Schema::hasColumn('reservation_services', 'total_price')) {
                $table->decimal('total_price', 10, 2)->default(0)->after('unit_price');
            }
            if (! Schema::hasColumn('reservation_services', 'service_date')) {
                $table->dateTime('service_date')->nullable()->after('total_price');
            }
            if (! Schema::hasColumn('reservation_services', 'notes')) {
                $table->text('notes')->nullable()->after('service_date');
            }
            if (! Schema::hasColumn('reservation_services', 'created_by')) {
                $table->foreignId('created_by')->nullable()->after('notes')->constrained('users')->nullOnDelete();
            }
        });

        DB::statement('UPDATE reservation_services SET unit_price = subtotal / GREATEST(quantity, 1), total_price = subtotal WHERE total_price = 0');

        try {
            DB::statement("ALTER TABLE invoices MODIFY booking_id BIGINT UNSIGNED NULL");
        } catch (Throwable $exception) {
            //
        }

        try {
            DB::statement("ALTER TABLE payments MODIFY booking_id BIGINT UNSIGNED NULL");
        } catch (Throwable $exception) {
            //
        }

        try {
            DB::statement("ALTER TABLE payments MODIFY payment_method ENUM('cash','card','bank_transfer','online','credit','mixed') NOT NULL");
        } catch (Throwable $exception) {
            //
        }

        $defaults = [
            ['Restaurant', 'restaurant', 'food', 'per_unit', 'item'],
            ['Laundry', 'laundry', 'laundry', 'per_unit', 'load'],
            ['Room Service', 'room-service', 'room', 'per_unit', 'item'],
            ['Transportation', 'transportation', 'transport', 'fixed', 'trip'],
            ['Mini Bar', 'mini-bar', 'beverage', 'per_unit', 'item'],
            ['Custom Service', 'custom-service', 'other', 'fixed', null],
        ];

        foreach ($defaults as [$name, $slug, $category, $priceType, $unit]) {
            if (! DB::table('services')->where('slug', $slug)->exists()) {
                DB::table('services')->insert([
                    'name' => $name,
                    'slug' => $slug,
                    'description' => $name.' service',
                    'price' => 0,
                    'price_type' => $priceType,
                    'unit' => $unit,
                    'category' => $category,
                    'is_active' => true,
                    'is_available_24h' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('reservation_services', function (Blueprint $table) {
            foreach (['created_by', 'notes', 'service_date', 'total_price', 'unit_price'] as $column) {
                if (Schema::hasColumn('reservation_services', $column)) {
                    if ($column === 'created_by') {
                        $table->dropConstrainedForeignId($column);
                    } else {
                        $table->dropColumn($column);
                    }
                }
            }
        });

        Schema::table('services', function (Blueprint $table) {
            foreach (['created_by', 'notes', 'icon', 'available_to', 'available_from', 'price_type'] as $column) {
                if (Schema::hasColumn('services', $column)) {
                    if ($column === 'created_by') {
                        $table->dropConstrainedForeignId($column);
                    } else {
                        $table->dropColumn($column);
                    }
                }
            }
        });
    }
};
