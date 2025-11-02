<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\VisitLog;

class VisitorAdminController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', VisitLog::class);

    $query = VisitLog::with(['visitor', 'host'])->latest('check_in_at');

        // Filter by host
        if ($request->filled('host_id')) {
            $query->where('host_id', $request->query('host_id'));
        }

        // Date range filter (check_in_at)
        if ($request->filled('from') || $request->filled('to')) {
            $from = $request->filled('from') ? \Carbon\Carbon::parse($request->query('from'))->startOfDay() : null;
            $to = $request->filled('to') ? \Carbon\Carbon::parse($request->query('to'))->endOfDay() : null;

            if ($from && $to) {
                $query->whereBetween('check_in_at', [$from, $to]);
            } elseif ($from) {
                $query->where('check_in_at', '>=', $from);
            } elseif ($to) {
                $query->where('check_in_at', '<=', $to);
            }
        }

        // Search by visitor name/email/phone
        if ($request->filled('search')) {
            $search = $request->query('search');
            $query->whereHas('visitor', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $perPage = (int) $request->query('per_page', 25);

        $paginated = $query->paginate($perPage)->appends($request->query());

        // Enrich each item with convenient flattened fields and server-side formatted timestamps
        $paginated->getCollection()->transform(function ($item) {
            $item->host_name = $item->host ? ($item->host->name ?? null) : null;
            $item->host_email = $item->host ? ($item->host->email ?? null) : null;
            $item->host_phone = $item->host ? ($item->host->phone ?? null) : null;

            $item->visitor_name = $item->visitor ? ($item->visitor->name ?? null) : null;
            $item->visitor_email = $item->visitor ? ($item->visitor->email ?? null) : null;
            $item->visitor_phone = $item->visitor ? ($item->visitor->phone ?? null) : null;

            // Provide ISO 8601 timestamps for reliable client parsing
            if ($item->check_in_at instanceof \DateTimeInterface) {
                $item->check_in_at_iso = $item->check_in_at->toIso8601String();
            } elseif (is_string($item->check_in_at) && $item->check_in_at !== '') {
                $item->check_in_at_iso = \Carbon\Carbon::parse($item->check_in_at)->toIso8601String();
            } else {
                $item->check_in_at_iso = null;
            }

            if ($item->check_out_at instanceof \DateTimeInterface) {
                $item->check_out_at_iso = $item->check_out_at->toIso8601String();
            } elseif (is_string($item->check_out_at) && $item->check_out_at !== '') {
                $item->check_out_at_iso = \Carbon\Carbon::parse($item->check_out_at)->toIso8601String();
            } else {
                $item->check_out_at_iso = null;
            }

            return $item;
        });

        return response()->json($paginated);
    }
}
