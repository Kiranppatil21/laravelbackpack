<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Visitor;
use App\Models\VisitLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Notifications\VisitorCheckedIn;

class VisitorController extends Controller
{
    /**
     * Public kiosk / IoT check-in endpoint.
     */
    public function checkIn(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'company' => 'nullable|string|max:255',
            'host_id' => 'nullable|integer',
            'source' => 'nullable|string|max:50',
            'external_id' => 'nullable|string|max:255',
        ]);

        // If an email is present try to find existing visitor; otherwise create new
        $visitor = null;
        if (! empty($data['email'])) {
            $visitor = Visitor::firstWhere('email', $data['email']);
        }

        if (! $visitor) {
            $visitor = Visitor::create([
                'name' => $data['name'],
                'email' => $data['email'] ?? null,
                'phone' => $data['phone'] ?? null,
                'company' => $data['company'] ?? null,
                'host_id' => $data['host_id'] ?? null,
                'source' => $data['source'] ?? null,
            ]);
        }

        $visit = VisitLog::create([
            'visitor_id' => $visitor->id,
            'host_id' => $data['host_id'] ?? null,
            'check_in_at' => now(),
            'source' => $data['source'] ?? 'kiosk',
            'external_id' => $data['external_id'] ?? null,
        ]);

        // Notify host user if present (assumes host_id references users.id)
        if (! empty($visit->host_id)) {
            $host = User::find($visit->host_id);
            if ($host) {
                try {
                    $host->notify(new VisitorCheckedIn($visitor, $visit));
                } catch (\Throwable $e) {
                    // don't fail check-in if notification delivery fails in tests/environments
                }
            }
        }

        return response()->json(['visitor' => $visitor, 'visit' => $visit], 201);
    }

    /**
     * Checkout endpoint. Accepts a visit log id in route param.
     */
    public function checkOut(Request $request, VisitLog $visit): JsonResponse
    {
        // Allow either host or authorized user to checkout via policy check
        if (Auth::check()) {
            $this->authorize('update', $visit);
        }

        if ($visit->check_out_at) {
            return response()->json(['message' => 'Already checked out'], 200);
        }

        $visit->check_out_at = now();
        $visit->save();

        return response()->json(['visit' => $visit]);
    }

    /**
     * List visit logs (paginated). Authorization via policy.
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', VisitLog::class);

        $query = VisitLog::with('visitor')->latest('check_in_at');

        // Optional filters
        if ($request->filled('host_id')) {
            $query->where('host_id', $request->query('host_id'));
        }

        $perPage = (int) $request->query('per_page', 25);

        $page = $query->paginate($perPage);

        return response()->json($page);
    }
}

