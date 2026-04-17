<?php

declare(strict_types = 1);

namespace App\Actions\Project;

use App\Models\Project;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Fluent;

final readonly class ListProjectAction
{
    /**
     * @template TValue
     *
     * @param Fluent<string, TValue> $params
     * @return LengthAwarePaginator<int, Project>|Collection<int, Project>
     */
    public function execute(Fluent $params): LengthAwarePaginator|Collection
    {
        $query = Project::query();

        /** @var int|null $userId */
        $userId = $params->get('user_id');

        if (is_int($userId)) {
            $query->where('user_id', $userId);
        }

        /** @var string|null $search */
        $search = $params->get('search');

        if (is_string($search) && $search !== '') {
            $query->where(function ($query) use ($search) {
                $query->whereLike('projects.id', "%{$search}%")
                    ->orWhereLike('projects.name', "%{$search}%")
                    ->orWhereLike('projects.description', "%{$search}%")
                    ->orWhereLike('projects.status', "%{$search}%");
            });
        }

        /** @var string|null $status */
        $status = $params->get('status');

        if (is_string($status) && $status !== '') {
            $query->where('projects.status', $status);
        }

        /** @var string|null $column */
        $column = $params->get('column', 'id');

        /** @var string|null $order */
        $order = $params->get('order', 'desc');

        $sortableColumn = match ($column) {
            'name' => 'projects.name',
            'description' => 'projects.description',
            'status' => 'projects.status',
            'created_at' => 'projects.created_at',
            'updated_at' => 'projects.updated_at',
            default => 'projects.id',
        };

        $sortableOrder = in_array($order, ['asc', 'desc'], true) ? $order : 'desc';

        $query->orderBy($sortableColumn, $sortableOrder);

        /** @var bool $paginated */
        $paginated = (bool) $params->get('paginated', false);

        /** @var int $limit */
        $limit = is_numeric($params->get('limit')) ? (int) $params->get('limit') : 15;

        if ($limit < 1) {
            $limit = 15;
        }

        if ($limit > 100) {
            $limit = 100;
        }

        return $paginated
            ? $query->paginate($limit)->withQueryString()
            : $query->get();
    }
}
