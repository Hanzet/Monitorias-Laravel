<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('persona', function (Blueprint $table) {
            $table->id();
            $table->string('tipo_documento', 50);
            $table->string('numero_documento', 45)->unique()->nullable();
            $table->string('nombreA', 45)->nullable();
            $table->string('nombreB', 45)->nullable();
            $table->string('apellidoA', 45)->nullable();
            $table->string('apellidoB', 45)->nullable();
            $table->string('correo_electronico', 100)->nullable();
            $table->string('telefono', 20)->nullable();
            $table->date('fecha_nacimiento')->nullable();
            $table->string('direccion', 255)->nullable();
            $table->string('estado', 1)->default('1')->comment('1: Activo, 0: Inactivo');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personas');
    }
};
