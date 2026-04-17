<?php

declare(strict_types=1);

namespace App\Http\Requests\Task;

use App\Enums\TaskStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTaskRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'description' => ['required', 'string'],
            'project_id' => ['required', 'integer', Rule::exists('projects', 'id')],
            'predecessor_task_id' => ['nullable', 'integer', Rule::exists('tasks', 'id'), 'different:id'],
            'status' => ['required', Rule::enum(TaskStatus::class)],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'description.required' => 'A descrição da tarefa é obrigatória.',
            'description.string' => 'A descrição da tarefa deve ser um texto válido.',
            'project_id.required' => 'O projeto é obrigatório.',
            'project_id.integer' => 'O projeto informado é inválido.',
            'project_id.exists' => 'O projeto informado não existe.',
            'predecessor_task_id.integer' => 'A tarefa predecessora informada é inválida.',
            'predecessor_task_id.exists' => 'A tarefa predecessora informada não existe.',
            'predecessor_task_id.different' => 'A tarefa não pode ser predecessora de si mesma.',
            'status.required' => 'O status da tarefa é obrigatório.',
            'status.enum' => 'O status da tarefa deve ser concluída ou não concluída.',
            'start_date.date' => 'A data de início deve ser uma data válida.',
            'end_date.date' => 'A data de fim deve ser uma data válida.',
            'end_date.after_or_equal' => 'A data de fim não pode ser anterior à data de início.',
        ];
    }
}
