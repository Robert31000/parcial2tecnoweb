<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orden_proceso', function (Blueprint $table) {
            $table->id();
            $table->string('orden_nro', 20);
            $table->string('equipo_codigo', 20);
            $table->string('etapa', 20);
            $table->integer('ciclos')->default(1);
            $table->integer('duracion_ciclo')->nullable();
            $table->string('estado', 20)->default('PENDIENTE');
            $table->text('observacion')->nullable();
            $table->decimal('kwh_consumidos', 12, 2)->nullable();
            $table->decimal('agua_litros_consumidos', 12, 2)->nullable();
            
            $table->foreign('orden_nro')->references('nro')->on('orden')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('equipo_codigo')->references('codigo')->on('equipo')->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orden_proceso');
    }
};
