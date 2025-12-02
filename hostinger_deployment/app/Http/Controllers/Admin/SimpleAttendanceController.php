<?php

namespace App\Http\Controllers\Admin;

use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class SimpleAttendanceController extends Controller
{
    public function index()
    {
        $attendances = Attendance::paginate(15);
        
        return view('admin.simple_attendance', compact('attendances'));
    }
}