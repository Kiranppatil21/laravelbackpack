{{-- Bulk Assign Client button --}}
<button type="button" class="btn btn-primary" onclick="showBulkAssignModal()" data-toggle="tooltip" title="Bulk Assign Employees to Client">
    <i class="la la-users"></i> Bulk Assign to Client
</button>

@push('after_scripts')
<script>
function showBulkAssignModal() {
    // Create modal HTML
    var modalHtml = `
        <div class="modal fade" id="bulkAssignModal" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Bulk Assign Employees to Client</h5>
                        <button type="button" class="close" data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>
                    <form id="bulkAssignForm">
                        <div class="modal-body">
                            <div class="form-group">
                                <label for="selectedClient">Select Client:</label>
                                <select id="selectedClient" class="form-control" required>
                                    <option value="">Choose a client...</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Select Employees to Assign:</label>
                                <div id="employeeList" style="max-height: 300px; overflow-y: auto; border: 1px solid #ddd; padding: 10px;">
                                    <div class="text-center">Loading employees...</div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>
                                    <input type="checkbox" id="selectAllEmployees"> Select All Employees
                                </label>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">
                                <i class="la la-check"></i> Assign Selected Employees
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing modal if any
    $('#bulkAssignModal').remove();
    
    // Add new modal
    $('body').append(modalHtml);
    
    // Load clients and employees
    loadClientsAndEmployees();
    
    // Show modal
    $('#bulkAssignModal').modal('show');
}

function loadClientsAndEmployees() {
    // Load clients
    fetch('/admin/api/clients')
        .then(response => response.json())
        .then(clients => {
            var clientSelect = document.getElementById('selectedClient');
            clients.forEach(client => {
                var option = document.createElement('option');
                option.value = client.id;
                option.textContent = client.name;
                clientSelect.appendChild(option);
            });
        })
        .catch(error => console.error('Error loading clients:', error));
    
    // Load employees
    fetch('/admin/api/employees')
        .then(response => response.json())
        .then(employees => {
            var employeeList = document.getElementById('employeeList');
            var html = '';
            employees.forEach(employee => {
                var currentClient = employee.client ? employee.client.name : 'Not assigned';
                html += `
                    <div class="form-check mb-2">
                        <input class="form-check-input employee-checkbox" type="checkbox" value="${employee.id}" id="emp${employee.id}">
                        <label class="form-check-label" for="emp${employee.id}">
                            <strong>${employee.name}</strong><br>
                            <small class="text-muted">Current: ${currentClient}</small>
                        </label>
                    </div>
                `;
            });
            employeeList.innerHTML = html;
            
            // Add select all functionality
            document.getElementById('selectAllEmployees').addEventListener('change', function() {
                var checkboxes = document.querySelectorAll('.employee-checkbox');
                checkboxes.forEach(cb => cb.checked = this.checked);
            });
        })
        .catch(error => {
            document.getElementById('employeeList').innerHTML = 
                '<div class="alert alert-danger">Error loading employees</div>';
            console.error('Error loading employees:', error);
        });
}

// Handle form submission
$(document).on('submit', '#bulkAssignForm', function(e) {
    e.preventDefault();
    
    var clientId = document.getElementById('selectedClient').value;
    var selectedEmployees = [];
    
    document.querySelectorAll('.employee-checkbox:checked').forEach(cb => {
        selectedEmployees.push(cb.value);
    });
    
    if (!clientId) {
        alert('Please select a client');
        return;
    }
    
    if (selectedEmployees.length === 0) {
        alert('Please select at least one employee');
        return;
    }
    
    // Submit assignment
    fetch('/admin/employee/bulk-assign-client', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            client_id: clientId,
            employee_ids: selectedEmployees
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Successfully assigned ' + selectedEmployees.length + ' employees to client');
            $('#bulkAssignModal').modal('hide');
            location.reload(); // Refresh the page to show updated assignments
        } else {
            alert('Error: ' + (data.message || 'Assignment failed'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred during assignment');
    });
});
</script>
@endpush