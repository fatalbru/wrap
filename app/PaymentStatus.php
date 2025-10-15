<?php

declare(strict_types=1);

namespace App;

enum PaymentStatus: string
{
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case AUTHORIZED = 'authorized';
    case IN_PROCESS = 'in_process';
    case IN_MEDIATION = 'in_mediation';
    case REJECTED = 'rejected';
    case CANCELLED = 'cancelled';
    case REFUNDED = 'refunded';
    case CHARGED_BACK = 'charged_back';

    function getLabel(): string
    {
        return match ($this) {
            self::APPROVED => __('Approved'),
            self::AUTHORIZED => __('Authorized'),
            self::IN_PROCESS => __('In Process'),
            self::IN_MEDIATION => __('In Media'),
            self::REJECTED => __('Rejected'),
            self::CANCELLED => __('Cancelled'),
            self::REFUNDED => __('Refunded'),
            self::CHARGED_BACK => __('Charged Back'),
            self::PENDING => __('Pending'),
        };
    }

    function getBadgeColor(): string
    {
        return match ($this) {
            self::APPROVED => 'green',
            self::AUTHORIZED => 'blue',
            self::IN_PROCESS, self::IN_MEDIATION => 'yellow',
            self::REJECTED, self::CANCELLED => 'red',
            self::REFUNDED, self::CHARGED_BACK, self::PENDING => 'zinc',
        };
    }
}
