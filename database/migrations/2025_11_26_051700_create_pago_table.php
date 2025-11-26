<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pago', function (Blueprint $table) {
            $table->id();
            $table->string('orden_nro', 20);
            $table->timestamp('fecha')->useCurrent();
            $table->decimal('monto', 10, 2);
            $table->string('metodo', 20);
            $table->string('referencia', 120)->nullable();
            
            $table->foreign('orden_nro')->references('nro')->on('orden')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pago');
    }
};
