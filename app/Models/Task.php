<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TaskStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use SoftDeletes;
    use HasFactory;

    protected $fillable = [
        'description',
        'project_id',
        'predecessor_task_id',
        'status',
        'start_date',
        'end_date',
        'user_id',
    ];

    protected $casts = [
        'status'     => TaskStatus::class,
        'start_date' => 'date',
        'end_date'   => 'date',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function predecessor(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'predecessor_task_id');
    }

    public function successors(): HasMany
    {
        return $this->hasMany(Task::class, 'predecessor_task_id');
    }
}
