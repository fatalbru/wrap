<?php

declare(strict_types=1);

namespace App;

enum FrequencyType: string
{
    case DAILY = 'daily';
    case WEEKLY = 'weekly';
    case MONTHLY = 'monthly';
    case YEARLY = 'yearly';

    public function getLabel(): string
    {
        return match ($this) {
            self::DAILY => __('daily'),
            self::WEEKLY => __('weekly'),
            self::MONTHLY => __('monthly'),
            self::YEARLY => __('yearly'),
        };
    }

    public function getFrequencyIterations(): int
    {
        return match ($this) {
            self::DAILY, self::MONTHLY => 1,
            self::WEEKLY => 7,
            self::YEARLY => 12,
        };
    }

    public function getFrequencyApiType(): string
    {
        return match ($this) {
            self::DAILY, self::WEEKLY => 'days',
            self::MONTHLY, self::YEARLY => 'months',
        };
    }

    public function getFrequencyCarbonInterval(): string
    {
        return match ($this) {
            self::DAILY, self::WEEKLY => 'day',
            self::MONTHLY, self::YEARLY => 'month',
        };
    }
}
