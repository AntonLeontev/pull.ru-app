<?php

namespace App\Services\Planfact\Enums;

enum OperationCategoryType: string
{
    case income = 'Income';
    case outcome = 'Outcome';
    case assets = 'Assets';
    case liabilities = 'Liabilities';
    case capital = 'Capital';
}
