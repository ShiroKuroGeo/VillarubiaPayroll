<?php

namespace App\Http\Controllers;

use App\Http\Services\SSSServices;
use Illuminate\Http\Request;

class SSSController extends Controller
{
    private $sssServices;

    public function __construct(SSSServices $sSSServices)
    {
        $this->sssServices = $sSSServices;
    }

    public function createSSSDeduction(Request $request){
        return $this->sssServices->createSSSDeduction($request);
    }

    public function getSSSDeductionsRecords(Request $request){
        return $this->sssServices->getSSSDeductionsRecords($request);
    }

    public function getSSSDeductionsHistory(Request $request){
        return $this->sssServices->getSSSDeductionsHistory($request);
    }

    public function removeSSSDeduction(Request $request){
        return $this->sssServices->removeSSSDeduction($request);
    }

    public function updateSSSDeduction(Request $request){
        return $this->sssServices->updateSSSDeduction($request);
    }
    
}
