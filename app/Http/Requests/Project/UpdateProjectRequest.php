<?php

declare(strict_types = 1);

namespace App\Http\Requests\Project;

use App\Enums\ProjectStatus;
use App\Models\Project;
use App\Traits\FailedValidation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\{Enum, Unique};

final class UpdateProjectRequest extends FormRequest
{
    use FailedValidation;

    /**
     * @return array<string, list<string|Enum|Unique>>
     */
    public function rules(): array
    {
        $project = $this->route('project');
        $projectId = $project instanceof Project ? $project->getKey() : null;

        return [
            'name' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('projects', 'name')->ignore($projectId),
            ],
            'description' => ['sometimes', 'nullable', 'string', 'max:5000'],
            'status' => ['sometimes', Rule::enum(ProjectStatus::class)],
            'budget' => ['sometimes', 'nullable', 'numeric', 'min:0'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.string' => 'O nome do projeto deve ser um texto válido.',
            'name.max' => 'O nome do projeto não pode ultrapassar 255 caracteres.',
            'name.unique' => 'Já existe um projeto com este nome.',
            'description.string' => 'A descrição do projeto deve ser um texto válido.',
            'status.enum' => 'O status do projeto deve ser ativo ou inativo.',
            'budget.numeric' => 'O orçamento deve ser um valor numérico válido.',
            'budget.min' => 'O orçamento deve ser maior ou igual a zero.',
            'description.max' => 'A descrição do projeto não pode ultrapassar 5000 caracteres.',
        ];
    }
}
