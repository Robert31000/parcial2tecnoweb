<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proveedor', function (Blueprint $table) {
            $table->id();
            $table->string('razon_social', 120);
            $table->string('telefono', 20)->nullable();
            $table->text('direccion')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proveedor');
    }
};
