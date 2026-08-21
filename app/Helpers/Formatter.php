<?php

namespace App\Helpers;

use App\Models\CashFlow;

class Formatter {

    /**
     * Generate cash flow reference number (001/JATIM//02/2026)
     *
     * @var array<int, string>
     */
    public static function generateCashFlowReferenceNumber()
    {
        // generate reference number
        $now = now();
        $number = CashFlow::whereYear('created_at', $now->year)->count() + 1;
        $referenceNumber = str_pad($number, 3, '0', STR_PAD_LEFT) . '/JATIM/'. $now->format('m') . '/' . $now->year;
        return $referenceNumber;
    }
}
