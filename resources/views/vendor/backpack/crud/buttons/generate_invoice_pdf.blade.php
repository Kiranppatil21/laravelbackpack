{{-- Generate Invoice PDF button --}}
@if ($crud->hasAccess('show'))
    <a href="{{ route('admin.client-invoice.pdf', $entry->getKey()) }}" 
       class="btn btn-sm btn-info" 
       target="_blank" 
       title="Generate PDF">
        <i class="la la-file-pdf"></i> PDF
    </a>
@endif