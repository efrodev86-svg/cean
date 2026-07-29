<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

abstract class AlumnoRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        foreach (['matricula', 'curp', 'email_institucional', 'email_personal'] as $campo) {
            if ($this->has($campo) && ! filled($this->input($campo))) {
                $this->merge([$campo => null]);
            }
        }

        if ($this->filled('curp')) {
            $curp = strtoupper((string) $this->input('curp'));
            $this->merge([
                'curp' => $curp,
                'referencia_pago' => \App\Support\ReferenciaPago::desdeCurp($curp),
            ]);
        }

        foreach (['tiene_diagnostico', 'tiene_discapacidad', 'labora'] as $booleano) {
            $this->merge([$booleano => $this->boolean($booleano)]);
        }

        if (! $this->boolean('tiene_diagnostico')) {
            $this->merge(['diagnostico_detalle' => null]);
        }

        if (! $this->boolean('tiene_discapacidad')) {
            $this->merge(['discapacidad_detalle' => null]);
        }

        if (! $this->boolean('labora')) {
            $this->merge(['lugar_trabajo' => null]);
        }

        if ($this->filled('estatus')) {
            $this->merge(['estatus' => \App\Support\AlumnoEstatus::normalizar($this->input('estatus'))]);
        }

        if ($this->filled('tipo_ingreso')) {
            $this->merge(['tipo_ingreso' => \App\Support\AlumnoTipoIngreso::normalizar($this->input('tipo_ingreso'))]);
        }

        if ($this->has('estado_civil')) {
            $this->merge([
                'estado_civil' => filled($this->input('estado_civil'))
                    ? \App\Support\AlumnoEstadoCivil::normalizar($this->input('estado_civil'))
                    : null,
            ]);
        }

        if (! \App\Support\AlumnoTipoIngreso::esTraslado($this->input('tipo_ingreso'))) {
            $this->merge([
                'entidad_procedencia' => null,
                'ciudad_procedencia' => null,
            ]);
        }

        $this->merge([
            'email_acceso' => \App\Support\AlumnoFicha::resolverEmailAcceso(
                $this->input('email_institucional'),
                $this->input('email_personal'),
            ),
        ]);
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if (! filled($this->input('email_acceso'))) {
                $validator->errors()->add('email_institucional', 'Indica al menos un correo institucional o personal.');
            }

            $celular = $this->soloDigitos($this->input('celular'));
            $telefonoEmergencia = $this->soloDigitos($this->input('telefono_emergencia'));

            if (filled($celular) && filled($telefonoEmergencia) && $celular === $telefonoEmergencia) {
                $validator->errors()->add(
                    'telefono_emergencia',
                    'El teléfono de emergencia debe ser distinto al celular.',
                );
            }
        });
    }

    private function soloDigitos(mixed $valor): string
    {
        return preg_replace('/\D+/', '', (string) $valor) ?? '';
    }

    /**
     * @return array<string, mixed>
     */
    protected function reglasPerfilAlumno(?int $alumnoId = null, ?int $userId = null): array
    {
        return [
            'grupo_id' => ['required', 'integer', 'exists:grupos,id'],
            'matricula' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('alumnos', 'matricula')->ignore($alumnoId),
            ],
            'nombres' => ['required', 'string', 'max:120'],
            'apellido_paterno' => ['required', 'string', 'max:120'],
            'apellido_materno' => ['nullable', 'string', 'max:120'],
            'curp' => [
                'required',
                'string',
                'size:18',
                Rule::unique('alumnos', 'curp')->ignore($alumnoId),
            ],
            'fecha_nacimiento' => ['required', 'date'],
            'referencia_pago' => ['nullable', 'string', 'max:40'],
            'email_institucional' => ['nullable', 'string', 'lowercase', 'email', 'max:255'],
            'email_personal' => ['nullable', 'string', 'lowercase', 'email', 'max:255'],
            'email_acceso' => [
                'nullable',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'domicilio' => ['nullable', 'string', 'max:255'],
            'colonia' => ['nullable', 'string', 'max:120'],
            'codigo_postal' => ['nullable', 'string', 'max:10'],
            'estado' => ['nullable', 'string', 'max:80'],
            'municipio' => ['nullable', 'string', 'max:80'],
            'celular' => ['nullable', 'string', 'max:20'],
            'telefono_emergencia' => ['nullable', 'string', 'max:20'],
            'nss' => ['nullable', 'string', 'max:20'],
            'tiene_diagnostico' => ['sometimes', 'boolean'],
            'diagnostico_detalle' => ['nullable', 'string', 'max:500', 'required_if:tiene_diagnostico,1,true'],
            'tiene_discapacidad' => ['sometimes', 'boolean'],
            'discapacidad_detalle' => ['nullable', 'string', 'max:500', 'required_if:tiene_discapacidad,1,true'],
            'estado_civil' => ['nullable', 'string', Rule::in(array_keys(\App\Support\AlumnoEstadoCivil::opciones()))],
            'labora' => ['sometimes', 'boolean'],
            'lugar_trabajo' => ['nullable', 'string', 'max:120', 'required_if:labora,1,true'],
            'estatus' => ['required', 'string', Rule::in(array_keys(\App\Support\AlumnoEstatus::opciones()))],
            'tipo_ingreso' => ['required', 'string', Rule::in(array_keys(\App\Support\AlumnoTipoIngreso::opciones()))],
            'entidad_procedencia' => [
                'nullable',
                'string',
                'max:200',
                Rule::requiredIf(fn () => \App\Support\AlumnoTipoIngreso::esTraslado($this->input('tipo_ingreso'))),
            ],
            'ciudad_procedencia' => [
                'nullable',
                'string',
                'max:120',
                Rule::requiredIf(fn () => \App\Support\AlumnoTipoIngreso::esTraslado($this->input('tipo_ingreso'))),
            ],
            'asignatura_adeuda' => ['nullable', 'string', 'max:500'],
            'activo' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function mensajesPerfilAlumno(): array
    {
        return [
            'grupo_id.required' => 'Selecciona el grupo escolar.',
            'matricula.unique' => 'Ya existe un alumno con esa matrícula.',
            'curp.required' => 'La CURP es obligatoria.',
            'curp.size' => 'La CURP debe tener 18 caracteres.',
            'curp.unique' => 'Ya existe un alumno con esa CURP.',
            'email_acceso.unique' => 'Ya existe un usuario con ese correo de acceso.',
            'estatus.required' => 'Selecciona el estatus del alumno.',
            'estatus.in' => 'El estatus seleccionado no es válido.',
            'tipo_ingreso.required' => 'Selecciona el tipo de ingreso.',
            'tipo_ingreso.in' => 'El tipo de ingreso seleccionado no es válido.',
            'estado_civil.in' => 'Selecciona un estado civil válido.',
            'diagnostico_detalle.required_if' => 'Especifica el diagnóstico.',
            'discapacidad_detalle.required_if' => 'Especifica la discapacidad.',
            'lugar_trabajo.required_if' => 'Indica dónde labora.',
            'entidad_procedencia.required' => 'Indica la escuela o entidad de procedencia.',
            'ciudad_procedencia.required' => 'Indica la ciudad de procedencia.',
        ];
    }
}
