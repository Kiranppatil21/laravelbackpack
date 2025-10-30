<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\EmployeeRecruitRequest;
use App\Models\Employee;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class EmployeeController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if (method_exists($user, 'hasRole') && $user->hasRole('super-admin')) {
            $items = Employee::with('client')->paginate(20);
        } else {
            $items = Employee::where('tenant_id', $user->tenant_id)->with('client')->paginate(20);
        }

        return response()->json($items);
    }

    public function show(Request $request, Employee $employee): JsonResponse
    {
        $this->authorize('view', $employee);

        return response()->json($employee->load('client'));
    }

    public function store(EmployeeRecruitRequest $request): JsonResponse
    {
        $this->authorize('create', Employee::class);

        $data = $request->validated();

        $user = $request->user();
        $data['tenant_id'] = $user->tenant_id ?? null;

        // ensure client belongs to same tenant if provided
        if (! empty($data['client_id'])) {
            $clientBelongs = Client::where('id', $data['client_id'])
                ->where('tenant_id', $data['tenant_id'])
                ->exists();

            if (! $clientBelongs) {
                return response()->json(['message' => 'Client does not belong to your tenant'], 422);
            }
        }

        // handle files
        if ($request->hasFile('aadhar')) {
            $data['aadhar_path'] = Storage::disk(config('filesystems.default'))->putFile('employee_docs', $request->file('aadhar'));
        }

        if ($request->hasFile('pan')) {
            $data['pan_path'] = Storage::disk(config('filesystems.default'))->putFile('employee_docs', $request->file('pan'));
        }

        if ($request->hasFile('police_verification')) {
            $data['police_verification_path'] = Storage::disk(config('filesystems.default'))->putFile('employee_docs', $request->file('police_verification'));
        }

        // JSON-encode shift if provided
        if (! empty($data['shift']) && is_array($data['shift'])) {
            $data['shift'] = json_encode($data['shift']);
        }

        $employee = Employee::create($data);

        return response()->json($employee, 201);
    }

    public function update(EmployeeRecruitRequest $request, Employee $employee): JsonResponse
    {
        $this->authorize('update', $employee);

        $data = $request->validated();

        if (! empty($data['client_id'])) {
            $clientBelongs = Client::where('id', $data['client_id'])
                ->where('tenant_id', $employee->tenant_id)
                ->exists();

            if (! $clientBelongs) {
                return response()->json(['message' => 'Client does not belong to the same tenant'], 422);
            }
        }

        if ($request->hasFile('aadhar')) {
            $data['aadhar_path'] = Storage::disk(config('filesystems.default'))->putFile('employee_docs', $request->file('aadhar'));
        }

        if ($request->hasFile('pan')) {
            $data['pan_path'] = Storage::disk(config('filesystems.default'))->putFile('employee_docs', $request->file('pan'));
        }

        if ($request->hasFile('police_verification')) {
            $data['police_verification_path'] = Storage::disk(config('filesystems.default'))->putFile('employee_docs', $request->file('police_verification'));
        }

        if (! empty($data['shift']) && is_array($data['shift'])) {
            $data['shift'] = json_encode($data['shift']);
        }

        $employee->update($data);

        return response()->json($employee);
    }

    public function destroy(Request $request, Employee $employee): JsonResponse
    {
        $this->authorize('delete', $employee);

        $employee->delete();

        return response()->json(['message' => 'deleted']);
    }
}
