<?php

namespace App\Http\Controllers;

use App\Http\Services\DeductionServices;
use Illuminate\Http\Request;

class DeductionController extends Controller
{
    private $deductionServices;

    public function __construct(DeductionServices $deductionServices)
    {
        $this->deductionServices = $deductionServices;
    }

    public function createDeduction(Request $request){
        return $this->deductionServices->createDeduction($request);
    }

    public function getDeductions(Request $request){
        return $this->deductionServices->getDeductions($request);
    }

    public function getDeduction(Request $request){
        return $this->deductionServices->getDeduction($request);
    }

}
