<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->nullable();            // Código, se genera automáticamente

            // Relación con la tabla categories
            // onDelete('restrict') o 'cascade' según prefieras si se borra una categoría
            $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');

            $table->string('producto');                      // Producto (ej. gel sólido)
            $table->string('marca')->nullable();             // Marca (ej. kiss, fantasy)

            // Presentación separada en cantidad numérica + unidad seleccionable
            // (ej. 15 + ml, en vez de escribir "15 ml" como texto libre)
            $table->decimal('presentacion_valor', 8, 2)->nullable();
            $table->string('presentacion_unidad', 20)->nullable(); // ml, l, g, kg, oz, unidad

            $table->integer('existencia')->default(0);       // Existencia actual
            $table->integer('stock_minimo')->default(0);     // Stock mínimo
            $table->decimal('precio_compra', 10, 2)->default(0.00); // Precio de compra
            $table->decimal('precio_venta', 10, 2)->default(0.00);  // Precio de venta
            $table->timestamps();                            // created_at y updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};