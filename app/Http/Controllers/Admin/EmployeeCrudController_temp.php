
    /**
     * Toggle employee status (active/inactive)
     */
    public function toggleStatus($id)
    {
        $employee = \App\Models\Employee::withoutGlobalScope(\App\Models\Scopes\TenantScope::class)
            ->findOrFail($id);

        $newStatus = $employee->status === 'active' ? 'inactive' : 'active';
        $employee->status = $newStatus;
        $employee->save();

        return response()->json([
            'success' => true,
            'status' => $newStatus,
            'message' => 'Employee status updated to ' . $newStatus
        ]);
    }
