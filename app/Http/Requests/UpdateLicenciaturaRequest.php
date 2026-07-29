<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLicenciaturaRequest extends FormRequest
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
        $licenciaturaId = $this->route('licenciatura')?->id;

        return [
            'nombre_corto' => [
                'required',
                'string',
                'max:50',
                Rule::unique('licenciaturas', 'nombre_corto')->ignore($licenciaturaId),
            ],
            'clave_dgp' => [
                'nullable',
                'string',
                'alpha_num',
                'size:10',
                Rule::unique('licenciaturas', 'clave_dgp')->ignore($licenciaturaId),
            ],
            'nombre' => ['required', 'string', 'max:255'],
            'plan_nombre' => ['nullable', 'string', 'max:255'],
            'anio_plan' => ['nullable', 'integer', 'min:1990', 'max:2100'],
            'activa' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'clave_dgp.alpha_num' => 'La clave DGP solo puede contener letras y números.',
            'clave_dgp.size' => 'La clave DGP debe tener exactamente 10 caracteres.',
            'clave_dgp.unique' => 'Esa clave DGP ya está registrada en otra licenciatura.',
        ];
    }
}
