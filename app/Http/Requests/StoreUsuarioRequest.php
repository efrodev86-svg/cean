<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class StoreUsuarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    protected function prepareForValidation(): void
    {
        if ($this->input('sede_id') === '') {
            $this->merge(['sede_id' => null]);
        }

        if ($this->input('codigo') === '') {
            $this->merge(['codigo' => null]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', 'in:admin,encargado,docente,encargado-docente'],
            'sede_id' => [
                'nullable',
                'integer',
                'exists:sedes,id',
                \Illuminate\Validation\Rule::requiredIf(fn () => in_array($this->input('role'), ['encargado', 'encargado-docente'], true)),
            ],
            'codigo' => ['nullable', 'string', 'max:50', 'unique:users,codigo'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'El nombre es obligatorio.',
            'email.required' => 'El correo es obligatorio.',
            'email.unique' => 'Ya existe un usuario con ese correo.',
            'role.in' => 'El rol no es válido.',
            'sede_id.required_if' => 'Un encargado o encargado-docente debe estar asignado a una sede.',
            'password.required' => 'Define una contraseña inicial.',
            'codigo.unique' => 'Ese código ya está en uso.',
        ];
    }
}
