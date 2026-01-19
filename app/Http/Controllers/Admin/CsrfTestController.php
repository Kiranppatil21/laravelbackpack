<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class CsrfTestController extends Controller
{
    public function index()
    {
        return view('admin.csrf_test');
    }
    
    public function testPost(Request $request)
    {
        return response()->json([
            'success' => true,
            'message' => 'CSRF test passed!',
            'token' => csrf_token(),
            'session_id' => session()->getId()
        ]);
    }
}