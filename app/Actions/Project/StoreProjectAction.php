<?php

declare(strict_types=1);

namespace App\Actions\Project;

use App\Models\Project;
use App\Models\User;

class StoreProjectAction
{
    public function handle(User $user, array $data): Project
    {
        return $user->projects()->create($data);
    }
}
