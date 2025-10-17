<?php

namespace App\Interfaces;

use Illuminate\Database\Eloquent\Model;

interface OutgoingWebhookInterface
{
    function getWebhookData(): array;

    function getModel(): Model;
}
