<?php

declare(strict_types=1);

namespace Tests\Unit\Enums;

use App\Enums\TaskStatus;

describe('TaskStatus', function () {
    it('has correct values', function () {
        expect(TaskStatus::COMPLETED->value)->toBe('completed')
            ->and(TaskStatus::NOT_COMPLETED->value)->toBe('not_completed');
    });

    it('can be instantiated from value', function () {
        expect(TaskStatus::from('completed'))->toBe(TaskStatus::COMPLETED)
            ->and(TaskStatus::from('not_completed'))->toBe(TaskStatus::NOT_COMPLETED);
    });

    it('returns null for invalid value with tryFrom', function () {
        expect(TaskStatus::tryFrom('invalid'))->toBeNull();
    });

    it('has all expected cases', function () {
        expect(TaskStatus::cases())->toHaveCount(2);
    });
})->group('enums');
