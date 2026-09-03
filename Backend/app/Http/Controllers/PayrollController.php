<?php

namespace App\Http\Controllers;

use App\Http\Services\PayrollServices;
use Illuminate\Http\Request;

class PayrollController extends Controller
{
    private $payrollServices;

    public function __construct(PayrollServices $payrollServices){
        $this->payrollServices = $payrollServices;
    }

    public function generatePayroll(Request $request){
        return $this->payrollServices->generatePayroll($request);
    }

    public function updateStatus(Request $request){
        return $this->payrollServices->updateStatus($request);
    }

    public function getPayrolls(Request $request){
        return $this->payrollServices->getPayrolls($request);
    }

    public function getPayroll(Request $request){
        return $this->payrollServices->getPayroll($request);
    }

}
