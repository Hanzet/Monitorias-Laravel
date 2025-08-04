<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MonitoriaController
{
    public function index()
    {
        $monitorias = Monitoria::all();
        return response()->json($monitorias);
    }

    public function show($id)
    {
        $monitoria = Monitoria::findOrFail($id);
        return response()->json($monitoria);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'persona_id'            => 'required|exists:personas,id',
            'tipo_monitoria_id'     => 'required|exists:tipo_monitorias,id',
            'dependencia_id'        => 'required|exists:dependencias,id',
            'periodo_academico_id'  => 'required|exists:periodos_academicos,id',
            'descripcion'           => 'nullable|string|max:255',
            'inicio'                => 'required|date',
            'fin'                   => 'required|date|after_or_equal:inicio',
            'horas_asignadas'       => 'required|integer|min:1',
            'estado'                => 'required|string|in:activo,inactivo'
        ]);

        $monitoria = Monitoria::updateOrCreate($data);

        if ($monitoria) {
            return response()->json(['message' => 'Monitoria created successfully'], 201);
        } else {
            return response()->json(['message' => 'Failed to create Monitoria'], 500);
        }

        return response()->json(['message' => 'Failed to create Monitoria'], 500);
    }
}
