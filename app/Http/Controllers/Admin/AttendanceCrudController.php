<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\AttendanceRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class AttendanceCrudController
 *
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class AttendanceCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     *
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(\App\Models\Attendance::class);
        CRUD::setRoute(config('backpack.base.route_prefix').'/attendance');
        CRUD::setEntityNameStrings('attendance', 'attendances');
    }

    /**
     * Define what happens when the List operation is loaded.
     */
    protected function setupListOperation()
    {
        // Basic columns for attendance list
        CRUD::column('id')->label('ID');
        CRUD::column('employee_id')->label('Employee ID');
        CRUD::column('check_in_at')->label('Check In')->type('datetime');
        CRUD::column('check_out_at')->label('Check Out')->type('datetime');
        CRUD::column('created_at')->label('Created At')->type('datetime');
    }

    /**
     * Define what happens when the Create operation is loaded.
     */
    protected function setupCreateOperation()
    {
        CRUD::setValidation(AttendanceRequest::class);
        
        // Use automatic field detection from database
        CRUD::setFromDb();
        
        // Remove fields that shouldn't be user-editable
        CRUD::removeField('id');
        CRUD::removeField('created_at');
        CRUD::removeField('updated_at');
        CRUD::removeField('tenant_uuid');

        // Add information about holidays and leaves
        $currentMonth = date('Y-m');
        $publicHolidays = $this->getPublicHolidaysForMonth($currentMonth);
        $employeeLeaves = $this->getEmployeeLeavesForMonth($currentMonth);

        if (!empty($publicHolidays) || !empty($employeeLeaves)) {
            $htmlContent = '<div class="alert alert-info mb-3"><strong>📅 Important Dates for ' . date('F Y') . '</strong><br>';
            
            if (!empty($publicHolidays)) {
                $htmlContent .= '<div class="mt-2"><strong>Public Holidays:</strong><ul class="mb-0">';
                foreach ($publicHolidays as $holiday) {
                    $htmlContent .= '<li>' . date('d M Y', strtotime($holiday['date'])) . ' - ' . $holiday['reason'] . '</li>';
                }
                $htmlContent .= '</ul></div>';
            }
            
            if (!empty($employeeLeaves)) {
                $htmlContent .= '<div class="mt-2"><strong>Employee Leaves:</strong><ul class="mb-0">';
                foreach ($employeeLeaves as $leave) {
                    $employee = \App\Models\Employee::find($leave['employee_id']);
                    $employeeName = $employee ? $employee->first_name . ' ' . $employee->last_name : 'Unknown';
                    $htmlContent .= '<li>' . $employeeName . ' - ' . date('d M', strtotime($leave['start_date'])) . ' to ' . date('d M', strtotime($leave['end_date'])) . ' (' . ucfirst($leave['leave_type']) . ' - ' . ucfirst($leave['status']) . ')</li>';
                }
                $htmlContent .= '</ul></div>';
            }
            
            $htmlContent .= '</div>';

            CRUD::addField([
                'name' => 'holidays_info',
                'type' => 'custom_html',
                'value' => $htmlContent,
            ]);
        }
    }

    /**
     * Define what happens when the Update operation is loaded.
     */
    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }

    /**
     * Get public holidays for a given month
     */
    private function getPublicHolidaysForMonth($month)
    {
        $startDate = \Carbon\Carbon::parse($month . '-01')->startOfMonth();
        $endDate = \Carbon\Carbon::parse($month . '-01')->endOfMonth();

        return \App\Models\Leave::where('leave_type', 'public_holiday')
            ->where('status', 'approved')
            ->where(function($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate, $endDate])
                      ->orWhereBetween('end_date', [$startDate, $endDate]);
            })
            ->get(['start_date', 'end_date', 'reason'])
            ->flatMap(function($holiday) {
                $dates = [];
                $current = \Carbon\Carbon::parse($holiday->start_date);
                $end = \Carbon\Carbon::parse($holiday->end_date);
                
                while ($current <= $end) {
                    $dates[] = [
                        'date' => $current->format('Y-m-d'),
                        'reason' => $holiday->reason,
                    ];
                    $current->addDay();
                }
                return $dates;
            })
            ->unique('date')
            ->values()
            ->toArray();
    }

    /**
     * Get employee leaves for a given month
     */
    private function getEmployeeLeavesForMonth($month)
    {
        $startDate = \Carbon\Carbon::parse($month . '-01')->startOfMonth();
        $endDate = \Carbon\Carbon::parse($month . '-01')->endOfMonth();

        return \App\Models\Leave::whereIn('status', ['approved', 'pending'])
            ->where('leave_type', '!=', 'public_holiday')
            ->where(function($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate, $endDate])
                      ->orWhereBetween('end_date', [$startDate, $endDate]);
            })
            ->get(['employee_id', 'start_date', 'end_date', 'leave_type', 'status'])
            ->toArray();
    }
}
