@php
    $notificationService = app(\App\Services\AdminNotificationService::class);
    $notifications = $notificationService->getUnreadNotifications(backpack_user()->id, 5);
    $unreadCount = $notificationService->getUnreadCount(backpack_user()->id);
@endphp

<li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle" href="#" id="notificationsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="las la-bell" style="font-size: 1.2rem;"></i>
        @if($unreadCount > 0)
        <span class="badge badge-danger badge-pill notification-badge">{{ $unreadCount > 99 ? '99+' : $unreadCount }}</span>
        @endif
    </a>
    
    <div class="dropdown-menu dropdown-menu-end notification-dropdown" aria-labelledby="notificationsDropdown">
        <div class="dropdown-header d-flex justify-content-between align-items-center">
            <span class="font-weight-bold">Notifications</span>
            @if($unreadCount > 0)
            <a href="#" class="text-primary small" onclick="markAllAsRead()">Mark all as read</a>
            @endif
        </div>
        
        <div class="dropdown-divider"></div>
        
        @if($notifications->count() > 0)
            @foreach($notifications as $notification)
            <a class="dropdown-item notification-item" href="#" data-notification-id="{{ $notification->id }}" onclick="markAsRead({{ $notification->id }})">
                <div class="d-flex align-items-center">
                    <div class="notification-icon me-3">
                        @switch($notification->type)
                            @case('success')
                                <i class="las la-check-circle text-success"></i>
                                @break
                            @case('warning')
                                <i class="las la-exclamation-triangle text-warning"></i>
                                @break
                            @case('danger')
                                <i class="las la-exclamation-circle text-danger"></i>
                                @break
                            @default
                                <i class="las la-info-circle text-info"></i>
                        @endswitch
                    </div>
                    <div class="notification-content flex-grow-1">
                        <div class="notification-title font-weight-bold">{{ $notification->title }}</div>
                        <div class="notification-message small text-muted">{{ Str::limit($notification->message, 60) }}</div>
                        <div class="notification-time small text-muted">{{ \Carbon\Carbon::parse($notification->created_at)->diffForHumans() }}</div>
                    </div>
                </div>
            </a>
            @endforeach
            
            <div class="dropdown-divider"></div>
            <a class="dropdown-item text-center text-primary" href="{{ backpack_url('notifications') }}">
                View All Notifications
            </a>
        @else
            <div class="dropdown-item-text text-center text-muted py-3">
                <i class="las la-bell-slash"></i>
                <div>No new notifications</div>
            </div>
        @endif
    </div>
</li>

<style>
.notification-badge {
    position: absolute;
    top: -5px;
    right: -5px;
    font-size: 0.7rem;
    padding: 2px 5px;
}

.notification-dropdown {
    width: 350px;
    max-height: 400px;
    overflow-y: auto;
}

.notification-item {
    white-space: normal !important;
    padding: 10px 15px;
    border-bottom: 1px solid #f1f1f1;
}

.notification-item:hover {
    background-color: #f8f9fa;
}

.notification-item:last-child {
    border-bottom: none;
}

.notification-icon {
    font-size: 1.2rem;
}

.notification-content {
    line-height: 1.3;
}

.notification-title {
    font-size: 0.9rem;
    margin-bottom: 2px;
}

.notification-message {
    font-size: 0.8rem;
    margin-bottom: 2px;
}

.notification-time {
    font-size: 0.75rem;
}

@media (max-width: 768px) {
    .notification-dropdown {
        width: 280px;
    }
}
</style>

<script>
function markAsRead(notificationId) {
    fetch('{{ backpack_url("notifications") }}/' + notificationId + '/read', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    }).then(response => {
        if (response.ok) {
            // Update UI to show notification as read
            document.querySelector(`[data-notification-id="${notificationId}"]`).style.opacity = '0.6';
            // Update badge count
            updateNotificationBadge();
        }
    }).catch(error => {
        console.error('Error marking notification as read:', error);
    });
}

function markAllAsRead() {
    fetch('{{ backpack_url("notifications") }}/mark-all-read', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    }).then(response => {
        if (response.ok) {
            // Reload page to update UI
            window.location.reload();
        }
    }).catch(error => {
        console.error('Error marking all notifications as read:', error);
    });
}

function updateNotificationBadge() {
    fetch('{{ backpack_url("notifications") }}/count', {
        headers: {
            'Accept': 'application/json'
        }
    }).then(response => response.json()).then(data => {
        const badge = document.querySelector('.notification-badge');
        if (data.count > 0) {
            if (badge) {
                badge.textContent = data.count > 99 ? '99+' : data.count;
            }
        } else {
            if (badge) {
                badge.remove();
            }
        }
    }).catch(error => {
        console.error('Error updating notification count:', error);
    });
}

// Auto-refresh notifications every 30 seconds
setInterval(updateNotificationBadge, 30000);
</script>