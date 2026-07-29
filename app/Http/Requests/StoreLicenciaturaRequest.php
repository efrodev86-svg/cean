<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLicenciaturaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    protected function prepareForValidation(): void
    {
        if ($this->input('clave_dgp') === '') {
            $this->merge(['clave_dgp' => null]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'nombre_corto' => ['required', 'string', 'max:50', 'unique:licenciaturas,nombre_corto'],
            'clave_dgp' => ['nullable', 'string', 'alpha_num', 'size:10', 'unique:licenciaturas,clave_dgp'],
            'nombre' => ['required', 'string', 'max:255'],
            'plan_nombre' => ['required', 'string', 'max:255'],
            'anio_plan' => ['required', 'integer', 'min:1990', 'max:2100'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nombre_corto.required' => 'El nombre corto es obligatorio.',
            'nombre.required' => 'El nombre es obligatorio.',
            'plan_nombre.required' => 'El nombre del plan es obligatorio.',
            'anio_plan.required' => 'El año del plan es obligatorio.',
            'clave_dgp.alpha_num' => 'La clave DGP solo puede contener letras y números.',
            'clave_dgp.size' => 'La clave DGP debe tener exactamente 10 caracteres.',
            'clave_dgp.unique' => 'Esa clave DGP ya está registrada en otra licenciatura.',
        ];
    }
}
