<?php

namespace App\Http\Services;

use App\Models\Maintenance;
use Illuminate\Http\Request;

class MaintenanceServices
{
    public function createMaintenance(Request $request)
    {
        try {
            $validation = $request->validate([
                'label' => ['required'],
                'value' => ['required']
            ]);

            $createNewMaintenance = Maintenance::create([
                'label' => $validation['label'],
                'value' => $validation['value'],
                'status' => 'Online'
            ]);

            if (!$createNewMaintenance) return response_return('Failed to create new maintenance.', [], 409);

            return response_return('Succesfully created new maintenance.', [
                'maintenance_name' => $createNewMaintenance->label,
                'maintenance_value' => $createNewMaintenance->value,
                'maintenance_status' => $createNewMaintenance->status,
            ], 201);
        } catch (\Throwable $th) {
            return response_return('Error occurred in creating a maintenance.', [], 500);
        }
    }

    public function updateMaintenance(Request $request)
    {
        try {
            $validation = $request->validate([
                'id' => ['required', 'exists:maintenances,id']
            ]);

            $checkMaintenance = Maintenance::where('id', $validation['id'])->first();

            if (!$checkMaintenance) return response_return('There is no existing maintenance', [], 409);

            $updateMaintenance = $checkMaintenance->create($request->only(['label', 'value', 'status']));

            if (!$updateMaintenance) return response_return('Failed to update the maintenance', [], 409);

            return response_return('Succesfully updated the maintenance', [
                'label' => $checkMaintenance->label,
                'value' => $checkMaintenance->value,
                'status' => $checkMaintenance->status
            ], 201);
        } catch (\Throwable $th) {
            return response_return('Error occurred in updating the maintenance', [], 500);
        }
    }
    
    public function removeMaintenance(Request $request)
    {
        try {
            $checkMaintenance = Maintenance::where('id', $request->id)->first();

            if (!$checkMaintenance) return response_return("We couldn't find this maintenance. Please check and try again.", [], 409);

            $removeMaintenance = $checkMaintenance->delete();

            if (!$removeMaintenance) return response_return('Failed to remove the maintenance', [], 409);

            return response_return('Successfully remove the maintenance', [], 201);
        } catch (\Throwable $th) {
            return response_return('Error occurred in removing the maintenance', [], 500);
        }
    }
}
