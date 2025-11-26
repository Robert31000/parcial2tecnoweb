<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promocion_servicio', function (Blueprint $table) {
            $table->unsignedBigInteger('id_promocion');
            $table->unsignedBigInteger('id_servicio');
            $table->date('fecha_inicio');
            $table->date('fecha_final');
            
            $table->primary(['id_promocion', 'id_servicio']);
            
            $table->foreign('id_promocion')->references('id')->on('promocion')->onDelete('cascade');
            $table->foreign('id_servicio')->references('id')->on('servicio')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promocion_servicio');
    }
};
