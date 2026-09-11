<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Maintenance;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class MaintenanceSeeder extends Seeder
{
    public function run(): void
    {

        User::create([
            'name' => 'Villarubia Inoc Fam',
            'email' => 'villarubiaInoc@gmail.com',
            'password' => Hash::make('Villarubia00'),
            'role' => 'admin',
        ]);

        DB::table('job_types')->insert([
            ['label' => 'Upholstery Installer', 'value' => 'upholstery_installer', 'status' => 'Active'],
            ['label' => 'Furniture Repair Technician', 'value' => 'furniture_repair', 'status' => 'Active'],
            ['label' => 'Foam Cutting / Cushion Specialist', 'value' => 'foam_specialist', 'status' => 'Active'],
            ['label' => 'Pattern Maker', 'value' => 'pattern_maker', 'status' => 'Active'],
            ['label' => 'Upholstery Sewing Specialist', 'value' => 'sewing_specialist', 'status' => 'Active'],
            ['label' => 'Fabric Specialist', 'value' => 'fabric_specialist', 'status' => 'Active'],
            ['label' => 'Leather Specialist', 'value' => 'leather_specialist', 'status' => 'Active'],
            ['label' => 'Wood Frame / Furniture Carpenter', 'value' => 'carpenter', 'status' => 'Active'],
            ['label' => 'Furniture Restoration Specialist', 'value' => 'restoration_specialist', 'status' => 'Active'],
            ['label' => 'Cushion Maker', 'value' => 'cushion_maker', 'status' => 'Active'],
            ['label' => 'Foam & Padding Installer', 'value' => 'padding_installer', 'status' => 'Active'],
            ['label' => 'Upholstery Apprentice', 'value' => 'apprentice', 'status' => 'Active'],
            ['label' => 'Quality Control Inspector', 'value' => 'quality_control', 'status' => 'Active'],
            ['label' => 'Production Assistant', 'value' => 'production_assistant', 'status' => 'Active'],
            ['label' => 'Shop / Workshop Assistant', 'value' => 'workshop_assistant', 'status' => 'Active'],
            ['label' => 'Furniture Assembler', 'value' => 'furniture_assembler', 'status' => 'Active'],
            ['label' => 'Spray / Adhesive Technician', 'value' => 'spray_technician', 'status' => 'Active'],
            ['label' => 'Furniture Stripping Technician', 'value' => 'stripping_technician', 'status' => 'Active'],
            ['label' => 'Upholstery Cleaner', 'value' => 'upholstery_cleaner', 'status' => 'Active'],
            ['label' => 'Inventory / Materials Coordinator', 'value' => 'inventory_coordinator', 'status' => 'Active'],
        ]);

        Maintenance::create([
            'name' => 'Payroll & Cash Advance',
            'description' => 'Controls used when computing net pay and employee cash advances',
            'value' => null,
            'tags' => 'payroll,cash advance',
            'is_section' => true,
            'section_name' => null,
            'input_type' => null,
            'status' => 'Online',
        ]);

        Maintenance::create([
            'name' => 'Cash Advance Limit',
            'description' => 'Maximum outstanding cash advance balance allowed per employee at any time.',
            'value' => '0',
            'tags' => 'cash advance,payroll',
            'is_section' => false,
            'section_name' => 'Payroll & Cash Advance',
            'input_type' => 'number',
            'status' => 'Online',
        ]);

        Maintenance::create([
            'name' => 'Pay Period',
            'description' => 'How often employees are paid. Affects payroll run frequency. Strictly Weekly.',
            'value' => 'Weekly',
            'tags' => 'payroll,pay period',
            'is_section' => false,
            'section_name' => 'Payroll & Cash Advance',
            'input_type' => 'pay-period',
            'status' => 'Online',
        ]);

        Maintenance::create([
            'name' => 'Overtime & Attendance Rules',
            'description' => 'Work schedule, lateness, and overtime computation rules',
            'value' => null,
            'tags' => 'overtime,attendance',
            'is_section' => true,
            'section_name' => null,
            'input_type' => null,
            'status' => 'Online',
        ]);

        Maintenance::create([
            'name' => 'Overtime Rate (Per Hour)',
            'description' => 'Flat peso amount paid for every hour of approved overtime work.',
            'value' => '0',
            'tags' => 'overtime',
            'is_section' => false,
            'section_name' => 'Overtime & Attendance Rules',
            'input_type' => 'number',
            'status' => 'Online',
        ]);

        Maintenance::create([
            'name' => 'Overtime Multiplier',
            'description' => 'Multiplier applied to the base overtime rate on rest days or holidays.',
            'value' => '1',
            'tags' => 'overtime,holiday',
            'is_section' => false,
            'section_name' => 'Overtime & Attendance Rules',
            'input_type' => 'number',
            'status' => 'Online',
        ]);

        Maintenance::create([
            'name' => 'Standard Working Hours',
            'description' => 'Expected number of work hours per day before overtime kicks in.',
            'value' => '8',
            'tags' => 'attendance,working hours',
            'is_section' => false,
            'section_name' => 'Overtime & Attendance Rules',
            'input_type' => 'number',
            'status' => 'Online',
        ]);

        Maintenance::create([
            'name' => 'Work Days per Week',
            'description' => 'Number of scheduled work days used for weekly and monthly attendance calculations.',
            'value' => '5',
            'tags' => 'attendance,work days',
            'is_section' => false,
            'section_name' => 'Overtime & Attendance Rules',
            'input_type' => 'number',
            'status' => 'Online',
        ]);

        Maintenance::create([
            'name' => 'Work Start Time',
            'description' => 'Official shift start time used to determine lateness.',
            'value' => '08:00',
            'tags' => 'attendance,schedule',
            'is_section' => false,
            'section_name' => 'Overtime & Attendance Rules',
            'input_type' => 'time',
            'status' => 'Online',
        ]);

        Maintenance::create([
            'name' => 'Work End Time',
            'description' => 'Official shift end time.',
            'value' => '17:00',
            'tags' => 'attendance,schedule',
            'is_section' => false,
            'section_name' => 'Overtime & Attendance Rules',
            'input_type' => 'time',
            'status' => 'Online',
        ]);

        Maintenance::create([
            'name' => 'Late Deduction (Per Minute)',
            'description' => 'Amount deducted from pay for every minute an employee clocks in beyond the grace period.',
            'value' => '0',
            'tags' => 'attendance,late deduction',
            'is_section' => false,
            'section_name' => 'Overtime & Attendance Rules',
            'input_type' => 'number',
            'status' => 'Online',
        ]);

        Maintenance::create([
            'name' => 'Company Information',
            'description' => 'General details used across payslips and reports',
            'value' => null,
            'tags' => 'company,information',
            'is_section' => true,
            'section_name' => null,
            'input_type' => null,
            'status' => 'Online',
        ]);

        Maintenance::create([
            'name' => 'Company Name',
            'description' => 'Displayed on payslips, reports, and exported files.',
            'value' => null,
            'tags' => 'company,payslip,reports',
            'is_section' => false,
            'section_name' => 'Company Information',
            'input_type' => 'text',
            'status' => 'Online',
        ]);

        Maintenance::create([
            'name' => 'Grace Period',
            'description' => 'Allowed late threshold in minutes before tardiness deduction applies.',
            'value' => '15',
            'tags' => 'attendance,late deduction',
            'is_section' => false,
            'section_name' => 'Company Information',
            'input_type' => 'number',
            'status' => 'Online',
        ]);
    }
}
