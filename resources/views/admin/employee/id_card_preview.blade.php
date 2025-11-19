@extends(backpack_view('blank'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-0">Employee ID Card Preview</h2>
                    <p class="text-muted">{{ $employee->name }} - Employee #{{ $employee->id }}</p>
                </div>
                <div>
                    <a href="{{ url('admin/employee/'.$employee->id.'/generate-id-card') }}" 
                       class="btn btn-success" target="_blank">
                        <i class="la la-download"></i> Download PDF
                    </a>
                    <a href="{{ url('admin/employee') }}" class="btn btn-secondary">
                        <i class="la la-arrow-left"></i> Back to List
                    </a>
                </div>
            </div>

            <!-- ID Card Preview Container -->
            <div class="card shadow-lg">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="la la-id-card"></i> ID Card Design Preview</h5>
                </div>
                <div class="card-body d-flex justify-content-center align-items-center" style="min-height: 500px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    
                    <!-- ID Card -->
                    <div class="id-card-container">
                        <div class="id-card">
                            <!-- Front Side -->
                            <div class="card-front">
                                <!-- Header Section -->
                                <div class="card-header-section">
                                    <div class="company-logo">
                                        <div class="logo-circle">
                                            <i class="las la-shield-alt"></i>
                                        </div>
                                    </div>
                                    <div class="company-info">
                                        <h3 class="company-name">SecureServe</h3>
                                        <p class="company-subtitle">Security Services</p>
                                    </div>
                                </div>

                                <!-- Employee Photo Section -->
                                <div class="photo-section">
                                    <div class="photo-frame">
                                        @if($employee->photo_path)
                                            <img src="{{ Storage::url($employee->photo_path) }}" alt="Employee Photo">
                                        @else
                                            <div class="photo-placeholder">
                                                <i class="las la-user"></i>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <!-- Employee Info Section -->
                                <div class="employee-info-section">
                                    <h4 class="employee-name">{{ $employee->name ?? 'N/A' }}</h4>
                                    <p class="employee-id">ID: {{ str_pad($employee->id, 6, '0', STR_PAD_LEFT) }}</p>
                                    <p class="employee-designation">{{ $employee->designation ?? 'Security Officer' }}</p>
                                    
                                    <div class="contact-info">
                                        <div class="contact-item">
                                            <i class="las la-phone"></i>
                                            <span>{{ $employee->phone ?? 'N/A' }}</span>
                                        </div>
                                        <div class="contact-item">
                                            <i class="las la-building"></i>
                                            <span>{{ $employee->agency->name ?? 'SecureServe Agency' }}</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Security Features -->
                                <div class="security-section">
                                    <div class="barcode">
                                        <div class="barcode-lines">
                                            <span></span><span></span><span></span><span></span><span></span>
                                            <span></span><span></span><span></span><span></span><span></span>
                                        </div>
                                    </div>
                                    <p class="validity">Valid Until: {{ \Carbon\Carbon::now()->addYear()->format('M Y') }}</p>
                                </div>

                                <!-- Footer -->
                                <div class="card-footer-section">
                                    <p class="footer-text">Authorized Personnel Only</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Employee Details -->
            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">Employee Information</h6>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm">
                                <tr>
                                    <td><strong>Full Name:</strong></td>
                                    <td>{{ $employee->name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Employee ID:</strong></td>
                                    <td>{{ str_pad($employee->id, 6, '0', STR_PAD_LEFT) }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Designation:</strong></td>
                                    <td>{{ $employee->designation ?? 'Security Officer' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Phone:</strong></td>
                                    <td>{{ $employee->phone ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Email:</strong></td>
                                    <td>{{ $employee->email ?? 'N/A' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">Agency Information</h6>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm">
                                <tr>
                                    <td><strong>Agency:</strong></td>
                                    <td>{{ $employee->agency->name ?? 'SecureServe Agency' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Issue Date:</strong></td>
                                    <td>{{ \Carbon\Carbon::now()->format('d M Y') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Valid Until:</strong></td>
                                    <td>{{ \Carbon\Carbon::now()->addYear()->format('d M Y') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Status:</strong></td>
                                    <td><span class="badge badge-success">Active</span></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* ID Card Styles */
.id-card-container {
    perspective: 1000px;
    margin: 20px;
}

.id-card {
    width: 350px;
    height: 550px;
    position: relative;
    transform-style: preserve-3d;
    transition: transform 0.6s;
}

.card-front {
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
    border-radius: 15px;
    padding: 20px;
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.3);
    color: white;
    display: flex;
    flex-direction: column;
    position: relative;
    overflow: hidden;
}

.card-front::before {
    content: '';
    position: absolute;
    top: 0;
    right: 0;
    width: 100px;
    height: 100px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 0 15px 0 100px;
}

/* Header Section */
.card-header-section {
    display: flex;
    align-items: center;
    margin-bottom: 25px;
    position: relative;
    z-index: 2;
}

.company-logo .logo-circle {
    width: 50px;
    height: 50px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 15px;
    border: 2px solid rgba(255, 255, 255, 0.3);
}

.logo-circle i {
    font-size: 24px;
    color: white;
}

.company-info h3 {
    margin: 0;
    font-size: 18px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.company-subtitle {
    margin: 0;
    font-size: 12px;
    opacity: 0.9;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Photo Section */
.photo-section {
    text-align: center;
    margin-bottom: 20px;
}

.photo-frame {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    background: white;
    margin: 0 auto;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    border: 4px solid rgba(255, 255, 255, 0.3);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
}

.photo-frame img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.photo-placeholder {
    color: #ccc;
    font-size: 60px;
}

/* Employee Info Section */
.employee-info-section {
    text-align: center;
    margin-bottom: 25px;
    flex: 1;
}

.employee-name {
    font-size: 20px;
    font-weight: 700;
    margin: 0 0 8px 0;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.employee-id {
    font-size: 16px;
    font-weight: 600;
    margin: 0 0 5px 0;
    color: #ffd700;
}

.employee-designation {
    font-size: 14px;
    margin: 0 0 20px 0;
    opacity: 0.9;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.contact-info {
    margin-top: 15px;
}

.contact-item {
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 8px 0;
    font-size: 12px;
}

.contact-item i {
    margin-right: 8px;
    width: 16px;
    text-align: center;
    opacity: 0.8;
}

/* Security Section */
.security-section {
    text-align: center;
    margin-bottom: 15px;
}

.barcode {
    margin-bottom: 10px;
}

.barcode-lines {
    display: flex;
    justify-content: center;
    gap: 2px;
}

.barcode-lines span {
    height: 20px;
    background: white;
    display: block;
}

.barcode-lines span:nth-child(odd) {
    width: 2px;
}

.barcode-lines span:nth-child(even) {
    width: 4px;
}

.validity {
    font-size: 11px;
    margin: 0;
    opacity: 0.8;
}

/* Footer Section */
.card-footer-section {
    text-align: center;
    border-top: 1px solid rgba(255, 255, 255, 0.2);
    padding-top: 10px;
}

.footer-text {
    font-size: 10px;
    margin: 0;
    opacity: 0.7;
    text-transform: uppercase;
    letter-spacing: 1px;
}

/* Hover Effects */
.id-card:hover {
    transform: rotateY(5deg) rotateX(5deg);
}

/* Responsive Design */
@media (max-width: 768px) {
    .id-card {
        width: 300px;
        height: 470px;
    }
    
    .card-front {
        padding: 15px;
    }
    
    .photo-frame {
        width: 100px;
        height: 100px;
    }
    
    .employee-name {
        font-size: 18px;
    }
}
</style>
@endsection

@section('after_scripts')
<script>
// Add subtle animations
document.addEventListener('DOMContentLoaded', function() {
    const card = document.querySelector('.id-card');
    
    // Add floating animation
    setInterval(() => {
        card.style.transform = 'translateY(-5px)';
        setTimeout(() => {
            card.style.transform = 'translateY(0px)';
        }, 1000);
    }, 3000);
});
</script>
@endsection