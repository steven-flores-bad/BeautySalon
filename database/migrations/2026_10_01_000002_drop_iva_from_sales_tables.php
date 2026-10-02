<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * El sistema ya no maneja IVA: el total de cada venta es la suma de los
 * precios menos los descuentos. Se elimina la columna "iva" de las ventas.
 * El total de las ventas ya registradas no cambia.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn('iva');
        });

        Schema::table('service_sales', function (Blueprint $table) {
            $table->dropColumn('iva');
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->decimal('iva', 10, 2)->default(0)->after('descuento');
        });

        Schema::table('service_sales', function (Blueprint $table) {
            $table->decimal('iva', 10, 2)->default(0)->after('descuento');
        });
    }
};
