<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orden', function (Blueprint $table) {
            $table->string('nro', 20)->primary();
            $table->date('fecha_recepcion');
            $table->date('fecha_listo')->nullable();
            $table->date('fecha_entrega')->nullable();
            $table->string('estado', 20)->default('PENDIENTE');
            $table->string('forma_pago', 10)->default('CONTADO');
            $table->date('fecha_vencimiento')->nullable();
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('descuento', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);
            $table->unsignedBigInteger('cliente_id');
            $table->unsignedBigInteger('empleado_id');
            $table->text('observaciones')->nullable();
            
            $table->foreign('cliente_id')->references('id')->on('cliente');
            $table->foreign('empleado_id')->references('id')->on('empleado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orden');
    }
};
