<?php

namespace App\Interfaces;

use Illuminate\Database\Eloquent\Model;

interface OutgoingWebhookInterface
{
    public function getWebhookData(): array;

    public function getModel(): Model;
}
