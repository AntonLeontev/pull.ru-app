<?php

namespace Src\Domain\FinancialAccounting\Enums;

enum CdekExpendsImportStatus: string
{
    case new = 'new';
    case working = 'working';
    case success = 'success';
    case error = 'error';
}
