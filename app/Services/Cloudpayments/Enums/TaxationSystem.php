<?php

namespace App\Services\Cloudpayments\Enums;

enum TaxationSystem: int
{
    case osn = 0;
    case usn_income = 1;
    case usn_income_outcome = 2;
    case envd = 3;
    case esn = 4;
    case patent = 5;
}
