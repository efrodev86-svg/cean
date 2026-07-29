<?php

namespace App\Http\Requests;

use App\Models\Grupo;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class SyncGrupoAsignacionesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'asignaciones' => ['required', 'array'],
            'asignaciones.*.materia_id' => ['required', 'integer', 'exists:materias,id'],
            'asignaciones.*.docente_id' => ['nullable', 'integer', 'exists:users,id'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            /** @var Grupo $grupo */
            $grupo = $this->route('grupo');
            $materiasPermitidas = $grupo->materiasDelPlan()->pluck('id')->all();
            $sedeId = $grupo->sede_id;

            foreach ($this->input('asignaciones', []) as $indice => $fila) {
                $materiaId = (int) ($fila['materia_id'] ?? 0);

                if ($materiaId && ! in_array($materiaId, $materiasPermitidas, true)) {
                    $validator->errors()->add(
                        "asignaciones.{$indice}.materia_id",
                        'La materia no pertenece al plan de este grupo.',
                    );
                }

                $docenteId = $fila['docente_id'] ?? null;

                if (! filled($docenteId)) {
                    continue;
                }

                $docente = User::query()->find((int) $docenteId);

                if (! $docente || ! $docente->isDocente()) {
                    $validator->errors()->add(
                        "asignaciones.{$indice}.docente_id",
                        'Selecciona un docente válido.',
                    );

                    continue;
                }

                if ($sedeId && ! $docente->sedes()->where('sedes.id', $sedeId)->exists()) {
                    $validator->errors()->add(
                        "asignaciones.{$indice}.docente_id",
                        'El docente no está asignado a la sede del grupo.',
                    );
                }
            }
        });
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'asignaciones.required' => 'Indica las asignaciones del grupo.',
        ];
    }
}
