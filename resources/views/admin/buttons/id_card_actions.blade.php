@if ($crud->hasAccess('show'))
<div class="btn-group" role="group">
    <button type="button" class="btn btn-sm btn-info" onclick="previewIdCard({{ $entry->getKey() }})" title="Preview ID Card">
        <i class="la la-eye"></i> Preview
    </button>
    <a href="{{ route('admin.employee.generate-id-card', $entry->getKey()) }}" class="btn btn-sm btn-primary" title="Download ID Card" target="_blank">
        <i class="la la-download"></i> Download
    </a>
</div>

<script>
function previewIdCard(id) {
    // Open preview in modal
    const previewUrl = '{{ url(config('backpack.base.route_prefix', 'admin').'/employee') }}/' + id + '/preview-id-card';
    
    // Create modal
    const modalHtml = `
        <div class="modal fade" id="idCardPreviewModal" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Employee ID Card Preview</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body text-center">
                        <iframe src="${previewUrl}" style="width: 100%; height: 600px; border: none;"></iframe>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <a href="{{ url(config('backpack.base.route_prefix', 'admin').'/employee') }}/${id}/generate-id-card" class="btn btn-primary" target="_blank">
                            <i class="la la-download"></i> Download PDF
                        </a>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing modal if any
    $('#idCardPreviewModal').remove();
    
    // Add modal to body and show
    $('body').append(modalHtml);
    $('#idCardPreviewModal').modal('show');
    
    // Clean up when modal is closed
    $('#idCardPreviewModal').on('hidden.bs.modal', function () {
        $(this).remove();
    });
}
</script>
@endif
