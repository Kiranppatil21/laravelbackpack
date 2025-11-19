{{-- Generate ID Card button with instant preview --}}
@if ($crud->hasAccess('show'))
    <a href="javascript:void(0)" 
       onclick="showIdCardModal({{ $entry->getKey() }})" 
       class="btn btn-sm btn-outline-info" 
       data-toggle="tooltip" 
       title="Preview & Generate ID Card">
        <i class="la la-id-card"></i> ID Card
    </a>
@endif

@push('after_scripts')
<script>
// Ensure script loads
document.addEventListener('DOMContentLoaded', function() {
    console.log('ID Card script ready at: ' + new Date().toLocaleTimeString());
});

function showIdCardModal(employeeId) {
    console.log('showIdCardModal called for employee ID:', employeeId);
    
    // Remove any existing modal
    if (document.getElementById('idCardModal')) {
        document.getElementById('idCardModal').remove();
    }
    
    // Create modal HTML
    var modalHtml = `
        <div class="modal fade" id="idCardModal" tabindex="-1" role="dialog" aria-labelledby="idCardModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="idCardModalLabel">ID Card Preview</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body text-center">
                        <div class="d-flex justify-content-center align-items-center" style="height: 200px;">
                            <div class="spinner-border text-primary" role="status">
                                <span class="sr-only">Loading...</span>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <a href="/admin/employee/${employeeId}/generate-id-card" class="btn btn-success" target="_blank">
                            <i class="la la-download"></i> Download PDF
                        </a>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Add modal to body
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    
    // Show modal using Bootstrap (try multiple methods)
    try {
        // Method 1: Bootstrap 5
        var modal = new bootstrap.Modal(document.getElementById('idCardModal'));
        modal.show();
        console.log('Modal shown using Bootstrap 5');
    } catch (e) {
        try {
            // Method 2: jQuery Bootstrap 4
            $('#idCardModal').modal('show');
            console.log('Modal shown using jQuery Bootstrap 4');
        } catch (e2) {
            // Method 3: Fallback - just make it visible
            var modalEl = document.getElementById('idCardModal');
            modalEl.style.display = 'block';
            modalEl.classList.add('show');
            document.body.classList.add('modal-open');
            console.log('Modal shown using fallback method');
        }
    }
    
    // Load employee data
    fetch(`/admin/employee/${employeeId}/preview-data`)
        .then(response => {
            console.log('Response status:', response.status);
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(employee => {
            console.log('Employee data received:', employee);
            var cardHtml = generateIdCard(employee);
            document.getElementById('idCardModal').querySelector('.modal-body').innerHTML = cardHtml;
        })
        .catch(error => {
            console.error('Error loading employee data:', error);
            document.getElementById('idCardModal').querySelector('.modal-body').innerHTML = 
                '<div class="alert alert-danger">Error loading preview: ' + error.message + '</div>';
        });
}

function generateIdCard(employee) {
    return `
        <div style="display: flex; justify-content: center; margin: 20px;">
            <div style="
                width: 300px;
                height: 450px;
                background: linear-gradient(135deg, #1e40af, #3b82f6);
                border-radius: 10px;
                padding: 20px;
                color: white;
                box-shadow: 0 8px 25px rgba(0,0,0,0.2);
                position: relative;
                font-family: Arial, sans-serif;
            ">
                <!-- Header -->
                <div style="text-align: center; border-bottom: 2px solid rgba(255,255,255,0.3); padding-bottom: 15px; margin-bottom: 20px;">
                    <div style="font-size: 18px; font-weight: bold; margin: 0; text-transform: uppercase; letter-spacing: 1px;">SecureServe</div>
                    <div style="font-size: 12px; margin: 5px 0 0 0; opacity: 0.8;">Security Services</div>
                </div>
                
                <!-- Photo -->
                <div style="width: 100px; height: 100px; border-radius: 50%; margin: 0 auto 20px; background: rgba(255,255,255,0.1); display: flex; align-items: center; justify-content: center; font-size: 48px; border: 3px solid rgba(255,255,255,0.3);">
                    👤
                </div>
                
                <!-- Employee Info -->
                <div style="text-align: center;">
                    <div style="font-size: 16px; font-weight: bold; margin: 0 0 10px 0; text-transform: uppercase;">${employee.name || 'N/A'}</div>
                    <div style="font-size: 12px; line-height: 1.6; margin-bottom: 15px;">
                        <div style="margin: 5px 0;"><strong>ID:</strong> ${String(employee.id).padStart(6, '0')}</div>
                        <div style="margin: 5px 0;"><strong>Position:</strong> ${employee.designation || 'Security Officer'}</div>
                        <div style="margin: 5px 0;"><strong>Phone:</strong> ${employee.phone || 'N/A'}</div>
                        <div style="margin: 5px 0;"><strong>Agency:</strong> ${employee.agency ? employee.agency.name : 'SecureServe'}</div>
                    </div>
                    
                    <!-- Emergency Contact -->
                    <div style="background: rgba(255,255,255,0.1); padding: 10px; border-radius: 5px; margin: 15px 0;">
                        <div style="font-size: 11px; font-weight: bold; margin-bottom: 5px; text-transform: uppercase;">Emergency Contact</div>
                        <div style="font-size: 11px;">📞 +91-911-SECURE</div>
                        <div style="font-size: 11px;">📧 emergency@secureserve.com</div>
                    </div>
                    
                    <!-- Validity -->
                    <div style="font-size: 10px; margin-top: 10px;">
                        <strong>Issued:</strong> ${new Date().toLocaleDateString()}<br>
                        <strong>Valid Until:</strong> ${new Date(Date.now() + 365*24*60*60*1000).toLocaleDateString()}
                    </div>
                </div>
                
                <!-- Footer -->
                <div style="position: absolute; bottom: 20px; left: 20px; right: 20px; text-align: center; font-size: 10px; opacity: 0.7; border-top: 1px solid rgba(255,255,255,0.3); padding-top: 10px;">
                    Authorized Personnel Only
                </div>
            </div>
        </div>
    `;
}
</script>
@endpush