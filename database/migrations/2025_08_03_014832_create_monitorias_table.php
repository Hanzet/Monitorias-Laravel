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
        Schema::create('monitorias', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('persona_id');
            $table->uuid('tipo_monitoria_id');
            $table->uuid('dependencia_id');
            $table->uuid('periodo_academico_id');
            $table->string('descripcion', 500)->nullable();
            $table->dateTime('inicio');
            $table->dateTime('fin');
            $table->integer('horas_asignadas');
            $table->enum('estado', ['activo', 'inactivo'])->default('activo');
            $table->timestamps();

            $table->index('persona_id', 'fk_monitorias_personas1_idx');
            $table->index('tipo_monitoria_id', 'fk_monitorias_tipo_monitorias1_idx');
            $table->index('dependencia_id', 'fk_monitorias_dependencias1_idx');
            $table->index('periodo_academico_id', 'fk_monitorias_periodos_academicos1_idx');

            $table->foreign('persona_id', 'fk_monitorias_personas1')
                ->references('id')->on('persona')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->foreign('tipo_monitoria_id', 'fk_monitorias_tipo_monitorias1')
                ->references('id')->on('tipo_monitorias')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->foreign('dependencia_id', 'fk_monitorias_dependencias1')
                ->references('id')->on('dependencias')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->foreign('periodo_academico_id', 'fk_monitorias_periodos_academicos1')
                ->references('id')->on('periodos_academicos')
                ->onDelete('cascade')
                ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monitorias');
    }
};
