<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventario', function (Blueprint $table) {
            $table->id();
            $table->string('insumo_codigo', 20);
            $table->string('tipo', 20)->nullable();
            $table->date('fecha')->useCurrent();
            $table->integer('cantidad');
            $table->decimal('costo_unitario', 10, 2)->nullable();
            $table->text('referencia')->nullable();
            $table->unsignedBigInteger('empleado_id')->nullable();
            $table->unsignedBigInteger('proveedor_id')->nullable();
            
            $table->foreign('insumo_codigo')->references('codigo')->on('insumo')->onUpdate('cascade');
            $table->foreign('empleado_id')->references('id')->on('empleado');
            $table->foreign('proveedor_id')->references('id')->on('proveedor');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventario');
    }
};
