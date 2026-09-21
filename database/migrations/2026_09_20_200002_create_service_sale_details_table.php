<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_sale_details', function (Blueprint $table) {
            $table->id();

            $table->foreignId('service_sale_id')->constrained('service_sales')->onDelete('cascade');
            $table->foreignId('service_id')->constrained('services')->onDelete('restrict');

            // Quién atendió este servicio específico. restrict: no se puede borrar
            // un empleado si tiene servicios en su historial (protege comisiones pasadas).
            $table->foreignId('employee_id')->constrained('employees')->onDelete('restrict');

            // Precio "congelado" al momento de la venta (por si el precio del
            // servicio cambia después, el historial no se ve afectado).
            $table->decimal('precio', 10, 2);
            $table->decimal('descuento', 10, 2)->default(0);
            $table->decimal('subtotal', 10, 2); // precio - descuento (lo que pagó el cliente)

            // Comisión de ESTA transacción específica, ya que confirmaste que
            // depende de la empleada, no es un valor fijo del servicio.
            $table->decimal('comision_porcentaje', 5, 2)->default(0);
            $table->decimal('comision_monto', 10, 2)->default(0); // calculado: subtotal * comision_porcentaje / 100

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_sale_details');
    }
};
