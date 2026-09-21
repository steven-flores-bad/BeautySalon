<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();

            $table->foreignId('service_category_id')->constrained('service_categories')->onDelete('cascade');

            $table->string('nombre'); // Ej. "Mechas con gorro", "Corte", "Reconstrucción de uñas"
            $table->decimal('precio', 10, 2)->default(0.00);

            // Porcentaje que se lleva la estilista/empleado que atiende el servicio.
            // Se guarda como valor por defecto de ESTE servicio; cuando registremos
            // quién lo atendió (módulo de empleados/ventas de servicios), se podrá
            // ajustar por transacción si algún día cambia según la persona.
            

            $table->text('descripcion')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
