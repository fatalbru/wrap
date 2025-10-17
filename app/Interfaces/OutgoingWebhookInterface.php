<?php

namespace App\Interfaces;

interface OutgoingWebhookInterface
{
    function getWebhookData(): array;
}
