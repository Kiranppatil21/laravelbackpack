<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Agency;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AgencyController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        // Super admins see all agencies; otherwise scope by tenant
        if (method_exists($user, 'hasRole') && $user->hasRole('super-admin')) {
            $items = Agency::with('clients')->paginate(20);
        } else {
            $items = Agency::where('tenant_id', $user->tenant_id)->with('clients')->paginate(20);
        }

        return response()->json($items);
    }

    public function show(Request $request, Agency $agency): JsonResponse
    {
        $this->authorize('view', $agency);

        return response()->json($agency->load('clients'));
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Agency::class);

        $data = $request->validate([
            'name' => 'required|string|max:191',
        ]);

        $user = $request->user();
        // assign tenant to new agency
        $data['tenant_id'] = $user->tenant_id ?? null;

        $agency = Agency::create($data);

        return response()->json($agency, 201);
    }

    public function update(Request $request, Agency $agency): JsonResponse
    {
        $this->authorize('update', $agency);

        $data = $request->validate([
            'name' => 'sometimes|required|string|max:191',
        ]);

        $agency->update($data);

        return response()->json($agency);
    }

    public function destroy(Request $request, Agency $agency): JsonResponse
    {
        $this->authorize('delete', $agency);

        $agency->delete();

        return response()->json(['message' => 'deleted']);
    }

    protected function canAccess($user, Agency $agency): bool
    {
        if (method_exists($user, 'hasRole') && $user->hasRole('super-admin')) {
            return true;
        }

        return $user->tenant_id && $agency->tenant_id == $user->tenant_id;
    }
}
