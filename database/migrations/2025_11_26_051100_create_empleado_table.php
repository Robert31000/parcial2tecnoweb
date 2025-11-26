<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('empleado', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->string('cargo', 50)->nullable();
            $table->date('fecha_contratacion')->nullable();
            
            $table->foreign('id')->references('id')->on('usuario')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('empleado');
    }
};
