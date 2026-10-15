<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Comisión por venta de productos: cada línea puede indicar qué empleada
 * vendió el producto y qué porcentaje recibe (como en ventas de servicios).
 * Las ventas anteriores quedan sin empleada y con comisión 0.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sale_details', function (Blueprint $table) {
            $table->foreignId('employee_id')->nullable()->after('product_id')
                  ->constrained('employees')->nullOnDelete();
            $table->decimal('comision_porcentaje', 5, 2)->default(0)->after('subtotal');
            $table->decimal('comision_monto', 10, 2)->default(0)->after('comision_porcentaje');
        });
    }

    public function down(): void
    {
        Schema::table('sale_details', function (Blueprint $table) {
            $table->dropConstrainedForeignId('employee_id');
            $table->dropColumn(['comision_porcentaje', 'comision_monto']);
        });
    }
};
