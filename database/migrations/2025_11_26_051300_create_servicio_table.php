<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('servicio', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100)->unique();
            $table->text('descripcion')->nullable();
            $table->string('tipo_cobro', 10);
            $table->decimal('precio_unitario', 10, 2);
            $table->boolean('estado')->default(true);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('servicio');
    }
};
