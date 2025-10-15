<?php

declare(strict_types=1);

namespace App;

enum Environment: string
{
    case LIVE = 'live';
    case TEST = 'test';
}
