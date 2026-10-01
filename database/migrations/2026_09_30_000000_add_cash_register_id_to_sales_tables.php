<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Cada venta queda ligada a la caja en la que se registró.
        Schema::table('sales', function (Blueprint $table) {
            $table->foreignId('cash_register_id')->nullable()->after('user_id')
                  ->constrained('cash_registers')->nullOnDelete();
        });

        Schema::table('service_sales', function (Blueprint $table) {
            $table->foreignId('cash_register_id')->nullable()->after('user_id')
                  ->constrained('cash_registers')->nullOnDelete();
        });

        // Ventas existentes: se ligan a la caja de su misma fecha (si existe).
        foreach (DB::table('cash_registers')->get(['id', 'fecha']) as $caja) {
            foreach (['sales', 'service_sales'] as $tabla) {
                DB::table($tabla)
                    ->whereNull('cash_register_id')
                    ->whereDate('created_at', $caja->fecha)
                    ->update(['cash_register_id' => $caja->id]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropConstrainedForeignId('cash_register_id');
        });

        Schema::table('service_sales', function (Blueprint $table) {
            $table->dropConstrainedForeignId('cash_register_id');
        });
    }
};
