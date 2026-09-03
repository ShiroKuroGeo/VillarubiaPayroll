<?php

namespace App\Http\Controllers;

use App\Http\Services\MaintenanceServices;
use Illuminate\Http\Request;

class MaintenanceController extends Controller
{
    private $maintenanceServices;

    public function __construct(MaintenanceServices $maintenanceServices)
    {
        $this->maintenanceServices = $maintenanceServices;
    }

    public function createMaintenance(Request $request){
        return $this->maintenanceServices->createMaintenance($request);
    }

    public function updateMaintenance(Request $request){
        return $this->maintenanceServices->updateMaintenance($request);
    }

    public function removeMaintenance(Request $request){
        return $this->maintenanceServices->removeMaintenance($request);
    }

}
