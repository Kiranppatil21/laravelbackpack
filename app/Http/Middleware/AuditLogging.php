<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class AuditLogging
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);
        $response = $next($request);
        $endTime = microtime(true);

        // Log sensitive operations
        if ($this->isSensitiveOperation($request)) {
            $this->logAuditEvent($request, $response, $endTime - $startTime);
        }

        return $response;
    }

    /**
     * Determine if this is a sensitive operation that should be audited.
     */
    private function isSensitiveOperation(Request $request): bool
    {
        // Admin routes
        if ($request->is('admin/*')) {
            return true;
        }

        // API routes that modify data
        if ($request->is('api/*') && in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            return true;
        }

        // Authentication routes
        if (in_array($request->route()?->getName(), [
            'login', 'logout', 'register', 'password.update', 'password.reset'
        ])) {
            return true;
        }

        // File uploads
        if ($request->hasFile('*')) {
            return true;
        }

        return false;
    }

    /**
     * Log audit event details.
     */
    private function logAuditEvent(Request $request, Response $response, float $duration): void
    {
        $user = function_exists('backpack_auth') && backpack_auth()->check()
            ? backpack_auth()->user()
            : (auth()->check() ? auth()->user() : null);

        $auditData = [
            'timestamp' => now(),
            'user_id' => $user?->id,
            'user_email' => $user?->email,
            'user_name' => $user?->name,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'route' => $request->route()?->getName(),
            'status_code' => $response->getStatusCode(),
            'duration_ms' => round($duration * 1000, 2),
            'request_size' => strlen($request->getContent()),
            'response_size' => strlen($response->getContent()),
        ];

        // Add request parameters for sensitive operations (excluding passwords)
        if (in_array($request->method(), ['POST', 'PUT', 'PATCH'])) {
            $params = $request->except(['password', 'password_confirmation', 'current_password']);
            if (!empty($params)) {
                $auditData['request_params'] = $params;
            }
        }

        // Add file upload information
        if ($request->hasFile('*')) {
            $files = [];
            foreach ($request->allFiles() as $key => $file) {
                if (is_array($file)) {
                    $files[$key] = count($file) . ' files';
                } else {
                    $files[$key] = $file->getClientOriginalName() . ' (' . $file->getSize() . ' bytes)';
                }
            }
            $auditData['uploaded_files'] = $files;
        }

        // Store in database
        try {
            AuditLog::create($auditData);
        } catch (\Exception $e) {
            // If database logging fails, still log to file
            Log::error('Failed to store audit log in database: ' . $e->getMessage());
        }

        // Also log to file for redundancy (fallback to default channel if 'audit' missing)
        try {
            Log::channel('audit')->info('Security Audit Event', $auditData);
        } catch (\Exception $e) {
            Log::info('Security Audit Event', $auditData);
        }
    }
}
