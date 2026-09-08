<?php

namespace App\Http\Services;

use App\Models\Maintenance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
            $validated = $request->validate([
                'settings' => 'required|array|min:1',
                'settings.*.id' => 'required|integer|exists:maintenances,id',
                'settings.*.value' => 'nullable|string',
            ]);
        } catch (\Throwable $th) {
            return response_return($th->getMessage(), [], 500);
        }

        try {
            DB::transaction(function () use ($validated) {
                foreach ($validated['settings'] as $setting) {
                    Maintenance::where('id', $setting['id'])
                        ->update(['value' => $setting['value']]);
                }
            });

            return response_return('Settings updated successfully.', [], 200);
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

    public function getMaintenances()
    {
        try {
            $getMaintenance = Maintenance::orderBy('id', 'ASC')->get();

            $data = $getMaintenance->map(function ($maintenance) {
                return [
                    'main_id' => $maintenance->id,
                    'main_name' => $maintenance->name,
                    'main_desc' => $maintenance->description,
                    'main_value' => $maintenance->value,
                    'main_tags' => $maintenance->tags,
                    'main_is_section' => $maintenance->is_section,
                    'main_section_name' => $maintenance->section_name,
                    'main_input_type' => $maintenance->input_type,
                    'main_status' => $maintenance->status,
                ];
            });

            return response_return('Successfully retrieve the maintenance', $data->toArray(), 200);
        } catch (\Throwable $th) {
            return response_return('Error occurred in retrieving the maintenance', [], 500);
        }
    }
}
