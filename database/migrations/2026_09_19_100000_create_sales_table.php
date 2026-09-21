<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();

            // Quién registró la venta (cajero/usuario). Nullable por si en algún
            // momento borras un usuario y no quieres perder el historial de ventas.
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            // Datos opcionales del cliente. Si más adelante quieres un CRM de
            // clientes completo, esto se puede migrar a una tabla "customers".
            $table->string('cliente_nombre')->nullable();

            $table->enum('metodo_pago', ['efectivo', 'tarjeta', 'transferencia'])->default('efectivo');

            // Montos: se guardan calculados y "congelados" en el momento de la venta,
            // no se recalculan dinámicamente después.
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('descuento', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);

            $table->enum('estado', ['completada', 'cancelada'])->default('completada');
            $table->text('notas')->nullable();

            $table->timestamps(); // created_at sirve como "fecha de la venta"
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
