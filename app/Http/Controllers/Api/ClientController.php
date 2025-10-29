<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Agency;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ClientController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if (method_exists($user, 'hasRole') && $user->hasRole('super-admin')) {
            $items = Client::with('agency')->paginate(20);
        } else {
            $items = Client::where('tenant_id', $user->tenant_id)->with('agency')->paginate(20);
        }

        return response()->json($items);
    }

    public function show(Request $request, Client $client): JsonResponse
    {
        $this->authorize('view', $client);

        return response()->json($client->load('agency', 'employees'));
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Client::class);

        $data = $request->validate([
            'name' => 'required|string|max:191',
            'email' => 'required|email|max:191',
            'agency_id' => 'nullable|exists:agencies,id',
        ]);

        $user = $request->user();
        $data['tenant_id'] = $user->tenant_id ?? null;

        // If agency_id provided, ensure agency belongs to same tenant
        if (! empty($data['agency_id'])) {
            $agency = Agency::find($data['agency_id']);
            if ($agency && $agency->tenant_id != $data['tenant_id']) {
                return response()->json(['message' => 'Agency does not belong to your tenant'], 422);
            }
        }

        $client = Client::create($data);

        return response()->json($client, 201);
    }

    public function update(Request $request, Client $client): JsonResponse
    {
        $this->authorize('update', $client);

        $data = $request->validate([
            'name' => 'sometimes|required|string|max:191',
            'email' => 'sometimes|required|email|max:191',
            'agency_id' => 'nullable|exists:agencies,id',
        ]);

        if (! empty($data['agency_id'])) {
            $agency = Agency::find($data['agency_id']);
            if ($agency && $agency->tenant_id != $client->tenant_id) {
                return response()->json(['message' => 'Agency does not belong to the same tenant'], 422);
            }
        }

        $client->update($data);

        return response()->json($client);
    }

    public function destroy(Request $request, Client $client): JsonResponse
    {
        $this->authorize('delete', $client);

        $client->delete();

        return response()->json(['message' => 'deleted']);
    }

    protected function canAccess($user, Client $client): bool
    {
        if (method_exists($user, 'hasRole') && $user->hasRole('super-admin')) {
            return true;
        }

        return $user->tenant_id && $client->tenant_id == $user->tenant_id;
    }
}
