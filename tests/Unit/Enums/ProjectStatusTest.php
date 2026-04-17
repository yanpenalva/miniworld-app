<?php

declare(strict_types=1);

namespace Tests\Unit\Enums;

use App\Enums\ProjectStatus;

describe('ProjectStatus', function () {
    it('has correct values', function () {
        expect(ProjectStatus::ACTIVE->value)->toBe('active')
            ->and(ProjectStatus::INACTIVE->value)->toBe('inactive');
    });

    it('can be instantiated from value', function () {
        expect(ProjectStatus::from('active'))->toBe(ProjectStatus::ACTIVE)
            ->and(ProjectStatus::from('inactive'))->toBe(ProjectStatus::INACTIVE);
    });

    it('returns null for invalid value with tryFrom', function () {
        expect(ProjectStatus::tryFrom('invalid'))->toBeNull();
    });

    it('has all expected cases', function () {
        expect(ProjectStatus::cases())->toHaveCount(2);
    });
})->group('enums');
