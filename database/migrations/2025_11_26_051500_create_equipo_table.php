<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipo', function (Blueprint $table) {
            $table->string('codigo', 20)->primary();
            $table->string('nombre', 100);
            $table->string('tipo', 30);
            $table->string('marca', 50)->nullable();
            $table->string('modelo', 50)->nullable();
            $table->date('fecha_compra')->nullable();
            $table->decimal('capacidad_kg', 10, 2)->nullable();
            $table->decimal('consumo_electrico_kw', 12, 2)->nullable();
            $table->decimal('consumo_agua_litros', 12, 2)->nullable();
            $table->string('estado', 30)->default('LIBRE');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipo');
    }
};
