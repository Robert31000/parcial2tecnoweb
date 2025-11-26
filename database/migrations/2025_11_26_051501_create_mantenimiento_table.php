<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mantenimiento', function (Blueprint $table) {
            $table->id();
            $table->string('equipo_codigo', 20);
            $table->date('fecha');
            $table->text('descripcion')->nullable();
            $table->decimal('costo', 10, 2)->default(0);
            
            $table->foreign('equipo_codigo')->references('codigo')->on('equipo')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mantenimiento');
    }
};
