<?php

declare(strict_types=1);

namespace App\Http\Requests\Project;

use App\Enums\ProjectStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'        => ['sometimes', 'string', 'max:255', 'unique:projects,name,' . $this->route('project')?->id],
            'description' => ['nullable', 'string'],
            'status'      => ['sometimes', new Enum(ProjectStatus::class)],
            'budget'      => ['nullable', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.max'       => 'O nome do projeto não pode ultrapassar 255 caracteres.',
            'name.unique'    => 'Já existe um projeto com este nome.',
            'status'         => 'O status do projeto deve ser ativo ou inativo.',
            'budget.numeric' => 'O orçamento deve ser um valor numérico válido.',
            'budget.min'     => 'O orçamento deve ser maior ou igual a zero.',
        ];
    }
}
