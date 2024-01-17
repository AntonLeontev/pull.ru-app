<?php

namespace Src\Domain\Synchronizer\Enums;

enum OrderStatus: string
{
    case init = 'init';
    case declined = 'declined';
}
