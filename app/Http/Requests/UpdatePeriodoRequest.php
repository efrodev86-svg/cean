<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePeriodoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isControlEscolar() ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:100'],
            'fecha_inicio' => ['nullable', 'date'],
            'fecha_cierre' => ['nullable', 'date', 'after_or_equal:fecha_inicio'],
            'fecha_entrega_calificaciones' => ['nullable', 'date', 'after_or_equal:fecha_inicio'],
            'fecha_consulta_boletas' => ['nullable', 'date', 'after_or_equal:fecha_inicio'],
            'activo' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre del periodo es obligatorio.',
            'fecha_cierre.after_or_equal' => 'La fecha de cierre no puede ser anterior al inicio.',
            'fecha_entrega_calificaciones.after_or_equal' => 'La fecha de entrega no puede ser anterior al inicio del periodo.',
            'fecha_consulta_boletas.after_or_equal' => 'La consulta de boletas no puede ser anterior al inicio del periodo.',
        ];
    }
}
