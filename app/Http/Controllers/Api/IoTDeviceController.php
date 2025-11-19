<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\VisitorDevice;
use App\Services\VisitorManagementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class IoTDeviceController extends Controller
{
    protected VisitorManagementService $visitorService;

    public function __construct(VisitorManagementService $visitorService)
    {
        $this->visitorService = $visitorService;
    }

    /**
     * List all registered IoT devices.
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', VisitorDevice::class);

        $query = VisitorDevice::with('managedBy')->latest('created_at');

        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        if ($request->filled('device_type')) {
            $query->where('device_type', $request->query('device_type'));
        }

        if ($request->filled('location')) {
            $query->byLocation($request->query('location'));
        }

        if ($request->filled('online_only')) {
            $query->online();
        }

        $perPage = min((int) $request->query('per_page', 25), 100);
        $devices = $query->paginate($perPage);

        // Transform the data to include online status
        $devices->getCollection()->transform(function ($device) {
            return [
                'id' => $device->id,
                'device_id' => $device->device_id,
                'device_name' => $device->device_name,
                'device_type' => $device->device_type,
                'location' => $device->location,
                'status' => $device->status,
                'is_online' => $device->isOnline(),
                'last_heartbeat' => $device->last_heartbeat,
                'capabilities' => $device->capabilities,
                'managed_by' => $device->managedBy ? [
                    'id' => $device->managedBy->id,
                    'name' => $device->managedBy->name,
                ] : null,
                'created_at' => $device->created_at,
            ];
        });

        return response()->json($devices);
    }

    /**
     * Register a new IoT device.
     */
    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', VisitorDevice::class);

        $data = $request->validate([
            'device_id' => 'required|string|unique:visitor_devices,device_id',
            'device_name' => 'required|string|max:255',
            'device_type' => 'required|in:kiosk,rfid_reader,biometric_scanner,thermal_camera,qr_scanner,tablet,other',
            'location' => 'required|string|max:255',
            'ip_address' => 'nullable|ip',
            'mac_address' => 'nullable|string|max:17',
            'capabilities' => 'nullable|array',
            'configuration' => 'nullable|array',
            'notes' => 'nullable|string|max:1000',
        ]);

        $device = VisitorDevice::create([
            ...$data,
            'managed_by' => Auth::id(),
            'status' => 'active',
        ]);

        return response()->json([
            'message' => 'Device registered successfully',
            'device' => $device->fresh(['managedBy']),
        ], 201);
    }

    /**
     * Get device details.
     */
    public function show(VisitorDevice $device): JsonResponse
    {
        $this->authorize('view', $device);

        return response()->json($device->load(['managedBy']));
    }

    /**
     * Update device configuration.
     */
    public function update(Request $request, VisitorDevice $device): JsonResponse
    {
        $this->authorize('update', $device);

        $data = $request->validate([
            'device_name' => 'sometimes|string|max:255',
            'device_type' => 'sometimes|in:kiosk,rfid_reader,biometric_scanner,thermal_camera,qr_scanner,tablet,other',
            'location' => 'sometimes|string|max:255',
            'ip_address' => 'sometimes|nullable|ip',
            'mac_address' => 'sometimes|nullable|string|max:17',
            'status' => 'sometimes|in:active,inactive,maintenance,error',
            'capabilities' => 'sometimes|nullable|array',
            'configuration' => 'sometimes|nullable|array',
            'notes' => 'sometimes|nullable|string|max:1000',
        ]);

        $device->update($data);

        return response()->json([
            'message' => 'Device updated successfully',
            'device' => $device->fresh(['managedBy']),
        ]);
    }

    /**
     * Delete/deregister a device.
     */
    public function destroy(VisitorDevice $device): JsonResponse
    {
        $this->authorize('delete', $device);

        $device->delete();

        return response()->json([
            'message' => 'Device deregistered successfully',
        ]);
    }

    /**
     * Device heartbeat endpoint for IoT devices.
     */
    public function heartbeat(Request $request): JsonResponse
    {
        $data = $request->validate([
            'device_id' => 'required|string|exists:visitor_devices,device_id',
            'status' => 'sometimes|in:active,inactive,maintenance,error',
            'metadata' => 'sometimes|array',
        ]);

        $device = VisitorDevice::where('device_id', $data['device_id'])->first();

        if (!$device) {
            return response()->json([
                'message' => 'Device not found',
            ], 404);
        }

        // Update heartbeat and status
        $updateData = ['last_heartbeat' => now()];
        
        if (isset($data['status'])) {
            $updateData['status'] = $data['status'];
        }

        if (isset($data['metadata'])) {
            $currentConfig = $device->configuration ?? [];
            $updateData['configuration'] = array_merge($currentConfig, ['last_metadata' => $data['metadata']]);
        }

        $device->update($updateData);

        return response()->json([
            'message' => 'Heartbeat received',
            'device_status' => $device->status,
            'next_heartbeat_in_seconds' => config('visitor.iot.device_heartbeat_timeout_minutes', 5) * 60,
        ]);
    }

    /**
     * Process visitor check-in from IoT device.
     */
    public function deviceCheckIn(Request $request): JsonResponse
    {
        $data = $request->validate([
            'device_id' => 'required|string|exists:visitor_devices,device_id',
            'visitor_name' => 'required|string|max:255',
            'visitor_email' => 'nullable|email|max:255',
            'visitor_phone' => 'nullable|string|max:50',
            'visitor_company' => 'nullable|string|max:255',
            'visitor_id_type' => 'nullable|string|max:100',
            'visitor_id_value' => 'nullable|string|max:100',
            'host_id' => 'nullable|integer|exists:users,id',
            'purpose' => 'nullable|string|max:500',
            'device_data' => 'nullable|array',
            'biometric_data' => 'nullable|array',
            'temperature' => 'nullable|numeric|between:30,50',
            'photo_base64' => 'nullable|string',
            'external_reference' => 'nullable|string|max:255',
        ]);

        // Verify device is active and online
        $device = VisitorDevice::where('device_id', $data['device_id'])->first();
        
        if (!$device->isActive()) {
            return response()->json([
                'success' => false,
                'message' => 'Device is not active',
            ], 400);
        }

        // Process photo if provided
        $photoFile = null;
        if (!empty($data['photo_base64'])) {
            $photoFile = $this->processBase64Photo($data['photo_base64']);
        }

        // Prepare visitor data
        $visitorData = [
            'name' => $data['visitor_name'],
            'email' => $data['visitor_email'] ?? null,
            'phone' => $data['visitor_phone'] ?? null,
            'company' => $data['visitor_company'] ?? null,
            'id_type' => $data['visitor_id_type'] ?? null,
            'id_value' => $data['visitor_id_value'] ?? null,
            'purpose' => $data['purpose'] ?? null,
            'temperature' => $data['temperature'] ?? null,
            'photo' => $photoFile,
            'source' => 'iot_device',
        ];

        $visitData = [
            'device_id' => $data['device_id'],
            'device_data' => $data['device_data'] ?? null,
            'host_id' => $data['host_id'] ?? null,
            'entry_method' => $device->device_type,
            'external_id' => $data['external_reference'] ?? null,
        ];

        // Process check-in through service
        $result = $this->visitorService->registerDeviceCheckIn(
            $data['device_id'],
            $visitorData,
            $data['device_data'] ?? []
        );

        return response()->json($result);
    }

    /**
     * Process visitor check-out from IoT device.
     */
    public function deviceCheckOut(Request $request): JsonResponse
    {
        $data = $request->validate([
            'device_id' => 'required|string|exists:visitor_devices,device_id',
            'visitor_qr_code' => 'sometimes|string',
            'visitor_id_value' => 'sometimes|string',
            'visit_log_id' => 'sometimes|integer|exists:visit_logs,id',
            'exit_photo_base64' => 'nullable|string',
            'device_data' => 'nullable|array',
        ]);

        // Find the visit to check out
        $visit = null;
        
        if (isset($data['visit_log_id'])) {
            $visit = \App\Models\VisitLog::find($data['visit_log_id']);
        } elseif (isset($data['visitor_qr_code'])) {
            $visitor = \App\Models\Visitor::where('qr_code', $data['visitor_qr_code'])->first();
            $visit = $visitor?->currentVisit;
        } elseif (isset($data['visitor_id_value'])) {
            $visitor = \App\Models\Visitor::where('id_value', $data['visitor_id_value'])->first();
            $visit = $visitor?->currentVisit;
        }

        if (!$visit || $visit->isCompleted()) {
            return response()->json([
                'success' => false,
                'message' => 'No active visit found for checkout',
            ], 404);
        }

        // Process exit photo if provided
        $exitPhotoFile = null;
        if (!empty($data['exit_photo_base64'])) {
            $exitPhotoFile = $this->processBase64Photo($data['exit_photo_base64'], 'exit');
        }

        $checkoutData = [
            'exit_photo' => $exitPhotoFile,
            'checkout_reason' => 'device_checkout',
        ];

        $result = $this->visitorService->processCheckOut($visit, $checkoutData);

        return response()->json($result);
    }

    /**
     * Get device statistics and monitoring data.
     */
    public function deviceStats(Request $request): JsonResponse
    {
        $this->authorize('viewAny', VisitorDevice::class);

        $stats = [
            'total_devices' => VisitorDevice::count(),
            'active_devices' => VisitorDevice::active()->count(),
            'online_devices' => VisitorDevice::online()->count(),
            'devices_by_type' => VisitorDevice::selectRaw('device_type, COUNT(*) as count')
                ->groupBy('device_type')
                ->get(),
            'devices_by_status' => VisitorDevice::selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->get(),
            'recent_activity' => VisitorDevice::with('managedBy')
                ->orderBy('last_heartbeat', 'desc')
                ->limit(10)
                ->get()
                ->map(function ($device) {
                    return [
                        'device_name' => $device->device_name,
                        'device_type' => $device->device_type,
                        'location' => $device->location,
                        'last_heartbeat' => $device->last_heartbeat,
                        'is_online' => $device->isOnline(),
                    ];
                }),
        ];

        return response()->json($stats);
    }

    /**
     * Batch update device configurations.
     */
    public function batchUpdate(Request $request): JsonResponse
    {
        $this->authorize('update', VisitorDevice::class);

        $data = $request->validate([
            'device_ids' => 'required|array',
            'device_ids.*' => 'exists:visitor_devices,device_id',
            'updates' => 'required|array',
            'updates.status' => 'sometimes|in:active,inactive,maintenance,error',
            'updates.configuration' => 'sometimes|array',
        ]);

        $devices = VisitorDevice::whereIn('device_id', $data['device_ids'])->get();
        $updatedCount = 0;

        foreach ($devices as $device) {
            $updateData = [];
            
            if (isset($data['updates']['status'])) {
                $updateData['status'] = $data['updates']['status'];
            }
            
            if (isset($data['updates']['configuration'])) {
                $currentConfig = $device->configuration ?? [];
                $updateData['configuration'] = array_merge($currentConfig, $data['updates']['configuration']);
            }

            if (!empty($updateData)) {
                $device->update($updateData);
                $updatedCount++;
            }
        }

        return response()->json([
            'message' => 'Batch update completed',
            'updated_devices' => $updatedCount,
        ]);
    }

    /**
     * Process base64 encoded photo from IoT device.
     */
    protected function processBase64Photo(string $base64Data, string $type = 'entry'): ?\Illuminate\Http\UploadedFile
    {
        try {
            // Remove data URL prefix if present
            if (strpos($base64Data, 'data:image') === 0) {
                $base64Data = explode(',', $base64Data)[1];
            }

            $imageData = base64_decode($base64Data);
            
            if (!$imageData) {
                return null;
            }

            // Create temporary file
            $tempFile = tmpfile();
            fwrite($tempFile, $imageData);
            $tempPath = stream_get_meta_data($tempFile)['uri'];

            // Create UploadedFile instance
            $uploadedFile = new \Illuminate\Http\UploadedFile(
                $tempPath,
                "device_photo_{$type}_" . time() . '.jpg',
                'image/jpeg',
                null,
                true
            );

            return $uploadedFile;

        } catch (\Exception $e) {
            // Log error but don't fail the operation
            \Illuminate\Support\Facades\Log::warning('Failed to process device photo', [
                'error' => $e->getMessage(),
            ]);
            
            return null;
        }
    }
}