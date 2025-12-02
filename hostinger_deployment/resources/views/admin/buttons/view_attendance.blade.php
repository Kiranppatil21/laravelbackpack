{{-- View Attendance Button for Employee Row --}}
<a class="btn btn-sm btn-outline-primary" href="{{ url($crud->route.'/attendance/'.$entry->getKey()) }}" title="View Attendance">
    <i class="las la-clock"></i> Attendance
</a>