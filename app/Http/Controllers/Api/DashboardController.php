<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();

    $allowed = ['Super Admin', 'Agency Owner', 'HR', 'Client', 'Guard/Employee', 'Visitor'];

        if (class_exists('\Spatie\Permission\Models\Role')) {
            if (! $user->hasAnyRole($allowed)) {
                return response()->json(['message' => 'Forbidden'], 403);
            }
        }

        return response()->json([
            'message' => 'Welcome to the API dashboard',
            'roles' => $user->getRoleNames(),
        ]);
    }
}
