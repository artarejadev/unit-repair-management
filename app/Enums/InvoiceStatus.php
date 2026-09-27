<?php

namespace App\Enums;

enum InvoiceStatus: string
{
    case ISSUED = 'ISSUED';
    case PAID = 'PAID';
    case CANCELED = 'CANCELED';
}