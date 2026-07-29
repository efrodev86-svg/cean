<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

abstract class DocenteRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $merge = [];

        foreach (['nombre', 'primer_apellido', 'segundo_apellido', 'clave_plaza', 'nombre_plaza', 'celular'] as $campo) {
            if ($this->has($campo)) {
                $merge[$campo] = trim((string) $this->input($campo));
            }
        }

        if ($this->has('curp')) {
            $merge['curp'] = strtoupper(trim((string) $this->input('curp')));
        }

        if ($this->input('grado_academico_id') === '') {
            $merge['grado_academico_id'] = null;
        }

        if ($this->input('segundo_apellido') === '') {
            $merge['segundo_apellido'] = null;
        }

        foreach (['clave_plaza', 'nombre_plaza'] as $campo) {
            if ($this->input($campo) === '') {
                $merge[$campo] = null;
            }
        }

        if ($merge !== []) {
            $this->merge($merge);
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function reglasPerfilDocente(?int $docenteId = null): array
    {
        $tiposContratacion = array_keys(config('cean.tipos_contratacion_docente', []));

        return [
            'nombre' => ['required', 'string', 'max:100'],
            'primer_apellido' => ['required', 'string', 'max:100'],
            'segundo_apellido' => ['nullable', 'string', 'max:100'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255'],
            'curp' => [
                'required',
                'string',
                'size:18',
                'regex:/^[A-Z]{4}\d{6}[HM][A-Z]{5}[A-Z0-9]\d$/',
                Rule::unique('users', 'curp')->ignore($docenteId),
            ],
            'grado_academico_id' => ['nullable', 'integer', 'exists:grados_academicos,id'],
            'tipo_contratacion' => ['required', 'string', Rule::in($tiposContratacion)],
            'clave_plaza' => ['nullable', 'string', 'max:50'],
            'nombre_plaza' => ['nullable', 'string', 'max:150'],
            'celular' => ['required', 'string', 'max:20', 'regex:/^\d{10}$/'],
            'activo' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function mensajesPerfilDocente(): array
    {
        return [
            'nombre.required' => 'El nombre es obligatorio.',
            'primer_apellido.required' => 'El primer apellido es obligatorio.',
            'email.required' => 'El correo es obligatorio.',
            'curp.required' => 'La CURP es obligatoria.',
            'curp.size' => 'La CURP debe tener 18 caracteres.',
            'curp.regex' => 'La CURP no tiene un formato válido.',
            'curp.unique' => 'Ya existe un docente con esa CURP.',
            'tipo_contratacion.required' => 'Selecciona el tipo de contratación.',
            'tipo_contratacion.in' => 'El tipo de contratación no es válido.',
            'celular.required' => 'El celular es obligatorio.',
            'celular.regex' => 'El celular debe tener 10 dígitos.',
            'sedes.required' => 'Selecciona al menos una sede.',
            'sedes.min' => 'Selecciona al menos una sede.',
        ];
    }
}
