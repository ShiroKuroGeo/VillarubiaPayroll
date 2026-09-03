<?php

namespace App\Http\Controllers;

use App\Http\Services\SalaryServices;
use Illuminate\Http\Request;

class SalaryController extends Controller
{
    private $salaryServices;

    public function __construct(SalaryServices $salaryServices)
    {
        $this->salaryServices = $salaryServices;
    }

    
    public function createSalary(Request $request){
        return $this->salaryServices->createSalary($request);
    }

    public function updateSalary(Request $request){
        return $this->salaryServices->updateSalary($request);
    }

    public function getSalaries(Request $request){
        return $this->salaryServices->getSalaries($request);
    }

    public function getActiveSalary(Request $request){
        return $this->salaryServices->getActiveSalary($request);
    }

}
