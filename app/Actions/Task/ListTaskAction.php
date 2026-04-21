<?php

declare(strict_types = 1);

namespace App\Actions\Task;

use App\Models\Task;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\{Builder, Collection};
use Illuminate\Support\Fluent;
use InvalidArgumentException;

final readonly class ListTaskAction
{
    /**
     * @param Fluent<string, bool|int|string> $params
     * @return LengthAwarePaginator<int, Task>|Collection<int, Task>
     */
    public function execute(Fluent $params): LengthAwarePaginator|Collection
    {
        $userId = max(0, $params->integer('user_id'));

        if ($userId === 0) {
            throw new InvalidArgumentException('user_id is required.');
        }

        $projectId = max(0, $params->integer('project_id'));
        $search = $this->normalizeString($params->get('search'));
        $status = $this->normalizeString($params->get('status'));
        $column = $this->normalizeString($params->get('column')) ?? 'id';
        $order = mb_strtolower($this->normalizeString($params->get('order')) ?? 'desc');
        $limit = max(1, min(100, $params->integer('limit', 15)));
        $paginated = filter_var($params->get('paginated', true), FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE) ?? true;

        $sortableColumn = match ($column) {
            'description' => 'tasks.description',
            'status' => 'tasks.status',
            'start_date' => 'tasks.start_date',
            'end_date' => 'tasks.end_date',
            'created_at' => 'tasks.created_at',
            default => 'tasks.id',
        };

        $sortableOrder = $order === 'asc' ? 'asc' : 'desc';

        $query = Task::query()
            ->with(['project', 'predecessor'])
            ->where('tasks.user_id', $userId)
            ->when(
                $projectId > 0,
                fn (Builder $query): Builder => $query->where('tasks.project_id', $projectId)
            )
            ->when(
                $status !== null,
                fn (Builder $query): Builder => $query->where('tasks.status', $status)
            )
            ->when(
                $search !== null,
                fn (Builder $query): Builder => $query->where(function (Builder $query) use ($search): void {
                    $term = "%{$search}%";

                    $query
                        ->whereLike('tasks.id', $term)
                        ->orWhereLike('tasks.description', $term)
                        ->orWhereLike('tasks.status', $term)
                        ->orWhereHas('project', fn (Builder $projectQuery): Builder => $projectQuery->whereLike('name', $term));
                })
            )
            ->orderBy($sortableColumn, $sortableOrder);

        if (!$paginated) {
            return $query->get();
        }

        return $query->paginate($limit)->withQueryString();
    }

    private function normalizeString(mixed $value): ?string
    {
        if (!is_string($value)) {
            return null;
        }

        $value = mb_trim($value);

        return $value === '' ? null : $value;
    }
}
