<?php

namespace App\Http\Requests;

use App\Models\CicloEscolar;
use App\Models\Licenciatura;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

abstract class GrupoRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if ($this->filled('letra')) {
            $this->merge(['letra' => strtoupper((string) $this->input('letra'))]);
        }
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $cicloId = (int) $this->input('ciclo_escolar_id');
            $ciclo = CicloEscolar::query()->find($cicloId);
            $scopeSedeId = $this->user()?->sedeScopeId();

            if ($ciclo === null) {
                return;
            }

            if ($scopeSedeId !== null && $ciclo->sede_id !== $scopeSedeId) {
                $validator->errors()->add('ciclo_escolar_id', 'No puedes gestionar grupos de otra sede.');
            }
        });
    }

    /**
     * @return array<string, mixed>
     */
    protected function reglasGrupo(?int $grupoId = null): array
    {
        return [
            'ciclo_escolar_id' => ['required', 'integer', 'exists:ciclos_escolares,id'],
            'semestre' => ['required', 'integer', 'min:1', 'max:8'],
            'letra' => ['required', 'string', 'max:4', 'regex:/^[A-Z0-9]+$/'],
            'licenciatura' => [
                'required',
                'string',
                'max:40',
                Rule::in($this->licenciaturasPermitidas()),
                Rule::unique('grupos', 'licenciatura')
                    ->where(fn ($q) => $q
                        ->where('ciclo_escolar_id', $this->input('ciclo_escolar_id'))
                        ->where('semestre', $this->input('semestre'))
                        ->where('letra', $this->input('letra')))
                    ->ignore($grupoId),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function mensajesGrupo(): array
    {
        return [
            'ciclo_escolar_id.required' => 'Selecciona el ciclo escolar.',
            'semestre.required' => 'Selecciona el semestre.',
            'semestre.min' => 'El semestre debe ser entre 1 y 8.',
            'semestre.max' => 'El semestre debe ser entre 1 y 8.',
            'letra.required' => 'Indica la letra del grupo.',
            'letra.regex' => 'La letra del grupo solo puede contener letras o números.',
            'licenciatura.required' => 'Selecciona la licenciatura.',
            'licenciatura.in' => 'La licenciatura seleccionada no es válida.',
            'licenciatura.unique' => 'Ya existe un grupo con esa combinación de ciclo, semestre, letra y licenciatura.',
        ];
    }

    /**
     * @return list<string>
     */
    protected function licenciaturasPermitidas(): array
    {
        $registradas = Licenciatura::query()
            ->where('activa', true)
            ->orderBy('nombre_corto')
            ->pluck('nombre_corto')
            ->all();

        return $registradas !== [] ? $registradas : ['ESPANOL', 'TELESECUNDARIA'];
    }
}
