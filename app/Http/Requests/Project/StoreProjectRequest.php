<?php

declare(strict_types=1);

namespace App\Http\Requests\Project;

use App\Enums\ProjectStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProjectRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('projects', 'name')],
            'description' => ['nullable', 'string'],
            'status' => ['required', Rule::enum(ProjectStatus::class)],
            'budget' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'O nome do projeto é obrigatório.',
            'name.string' => 'O nome do projeto deve ser um texto válido.',
            'name.max' => 'O nome do projeto não pode ultrapassar 255 caracteres.',
            'name.unique' => 'Já existe um projeto com este nome.',
            'description.string' => 'A descrição do projeto deve ser um texto válido.',
            'status.required' => 'O status do projeto é obrigatório.',
            'status.enum' => 'O status do projeto deve ser ativo ou inativo.',
            'budget.numeric' => 'O orçamento deve ser um valor numérico válido.',
            'budget.min' => 'O orçamento deve ser maior ou igual a zero.',
        ];
    }
}
