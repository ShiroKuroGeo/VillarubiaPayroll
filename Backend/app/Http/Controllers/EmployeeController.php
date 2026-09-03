<?php

namespace App\Http\Controllers;

use App\Http\Services\EmployeeServices;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    private $employeeServices;

    public function __construct(EmployeeServices $employeeServices)
    {
        $this->employeeServices = $employeeServices;
    }

    public function createEmployee(Request $request){
        return $this->employeeServices->createEmployee($request);    
    }

    public function updateImage(Request $request){
        return $this->employeeServices->updateImage($request);    
    }

    public function updateEmployee(Request $request){
        return $this->employeeServices->updateEmployee($request);    
    }

    public function removeEmployee(Request $request){
        return $this->employeeServices->removeEmployee($request);    
    }

    public function restoreEmployee(Request $request){
        return $this->employeeServices->restoreEmployee($request);    
    }

    public function getEmployees(Request $request){
        return $this->employeeServices->getEmployees($request);    
    }

    public function getEmployee(Request $request){
        return $this->employeeServices->getEmployee($request);    
    }

    public function getJobTypes(){
        return $this->employeeServices->getJobTypes();
    }

}
