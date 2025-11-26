<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('insumo', function (Blueprint $table) {
            $table->string('codigo', 20)->primary();
            $table->string('nombre', 100);
            $table->integer('cantidad');
            $table->string('unidad_medida', 10);
            $table->decimal('stock', 10, 2)->default(0);
            $table->decimal('stock_min', 10, 2)->default(0);
            $table->decimal('stock_max', 10, 2)->default(0);
            $table->decimal('costo_promedio', 10, 2)->nullable();
            $table->boolean('estado')->default(true);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('insumo');
    }
};
