<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            if (! Schema::hasColumn('rooms', 'max_capacity')) {
                $table->integer('max_capacity')->default(2)->after('capacity');
            }

            if (! Schema::hasColumn('rooms', 'extra_person_price')) {
                $table->decimal('extra_person_price', 10, 2)->default(0)->after('max_capacity');
            }
        });
    }

    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            if (Schema::hasColumn('rooms', 'extra_person_price')) {
                $table->dropColumn('extra_person_price');
            }

            if (Schema::hasColumn('rooms', 'max_capacity')) {
                $table->dropColumn('max_capacity');
            }
        });
    }
};
