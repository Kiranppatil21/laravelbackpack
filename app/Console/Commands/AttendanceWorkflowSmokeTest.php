<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Client;
use App\Models\EmployeeAttendanceMaster;
use App\Models\EmployeeAttendanceAudit;
use App\Http\Controllers\Admin\BulkAttendanceController;
use Spatie\Permission\Models\Role;

class AttendanceWorkflowSmokeTest extends Command
{
    protected $signature = 'attendance:workflow-smoke {tenant?} {--month=}';
    protected $description = 'Run submit→approve→lock workflow smoke test and report audit entries.';

    public function handle(): int
    {
        $tenantId = $this->argument('tenant');
        $month = $this->option('month') ?: now()->format('Y-m');

        $tenant = $tenantId ? Tenant::where('uuid', $tenantId)->first() : Tenant::first();
        if (! $tenant) {
            $this->error('No tenant found. Create a tenant first.');
            return 1;
        }

        // Initialize tenancy context
        tenancy()->initialize($tenant);
        $this->info('Initialized tenancy for tenant uuid: ' . $tenant->uuid);

        // Ensure Super Admin role exists
        $role = Role::firstOrCreate(['name' => 'Super Admin']);

        // Ensure a user exists and has role
        $user = User::first();
        if (! $user) {
            $user = User::create([
                'name' => 'Smoke Admin',
                'email' => 'smoke-admin@example.com',
                'password' => bcrypt('secret')
            ]);
            $this->info('Created user id ' . $user->id);
        }
        if (! $user->hasRole($role->name)) {
            $user->assignRole($role);
            $this->info('Assigned role Super Admin to user id ' . $user->id);
        }
        Auth::login($user);

        // Ensure a site/client exists
        $site = Client::first();
        if (! $site) {
            $site = Client::create([
                'name' => 'Smoke Test Site',
                'email' => 'site@example.com'
            ]);
            $this->info('Created site id ' . $site->id);
        }

        // Create or fetch master record
        $master = EmployeeAttendanceMaster::where('site_id', $site->id)
            ->where('month', $month)
            ->where('user_type', 'Security Guard')
            ->first();
        if (! $master) {
            $master = EmployeeAttendanceMaster::create([
                'tenant_id' => 1,
                'site_id' => $site->id,
                'month' => $month,
                'user_type' => 'Security Guard',
                'created_by' => $user->id,
                'status' => 'draft'
            ]);
            $this->info('Created master id ' . $master->id . ' status ' . $master->status);
        } else {
            $this->info('Using existing master id ' . $master->id . ' status ' . $master->status);
        }

        $controller = app()->make(BulkAttendanceController::class);
        $emptyRequest = new Request();

        // Submit
        $submitResp = $controller->submit($master->id, $emptyRequest);
        $master->refresh();
        $this->line('After submit status: ' . $master->status);

        // Approve
        $approveResp = $controller->approve($master->id, $emptyRequest);
        $master->refresh();
        $this->line('After approve status: ' . $master->status);

        // Lock
        $lockResp = $controller->lock($master->id, $emptyRequest);
        $master->refresh();
        $this->line('After lock status: ' . $master->status);

        // Attempt update after lock
        $storeRequest = new Request([
            'site_id' => $site->id,
            'user_type' => 'Security Guard',
            'month' => $month,
            'attendance' => []
        ]);
        $storeResponse = $controller->store($storeRequest);
        if (method_exists($storeResponse, 'getData')) {
            $data = $storeResponse->getData();
            $this->line('Store after lock response: ' . json_encode($data));
        } else {
            $this->line('Store after lock raw response: ' . get_class($storeResponse));
        }

        $auditCount = EmployeeAttendanceAudit::where('attendance_master_id', $master->id)->count();
        $this->info('Audit entries count: ' . $auditCount);

        $latestAudits = EmployeeAttendanceAudit::where('attendance_master_id', $master->id)
            ->orderByDesc('id')->take(5)->get(['id','action','changed_by']);
        foreach ($latestAudits as $audit) {
            $this->line('Audit #' . $audit->id . ' action=' . $audit->action . ' by user ' . $audit->changed_by);
        }

        $this->info('Smoke test complete.');
        return 0;
    }
}
