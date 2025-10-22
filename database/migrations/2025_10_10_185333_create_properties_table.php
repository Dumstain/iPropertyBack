<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('listed_by_user_id')->constrained('users')->onDelete('cascade');
            $table->string('nombre_comercial');
            $table->enum('estado', ['disponible', 'apartada', 'vendida', 'rentada', 'pausada'])->default('disponible');
            $table->enum('tipo_contrato', ['exclusiva', 'directa', 'abierta'])->nullable();
            $table->decimal('precio_publicado', 12, 2);
            $table->string('notas_precio')->nullable();
            $table->decimal('comision_pct', 5, 2)->nullable()->comment('Porcentaje de comisión. Ej: 3.50');
            $table->decimal('comision_monto', 12, 2)->nullable()->comment('Monto calculado de la comisión');
            $table->text('comision_notas')->nullable();
            $table->string('domicilio_calle');
            $table->string('domicilio_num_ext', 50);
            $table->string('domicilio_num_int', 50)->nullable();
            $table->string('colonia');
            $table->string('ciudad');
            $table->string('estado_republica');
            $table->text('colindancias')->nullable();
            $table->decimal('m2_terreno', 10, 2)->nullable();
            $table->decimal('m2_construccion', 10, 2)->nullable();
            $table->tinyInteger('num_pisos')->unsigned()->default(1);
            $table->tinyInteger('habitaciones_total')->unsigned()->default(0);
            $table->tinyInteger('habitaciones_pb')->unsigned()->default(0)->comment('Habitaciones en Planta Baja');
            $table->tinyInteger('banos_completos')->unsigned()->default(0);
            $table->tinyInteger('medios_banos')->unsigned()->default(0);
            $table->tinyInteger('banos_completos_pb')->unsigned()->default(0)->comment('Baños Completos en Planta Baja');
            $table->tinyInteger('medios_banos_pb')->unsigned()->default(0)->comment('Medios Baños en Planta Baja');
            $table->enum('jardin_tamano', ['chico', 'mediano', 'grande'])->nullable()->comment('Si es NULL, no tiene jardín.');
            $table->json('amenidades')->nullable();
            $table->text('otros_detalles')->nullable();
            $table->json('imagenes')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};
