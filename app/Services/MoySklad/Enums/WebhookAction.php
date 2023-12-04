<?php

namespace App\Services\MoySklad\Enums;

enum WebhookAction: string
{
    case create = 'CREATE';
    case update = 'UPDATE';
    case delete = 'DELETE';
    case processed = 'PROCESSED';
}
