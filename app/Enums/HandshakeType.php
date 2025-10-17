<?php

namespace App\Enums;

enum HandshakeType: string
{
    case REROUTE = 'reroute';
    case JOB = 'job';
}
