{{-- Bulk Export Button --}}
<div class="btn-group">
    <button type="button" class="btn btn-sm btn-outline-info dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        <i class="las la-download"></i> Export
    </button>
    <div class="dropdown-menu">
        <a class="dropdown-item" href="#" onclick="exportData('csv')">
            <i class="las la-file-csv"></i> Export to CSV
        </a>
        <a class="dropdown-item" href="#" onclick="exportData('excel')">
            <i class="las la-file-excel"></i> Export to Excel
        </a>
        <a class="dropdown-item" href="#" onclick="exportData('pdf')">
            <i class="las la-file-pdf"></i> Export to PDF
        </a>
    </div>
</div>

<script>
function exportData(format) {
    // Get current filters and search parameters
    var params = new URLSearchParams(window.location.search);
    params.append('export', format);
    
    // Create download link
    var url = window.location.pathname + '/export?' + params.toString();
    window.open(url, '_blank');
}
</script>