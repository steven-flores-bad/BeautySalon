<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            // Solo los gastos en efectivo pagados con la caja de hoy abierta
            // quedan ligados a ella: se restan del efectivo esperado al cerrar.
            $table->foreignId('cash_register_id')->nullable()->constrained('cash_registers')->nullOnDelete();

            $table->date('fecha');
            $table->string('categoria', 50);
            $table->string('descripcion');
            $table->decimal('monto', 10, 2);
            $table->enum('metodo_pago', ['efectivo', 'tarjeta', 'transferencia'])->default('efectivo');
            $table->text('notas')->nullable();

            $table->timestamps();

            $table->index('fecha');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
