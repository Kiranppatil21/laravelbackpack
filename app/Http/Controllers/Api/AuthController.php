<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $data['email'])->first();

        if (! $user) {
            return response()->json(['message' => 'The provided credentials are incorrect.'], 401);
        }

        if (! Hash::check($data['password'], $user->password)) {
            // Fallback for legacy accounts created while hashing bug existed
            if (! hash_equals($user->password, $data['password'])) {
                return response()->json(['message' => 'The provided credentials are incorrect.'], 401);
            }

            $user->forceFill(['password' => Hash::make($data['password'])])->save();
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json(['token' => $token, 'user' => $user], 200);
    }

    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        // Optionally assign default role (e.g., Visitor)
        if (class_exists('\Spatie\Permission\Models\Role')) {
            $user->assignRole('Visitor');
        }

        // Fire Registered event and send verification (if implemented)
        try {
            event(new Registered($user));
            // method_exists check uses runtime trait behavior; suppress phpstan here
            // @phpstan-ignore-next-line
            if (method_exists($user, 'sendEmailVerificationNotification')) {
                $user->sendEmailVerificationNotification();
            }
        } catch (\Throwable $e) {
            Log::warning('Failed to send verification email: '.$e->getMessage());
        }

        // Attempt tenant provisioning if tenancy helper is available.
        if (function_exists('tenancy') && method_exists(tenancy(), 'create')) {
            try {
                $tenantId = Str::slug($user->email.'-'.time());
                // Try both array and (id, data) signatures depending on package version
                try {
                    tenancy()->create(['id' => $tenantId, 'email' => $user->email, 'name' => $user->name]);
                } catch (\Throwable $inner) {
                    tenancy()->create($tenantId, ['email' => $user->email, 'name' => $user->name]);
                }
            } catch (\Throwable $e) {
                Log::warning('Tenant provisioning failed: '.$e->getMessage());
            }
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json(['token' => $token, 'user' => $user], 201);
    }

    public function user(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->load('roles');

        return response()->json($user);
    }

    public function logout(Request $request): JsonResponse
    {
        $token = $request->user()?->currentAccessToken();
        if ($token) {
            $token->delete();
        }

        return response()->json(['message' => 'Logged out']);
    }
}
