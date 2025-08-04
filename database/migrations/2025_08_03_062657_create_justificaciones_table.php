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
        Schema::create('justificaciones', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('monitoria_id');
            $table->uuid('persona_id');
            $table->uuid('periodo_academico_id');
            $table->uuid('tipo_justificacion_id');
            $table->text('descripcion');
            $table->timestamps();
        });

        $table->index('monitoria_id', 'fk_justificaciones_monitorias1_idx');
        $table->index('persona_id', 'fk_justificaciones_personas1_idx');
        $table->index('periodo_academico_id', 'fk_justificaciones_periodos_academicos1_idx');
        $table->index('tipo_justificacion_id', 'fk_justificaciones_tipo_justificaciones1_idx');

        $table->foreign('monitoria_id', 'fk_justificaciones_monitorias1')
            ->references('id')->on('monitorias')
            ->onDelete('cascade')
            ->onUpdate('cascade');

        $table->foreign('persona_id', 'fk_justificaciones_personas1')
            ->references('id')->on('personas')
            ->onDelete('cascade')
            ->onUpdate('cascade');

        $table->foreign('periodo_academico_id', 'fk_justificaciones_periodos_academicos1')
            ->references('id')->on('periodos_academicos')
            ->onDelete('cascade')
            ->onUpdate('cascade');

        $table->foreign('tipo_justificacion_id', 'fk_justificaciones_tipo_justificaciones1')
            ->references('id')->on('tipo_justificaciones')
            ->onDelete('cascade')
            ->onUpdate('cascade');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('justificaciones');
    }
};
