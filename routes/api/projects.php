<?php

declare(strict_types=1);

use App\Http\Controllers\Api\Project\ProjectController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')
    ->apiResource('projects', ProjectController::class);
