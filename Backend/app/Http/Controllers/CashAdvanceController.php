<?php

namespace App\Http\Controllers;

use App\Http\Services\CashAdvanceServices;
use Illuminate\Http\Request;

class CashAdvanceController extends Controller
{
    private $cashAdvanceServices;

    public function __construct(CashAdvanceServices $cashAdvanceServices)
    {
        $this->cashAdvanceServices = $cashAdvanceServices;
    }

    public function requestCashAdvance(Request $request){
        return $this->cashAdvanceServices->requestCashAdvance($request);
    }

    public function reviewCashAdvance(Request $request){
        return $this->cashAdvanceServices->reviewCashAdvance($request);
    }

    public function getCashAdvances(Request $request){
        return $this->cashAdvanceServices->getCashAdvances($request);
    }

    public function getCashAdvance(Request $request){
        return $this->cashAdvanceServices->getCashAdvance($request);
    }

}
