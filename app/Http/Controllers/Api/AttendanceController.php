<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    public function checkIn(Request $request)
    {
        $data = $request->validate([
            'employee_id' => 'required|integer',
            'tenant_uuid' => 'required|uuid',
            'check_in_type' => 'nullable|string',
            'check_in_meta' => 'nullable|array',
            'shift_id' => 'nullable|integer',
        ]);

        $data['check_in_at'] = now();
        $data['created_by'] = Auth::id();

        // Prevent creating duplicate active check-in for same employee
        $active = Attendance::where('employee_id', $data['employee_id'])
            ->whereNull('check_out_at')
            ->first();

        if ($active) {
            return response()->json(['message' => 'Active check-in exists'], 409);
        }

        $attendance = Attendance::create($data);

        return response()->json($attendance, 201);
    }

    public function checkOut(Request $request)
    {
        $data = $request->validate([
            'employee_id' => 'required|integer',
            'tenant_uuid' => 'required|uuid',
        ]);

        $attendance = Attendance::where('employee_id', $data['employee_id'])
            ->whereNull('check_out_at')
            ->latest('check_in_at')
            ->first();

        if (! $attendance) {
            return response()->json(['message' => 'No active check-in found'], 404);
        }

        $attendance->check_out_at = now();
        $attendance->save();

        return response()->json($attendance, 200);
    }

    public function report(Request $request)
    {
        $query = Attendance::query();

        if ($request->filled('tenant_uuid')) {
            $query->where('tenant_uuid', $request->input('tenant_uuid'));
        }

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->input('employee_id'));
        }

        if ($request->filled('from')) {
            $query->where('check_in_at', '>=', $request->input('from'));
        }

        if ($request->filled('to')) {
            $query->where('check_in_at', '<=', $request->input('to'));
        }

        $perPage = min(100, (int) $request->input('per_page', 25));

        $result = $query->orderBy('check_in_at', 'desc')->paginate($perPage);

        return response()->json($result);
    }
}
