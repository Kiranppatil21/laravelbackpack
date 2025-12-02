<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Visitor Management Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration options for the visitor management system including
    | approval workflows, security settings, and integration options.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Auto-Approval Rules
    |--------------------------------------------------------------------------
    |
    | Define which criteria must be met for automatic visitor approval.
    | Set to true to require the criteria for auto-approval.
    |
    */
    'auto_approval_rules' => [
        'has_valid_invitation' => false,
        'id_verified' => true,
        'health_screening_passed' => true,
        'not_on_watchlist' => true,
        'background_check_passed' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Approval Requirements
    |--------------------------------------------------------------------------
    |
    | Global settings for what is required for visitor approval.
    |
    */
    'require_id_verification' => env('VISITOR_REQUIRE_ID_VERIFICATION', true),
    'require_health_screening' => env('VISITOR_REQUIRE_HEALTH_SCREENING', true),
    'require_manual_approval_first_time' => env('VISITOR_REQUIRE_MANUAL_APPROVAL_FIRST_TIME', false),
    'require_photo' => env('VISITOR_REQUIRE_PHOTO', true),

    /*
    |--------------------------------------------------------------------------
    | Health Screening Configuration
    |--------------------------------------------------------------------------
    |
    | Settings for health screening requirements.
    |
    */
    'health_screening' => [
        'max_temperature' => env('VISITOR_MAX_TEMPERATURE', 37.5),
        'required_questions' => [
            'symptoms' => 'Do you have any COVID-19 symptoms?',
            'contact' => 'Have you been in contact with anyone with COVID-19?',
            'travel' => 'Have you traveled internationally in the last 14 days?',
        ],
        'enabled' => env('VISITOR_HEALTH_SCREENING_ENABLED', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Visit Duration Settings
    |--------------------------------------------------------------------------
    |
    | Default settings for visit durations and overstay alerts.
    |
    */
    'visit_duration' => [
        'default_duration_hours' => 4,
        'max_duration_hours' => 12,
        'overstay_alert_after_minutes' => 30,
        'auto_checkout_after_hours' => 24,
    ],

    /*
    |--------------------------------------------------------------------------
    | Photo and Document Storage
    |--------------------------------------------------------------------------
    |
    | Configuration for storing visitor photos and documents.
    |
    */
    'storage' => [
        'photos_disk' => env('VISITOR_PHOTOS_DISK', 'public'),
        'photos_path' => 'visitor-photos',
        'documents_disk' => env('VISITOR_DOCUMENTS_DISK', 'local'),
        'documents_path' => 'visitor-documents',
        'max_photo_size_mb' => 5,
        'allowed_photo_types' => ['jpg', 'jpeg', 'png'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Settings
    |--------------------------------------------------------------------------
    |
    | Security-related configuration options.
    |
    */
    'security' => [
        'auto_deny_watchlist' => env('VISITOR_AUTO_DENY_WATCHLIST', false),
        'alert_on_watchlist_entry' => env('VISITOR_ALERT_ON_WATCHLIST_ENTRY', true),
        'require_escort_for_high_risk' => env('VISITOR_REQUIRE_ESCORT_HIGH_RISK', true),
        'block_multiple_active_visits' => env('VISITOR_BLOCK_MULTIPLE_VISITS', false),
        'enable_facial_recognition' => env('VISITOR_ENABLE_FACIAL_RECOGNITION', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Notification Settings
    |--------------------------------------------------------------------------
    |
    | Configure when and how notifications are sent.
    |
    */
    'notifications' => [
        'notify_host_on_checkin' => true,
        'notify_host_on_overstay' => true,
        'notify_security_on_watchlist' => true,
        'notify_admin_on_approval_needed' => true,
        'email_invitations' => env('VISITOR_EMAIL_INVITATIONS', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Data Retention and Compliance
    |--------------------------------------------------------------------------
    |
    | Settings for data retention and compliance requirements.
    |
    */
    'compliance' => [
        'data_retention_years' => env('VISITOR_DATA_RETENTION_YEARS', 2),
        'auto_delete_photos' => env('VISITOR_AUTO_DELETE_PHOTOS', true),
        'gdpr_compliance' => env('VISITOR_GDPR_COMPLIANCE', false),
        'audit_trail_enabled' => env('VISITOR_AUDIT_TRAIL', true),
        'anonymize_after_years' => env('VISITOR_ANONYMIZE_AFTER_YEARS', 5),
    ],

    /*
    |--------------------------------------------------------------------------
    | IoT Device Integration
    |--------------------------------------------------------------------------
    |
    | Configuration for IoT device integration.
    |
    */
    'iot' => [
        'enabled' => env('VISITOR_IOT_ENABLED', false),
        'device_heartbeat_timeout_minutes' => 5,
        'auto_register_devices' => env('VISITOR_AUTO_REGISTER_DEVICES', false),
        'require_device_authentication' => env('VISITOR_REQUIRE_DEVICE_AUTH', true),
        'supported_device_types' => [
            'kiosk',
            'rfid_reader',
            'biometric_scanner',
            'thermal_camera',
            'qr_scanner',
            'tablet',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | API Rate Limiting
    |--------------------------------------------------------------------------
    |
    | Rate limiting for visitor API endpoints.
    |
    */
    'rate_limiting' => [
        'checkin_per_minute' => env('VISITOR_CHECKIN_RATE_LIMIT', 60),
        'invitation_per_hour' => env('VISITOR_INVITATION_RATE_LIMIT', 100),
        'api_requests_per_minute' => env('VISITOR_API_RATE_LIMIT', 120),
    ],

    /*
    |--------------------------------------------------------------------------
    | Dashboard Configuration
    |--------------------------------------------------------------------------
    |
    | Settings for the visitor management dashboard.
    |
    */
    'dashboard' => [
        'refresh_interval_seconds' => 30,
        'show_photos_in_dashboard' => true,
        'max_current_visitors_display' => 100,
        'alert_severity_colors' => [
            'low' => '#10B981',
            'medium' => '#F59E0B',
            'high' => '#EF4444',
            'critical' => '#DC2626',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | QR Code Configuration
    |--------------------------------------------------------------------------
    |
    | Settings for QR code generation and validation.
    |
    */
    'qr_codes' => [
        'enabled' => env('VISITOR_QR_CODES_ENABLED', true),
        'expiry_hours' => env('VISITOR_QR_CODE_EXPIRY_HOURS', 24),
        'include_visitor_info' => env('VISITOR_QR_INCLUDE_INFO', false),
        'size' => 200, // pixels
        'error_correction' => 'M', // L, M, Q, H
    ],

];