<?php

declare(strict_types=1);

namespace App;

enum SubscriptionStatus: string
{
    case PENDING = 'pending';
    case AUTHORIZED = 'authorized';
    case PAUSED = 'paused';
    case CANCELLED = 'cancelled';

    function getLabel(): string
    {
        return match ($this) {
            self::PENDING => __('Pending'),
            self::AUTHORIZED => __('Active'),
            self::PAUSED => __('Paused'),
            self::CANCELLED => __('Cancelled'),
        };
    }

    function getBadgeColor(): string
    {
        return match ($this) {
            self::PENDING => 'zinc',
            self::AUTHORIZED => 'blue',
            self::PAUSED => 'indigo',
            self::CANCELLED => 'yellow',
        };
    }
}
