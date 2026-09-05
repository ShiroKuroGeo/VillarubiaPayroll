<?php

namespace App\Http\Controllers;

use App\Http\Services\PayrollServices;

abstract class Controller
{
    public function ensureActivePayrollThisMonth()
    {
        return app(PayrollServices::class)->ensureActivePayrollThisMonth();
    }
}
