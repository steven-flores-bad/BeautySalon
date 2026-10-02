<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Antes, al vender se sumaba un 13% (IVA) encima del precio. El sistema ya
 * no maneja IVA, así que a los precios de servicios y productos se les suma
 * ese 13% una sola vez para que el cliente siga pagando lo mismo que antes.
 * Ej. Alisado $35.00 → $39.55.
 *
 * Las ventas ya registradas no se modifican.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('services')->update(['precio' => DB::raw('ROUND(precio * 1.13, 2)')]);
        DB::table('products')->update(['precio_venta' => DB::raw('ROUND(precio_venta * 1.13, 2)')]);
    }

    public function down(): void
    {
        DB::table('services')->update(['precio' => DB::raw('ROUND(precio / 1.13, 2)')]);
        DB::table('products')->update(['precio_venta' => DB::raw('ROUND(precio_venta / 1.13, 2)')]);
    }
};
