<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proceso_insumo', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('proceso_id');
            $table->string('insumo_codigo', 20);
            $table->integer('cantidad');
            
            $table->foreign('proceso_id')->references('id')->on('orden_proceso')->onDelete('cascade');
            $table->foreign('insumo_codigo')->references('codigo')->on('insumo')->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proceso_insumo');
    }
};
