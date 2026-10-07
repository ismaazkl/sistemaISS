<?php

namespace App\Http\Requests;

use App\Models\Curso;
use App\Models\Ficha;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveFichaRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $teamId = (int) $this->user()->current_team_id;

        return [
            'estudiante' => ['required', 'string', 'min:3', 'max:120'],
            'representante' => ['required', 'string', 'min:3', 'max:120'],
            'cedula_estudiante' => [
                'required',
                'string',
                'digits:10',
                Rule::unique(Ficha::class, 'cedula_estudiante')
                    ->where('team_id', $teamId)
                    ->ignore($this->route('ficha')),
            ],
            'cedula_representante' => ['required', 'string', 'digits:10'],
            'telefono' => ['nullable', 'string', 'max:20', 'regex:/^[0-9]{7,15}$/'],
            'causa_salida' => ['nullable', 'string', 'max:255'],
            'fecha' => ['required', 'date', 'before_or_equal:today'],
            'hora' => ['nullable', 'date_format:H:i'],
            'curso_id' => [
                'required',
                'integer',
                Rule::exists(Curso::class, 'id')->where('team_id', $teamId),
            ],
        ];
    }

    /**
     * Get the custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'estudiante.required' => 'Ingresa el nombre del estudiante.',
            'representante.required' => 'Ingresa el nombre del representante.',
            'cedula_estudiante.digits' => 'La cédula del estudiante debe tener 10 dígitos.',
            'cedula_estudiante.unique' => 'Ya existe una ficha con esta cédula.',
            'cedula_representante.digits' => 'La cédula del representante debe tener 10 dígitos.',
            'telefono.regex' => 'El teléfono debe contener entre 7 y 15 dígitos.',
            'causa_salida.max' => 'La causa o motivo de salida no puede superar los 255 caracteres.',
            'fecha.before_or_equal' => 'La fecha no puede ser futura.',
            'hora.date_format' => 'La hora debe tener el formato HH:MM.',
            'curso_id.required' => 'Selecciona el curso.',
            'curso_id.exists' => 'El curso seleccionado no es válido.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'estudiante' => 'estudiante',
            'representante' => 'representante',
            'cedula_estudiante' => 'cédula del estudiante',
            'cedula_representante' => 'cédula del representante',
            'telefono' => 'teléfono',
            'causa_salida' => 'causa o motivo de salida',
            'fecha' => 'fecha',
            'hora' => 'hora',
            'curso_id' => 'curso',
        ];
    }
}
