<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('citas', function (Blueprint $table) {
            $table->id();

            $table->string('cliente_nombre', 150);
            $table->string('telefono', 30);
            $table->string('email', 150)->nullable();

            // Servicio tal como lo escribió/eligió el cliente en la página,
            // y el servicio del sistema si coincide por nombre.
            $table->string('servicio', 150);
            $table->foreignId('service_id')->nullable()->constrained('services')->nullOnDelete();

            $table->date('fecha');
            $table->time('hora');
            $table->text('notas')->nullable();

            $table->enum('estado', ['pendiente', 'confirmada', 'atendida', 'cancelada'])->default('pendiente');
            $table->string('origen', 30)->default('wordpress');

            $table->timestamps();

            $table->index(['fecha', 'hora']);
            $table->index('estado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('citas');
    }
};
