<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orden_detalle', function (Blueprint $table) {
            $table->id();
            $table->string('orden_nro', 20);
            $table->unsignedBigInteger('servicio_id');
            $table->string('unidad', 10);
            $table->decimal('peso_kg', 10, 2)->nullable();
            $table->integer('cantidad')->nullable();
            $table->decimal('precio_unitario', 10, 2);
            $table->decimal('descuento', 10, 2)->default(0);
            $table->string('fragancia', 50)->nullable();
            $table->text('notas')->nullable();
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('total_linea', 10, 2)->default(0);
            
            $table->foreign('orden_nro')->references('nro')->on('orden')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('servicio_id')->references('id')->on('servicio');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orden_detalle');
    }
};
