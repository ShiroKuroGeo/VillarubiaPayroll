<?php

namespace App\Http\Controllers;

use App\Http\Services\AttendanceServices;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    private $attendanceServices;

    public function __construct(AttendanceServices $attendanceServices)
    {
        $this->attendanceServices = $attendanceServices;
    }

    public function createAttendance(Request $request){
        return $this->attendanceServices->createAttendance($request);
    }

    public function updateAttendance(Request $request){
        return $this->attendanceServices->updateAttendance($request);
    }

    public function getAttendances(Request $request){
        return $this->attendanceServices->getAttendances($request);
    }
}
