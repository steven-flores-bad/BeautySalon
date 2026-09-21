<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sale_details', function (Blueprint $table) {
            $table->id();

            // Si se borra la venta completa, se borran sus líneas de detalle.
            $table->foreignId('sale_id')->constrained('sales')->onDelete('cascade');

            // restrict: no se puede borrar un producto si tiene ventas asociadas
            // (protege tu historial de ventas de quedar con referencias rotas).
            $table->foreignId('product_id')->constrained('products')->onDelete('restrict');

            $table->integer('cantidad');

            // Precio "congelado" al momento de la venta, independiente de que el
            // precio del producto cambie después. Clave para reportes históricos.
            $table->decimal('precio_unitario', 10, 2);
            $table->decimal('descuento', 10, 2)->default(0); // <-- Nuevo campo de descuento por producto
            $table->decimal('subtotal', 10, 2); // (cantidad * precio_unitario) - descuento

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_details');
    }
};