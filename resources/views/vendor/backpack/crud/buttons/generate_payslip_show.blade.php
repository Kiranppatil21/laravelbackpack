@php
    // `$entry` is provided by Backpack show views. Render a button that links
    // to the admin route which will generate a payslip from the payroll record.
    $url = route('admin.payroll.generate', [$entry->getKey()]);
@endphp

<a href="{{ $url }}" class="btn btn-primary mr-2" title="Generate Payslip">
    <i class="la la-file-pdf-o"></i>
    Generate Payslip
</a>