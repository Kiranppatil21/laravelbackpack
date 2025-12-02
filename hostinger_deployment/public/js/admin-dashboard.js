/**
 * Admin Dashboard JavaScript
 * Handles notifications, real-time updates, and interactive features
 */

class AdminDashboard {
    constructor() {
        this.notificationUpdateInterval = 30000; // 30 seconds
        this.chartInstances = {};
        this.init();
    }

    init() {
        this.setupNotifications();
        this.setupCharts();
        this.setupRealTimeUpdates();
        this.setupQuickActions();
        this.setupTableEnhancements();
    }

    /**
     * Setup notification system
     */
    setupNotifications() {
        // Auto-refresh notification count
        setInterval(() => {
            this.updateNotificationCount();
        }, this.notificationUpdateInterval);

        // Handle notification clicks
        document.addEventListener('click', (e) => {
            if (e.target.closest('.notification-item')) {
                const notificationId = e.target.closest('.notification-item').dataset.notificationId;
                if (notificationId) {
                    this.markNotificationAsRead(notificationId);
                }
            }

            // Mark all as read
            if (e.target.closest('.mark-all-read')) {
                this.markAllNotificationsAsRead();
            }
        });

        // Show notification toasts
        this.showNotificationToasts();
    }

    /**
     * Update notification count in badge
     */
    async updateNotificationCount() {
        try {
            const response = await fetch('/admin/notifications/count', {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                }
            });

            if (response.ok) {
                const data = await response.json();
                this.updateNotificationBadge(data.count);
            }
        } catch (error) {
            console.warn('Failed to update notification count:', error);
        }
    }

    /**
     * Update notification badge UI
     */
    updateNotificationBadge(count) {
        const badge = document.querySelector('.notification-badge');
        
        if (count > 0) {
            if (badge) {
                badge.textContent = count > 99 ? '99+' : count;
                badge.style.display = 'flex';
            } else {
                // Create badge if it doesn't exist
                const bellIcon = document.querySelector('.notification-bell');
                if (bellIcon) {
                    const newBadge = document.createElement('span');
                    newBadge.className = 'notification-badge';
                    newBadge.textContent = count > 99 ? '99+' : count;
                    bellIcon.appendChild(newBadge);
                }
            }
        } else {
            if (badge) {
                badge.style.display = 'none';
            }
        }
    }

    /**
     * Mark single notification as read
     */
    async markNotificationAsRead(notificationId) {
        try {
            const response = await fetch(`/admin/notifications/${notificationId}/read`, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                }
            });

            if (response.ok) {
                // Update UI
                const notificationElement = document.querySelector(`[data-notification-id="${notificationId}"]`);
                if (notificationElement) {
                    notificationElement.style.opacity = '0.6';
                    notificationElement.classList.add('read');
                }
                
                // Update count
                this.updateNotificationCount();
            }
        } catch (error) {
            console.error('Failed to mark notification as read:', error);
        }
    }

    /**
     * Mark all notifications as read
     */
    async markAllNotificationsAsRead() {
        try {
            const response = await fetch('/admin/notifications/mark-all-read', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                }
            });

            if (response.ok) {
                // Refresh page or update UI
                window.location.reload();
            }
        } catch (error) {
            console.error('Failed to mark all notifications as read:', error);
        }
    }

    /**
     * Show notification toasts for important events
     */
    showNotificationToasts() {
        // Check for any flash messages or new notifications
        const flashMessages = document.querySelectorAll('.alert[data-auto-dismiss]');
        flashMessages.forEach(alert => {
            setTimeout(() => {
                alert.style.transition = 'opacity 0.3s ease';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 300);
            }, 5000);
        });
    }

    /**
     * Setup dashboard charts
     */
    setupCharts() {
        // Ensure Chart.js is loaded
        if (typeof Chart === 'undefined') {
            console.warn('Chart.js not loaded');
            return;
        }

        Chart.defaults.font.family = "'Segoe UI', Tahoma, Geneva, Verdana, sans-serif";
        Chart.defaults.font.size = 12;
        Chart.defaults.color = '#6c757d';

        // Setup responsive charts
        this.setupResponsiveCharts();
        
        // Add chart interaction handlers
        this.addChartInteractions();
    }

    /**
     * Make charts responsive
     */
    setupResponsiveCharts() {
        const chartContainers = document.querySelectorAll('.chart-container canvas');
        
        chartContainers.forEach(canvas => {
            const chart = Chart.getChart(canvas);
            if (chart) {
                chart.options.responsive = true;
                chart.options.maintainAspectRatio = false;
                chart.update();
            }
        });

        // Handle window resize
        window.addEventListener('resize', this.debounce(() => {
            chartContainers.forEach(canvas => {
                const chart = Chart.getChart(canvas);
                if (chart) {
                    chart.resize();
                }
            });
        }, 250));
    }

    /**
     * Add chart click interactions
     */
    addChartInteractions() {
        document.addEventListener('click', (e) => {
            const canvas = e.target.closest('.chart-container canvas');
            if (canvas) {
                const chart = Chart.getChart(canvas);
                if (chart) {
                    const points = chart.getElementsAtEventForMode(e, 'nearest', { intersect: true }, true);
                    if (points.length > 0) {
                        const firstPoint = points[0];
                        const label = chart.data.labels[firstPoint.index];
                        const value = chart.data.datasets[firstPoint.datasetIndex].data[firstPoint.index];
                        
                        // Show tooltip with detailed info
                        this.showChartTooltip(e, label, value, canvas);
                    }
                }
            }
        });
    }

    /**
     * Show custom chart tooltip
     */
    showChartTooltip(event, label, value, canvas) {
        // Remove existing tooltips
        const existingTooltips = document.querySelectorAll('.custom-chart-tooltip');
        existingTooltips.forEach(tooltip => tooltip.remove());

        // Create new tooltip
        const tooltip = document.createElement('div');
        tooltip.className = 'custom-chart-tooltip';
        tooltip.innerHTML = `
            <div class="tooltip-content">
                <strong>${label}</strong><br>
                Value: ${typeof value === 'number' ? value.toLocaleString() : value}
            </div>
        `;

        tooltip.style.cssText = `
            position: absolute;
            background: rgba(0,0,0,0.8);
            color: white;
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 12px;
            z-index: 1000;
            pointer-events: none;
            left: ${event.pageX + 10}px;
            top: ${event.pageY - 30}px;
        `;

        document.body.appendChild(tooltip);

        // Auto-remove after 3 seconds
        setTimeout(() => tooltip.remove(), 3000);
    }

    /**
     * Setup real-time dashboard updates
     */
    setupRealTimeUpdates() {
        // Update widget values periodically
        setInterval(() => {
            this.updateDashboardWidgets();
        }, 60000); // Every minute

        // Update activity feed
        setInterval(() => {
            this.updateActivityFeed();
        }, 30000); // Every 30 seconds
    }

    /**
     * Update dashboard widget values
     */
    async updateDashboardWidgets() {
        try {
            const response = await fetch('/admin/dashboard/widgets', {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                }
            });

            if (response.ok) {
                const data = await response.json();
                this.refreshWidgetValues(data);
            }
        } catch (error) {
            console.warn('Failed to update dashboard widgets:', error);
        }
    }

    /**
     * Refresh widget values in UI
     */
    refreshWidgetValues(data) {
        if (data.widgets) {
            data.widgets.forEach(widget => {
                const widgetElement = document.querySelector(`[data-widget="${widget.key}"]`);
                if (widgetElement) {
                    const valueElement = widgetElement.querySelector('.widget-value');
                    const trendElement = widgetElement.querySelector('.widget-trend');
                    
                    if (valueElement) {
                        this.animateValueChange(valueElement, widget.value);
                    }
                    
                    if (trendElement && widget.trend) {
                        trendElement.textContent = widget.trend;
                        trendElement.className = `widget-trend ${widget.trend.includes('+') ? 'positive' : 'negative'}`;
                    }
                }
            });
        }
    }

    /**
     * Animate value changes
     */
    animateValueChange(element, newValue) {
        const currentValue = element.textContent.replace(/[^\d.-]/g, '');
        const current = parseFloat(currentValue) || 0;
        const target = parseFloat(newValue.toString().replace(/[^\d.-]/g, '')) || 0;
        
        if (current !== target) {
            element.style.transform = 'scale(1.1)';
            element.style.transition = 'transform 0.3s ease';
            
            setTimeout(() => {
                element.textContent = newValue;
                element.style.transform = 'scale(1)';
            }, 150);
        }
    }

    /**
     * Update activity feed
     */
    async updateActivityFeed() {
        try {
            const response = await fetch('/admin/dashboard/activity', {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                }
            });

            if (response.ok) {
                const data = await response.json();
                this.refreshActivityFeed(data.activities || []);
            }
        } catch (error) {
            console.warn('Failed to update activity feed:', error);
        }
    }

    /**
     * Refresh activity feed content
     */
    refreshActivityFeed(activities) {
        const feedContainer = document.querySelector('.activity-feed');
        if (feedContainer && activities.length > 0) {
            // Add new activities with animation
            activities.forEach(activity => {
                if (!document.querySelector(`[data-activity-id="${activity.id}"]`)) {
                    const activityElement = this.createActivityElement(activity);
                    feedContainer.insertBefore(activityElement, feedContainer.firstChild);
                }
            });

            // Remove excess activities (keep max 10)
            const activityItems = feedContainer.querySelectorAll('.activity-item');
            if (activityItems.length > 10) {
                for (let i = 10; i < activityItems.length; i++) {
                    activityItems[i].remove();
                }
            }
        }
    }

    /**
     * Create activity element
     */
    createActivityElement(activity) {
        const div = document.createElement('div');
        div.className = 'activity-item';
        div.dataset.activityId = activity.id;
        div.style.opacity = '0';
        div.style.transform = 'translateY(-10px)';
        
        div.innerHTML = `
            <div class="activity-icon ${activity.type}">
                <i class="${activity.icon}"></i>
            </div>
            <div class="activity-content">
                <div class="activity-title">${activity.title}</div>
                <div class="activity-time">${activity.time}</div>
            </div>
        `;

        // Animate in
        setTimeout(() => {
            div.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
            div.style.opacity = '1';
            div.style.transform = 'translateY(0)';
        }, 50);

        return div;
    }

    /**
     * Setup quick actions
     */
    setupQuickActions() {
        // Add keyboard shortcuts
        document.addEventListener('keydown', (e) => {
            if (e.ctrlKey || e.metaKey) {
                switch (e.key) {
                    case 'n':
                        e.preventDefault();
                        this.triggerQuickAction('new-employee');
                        break;
                    case 'm':
                        e.preventDefault();
                        this.triggerQuickAction('mark-attendance');
                        break;
                    case 'i':
                        e.preventDefault();
                        this.triggerQuickAction('create-invoice');
                        break;
                }
            }
        });

        // Add click handlers for quick actions
        document.addEventListener('click', (e) => {
            const quickAction = e.target.closest('.quick-action-btn');
            if (quickAction) {
                const actionType = quickAction.dataset.action;
                if (actionType) {
                    this.triggerQuickAction(actionType);
                }
            }
        });
    }

    /**
     * Trigger quick action
     */
    triggerQuickAction(actionType) {
        const actions = {
            'new-employee': '/admin/employee/create',
            'mark-attendance': '/admin/attendance/create', 
            'create-invoice': '/admin/invoice/create',
            'process-payroll': '/admin/payroll/create'
        };

        const url = actions[actionType];
        if (url) {
            window.location.href = url;
        }
    }

    /**
     * Setup table enhancements
     */
    setupTableEnhancements() {
        // Add search functionality
        this.setupTableSearch();
        
        // Add sorting functionality
        this.setupTableSorting();
        
        // Add export functionality
        this.setupTableExport();
    }

    /**
     * Setup table search
     */
    setupTableSearch() {
        const searchInputs = document.querySelectorAll('.table-search');
        
        searchInputs.forEach(input => {
            input.addEventListener('input', this.debounce((e) => {
                const searchTerm = e.target.value.toLowerCase();
                const table = e.target.closest('.table-container').querySelector('table');
                const rows = table.querySelectorAll('tbody tr');
                
                rows.forEach(row => {
                    const text = row.textContent.toLowerCase();
                    row.style.display = text.includes(searchTerm) ? '' : 'none';
                });
            }, 300));
        });
    }

    /**
     * Setup table sorting
     */
    setupTableSorting() {
        const sortableHeaders = document.querySelectorAll('th[data-sortable]');
        
        sortableHeaders.forEach(header => {
            header.style.cursor = 'pointer';
            header.addEventListener('click', () => {
                this.sortTable(header);
            });
        });
    }

    /**
     * Sort table by column
     */
    sortTable(header) {
        const table = header.closest('table');
        const tbody = table.querySelector('tbody');
        const rows = Array.from(tbody.querySelectorAll('tr'));
        const columnIndex = Array.from(header.parentNode.children).indexOf(header);
        const dataType = header.dataset.type || 'string';
        
        // Toggle sort direction
        const isAscending = header.classList.contains('sort-asc');
        
        // Remove sort classes from all headers
        table.querySelectorAll('th').forEach(h => {
            h.classList.remove('sort-asc', 'sort-desc');
        });
        
        // Add appropriate class
        header.classList.add(isAscending ? 'sort-desc' : 'sort-asc');
        
        // Sort rows
        rows.sort((a, b) => {
            const aValue = a.children[columnIndex].textContent.trim();
            const bValue = b.children[columnIndex].textContent.trim();
            
            let comparison = 0;
            
            if (dataType === 'number') {
                comparison = parseFloat(aValue) - parseFloat(bValue);
            } else if (dataType === 'date') {
                comparison = new Date(aValue) - new Date(bValue);
            } else {
                comparison = aValue.localeCompare(bValue);
            }
            
            return isAscending ? -comparison : comparison;
        });
        
        // Reorder DOM
        rows.forEach(row => tbody.appendChild(row));
    }

    /**
     * Setup table export
     */
    setupTableExport() {
        const exportButtons = document.querySelectorAll('.export-btn');
        
        exportButtons.forEach(button => {
            button.addEventListener('click', (e) => {
                const format = e.target.dataset.format || 'csv';
                const table = e.target.closest('.table-container').querySelector('table');
                this.exportTable(table, format);
            });
        });
    }

    /**
     * Export table data
     */
    exportTable(table, format) {
        const data = this.extractTableData(table);
        
        switch (format) {
            case 'csv':
                this.downloadCSV(data);
                break;
            case 'excel':
                this.downloadExcel(data);
                break;
            case 'pdf':
                this.downloadPDF(data);
                break;
        }
    }

    /**
     * Extract table data
     */
    extractTableData(table) {
        const headers = Array.from(table.querySelectorAll('thead th')).map(th => th.textContent.trim());
        const rows = Array.from(table.querySelectorAll('tbody tr')).map(row => 
            Array.from(row.querySelectorAll('td')).map(td => td.textContent.trim())
        );
        
        return { headers, rows };
    }

    /**
     * Download CSV
     */
    downloadCSV(data) {
        const csv = [
            data.headers.join(','),
            ...data.rows.map(row => row.map(cell => `"${cell.replace(/"/g, '""')}"`).join(','))
        ].join('\n');
        
        this.downloadFile(csv, 'export.csv', 'text/csv');
    }

    /**
     * Download file
     */
    downloadFile(content, filename, mimeType) {
        const blob = new Blob([content], { type: mimeType });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = filename;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    }

    /**
     * Utility: Debounce function
     */
    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    /**
     * Show loading state
     */
    showLoading(element) {
        element.style.opacity = '0.6';
        element.style.pointerEvents = 'none';
    }

    /**
     * Hide loading state
     */
    hideLoading(element) {
        element.style.opacity = '1';
        element.style.pointerEvents = 'auto';
    }

    /**
     * Show toast notification
     */
    showToast(message, type = 'info', duration = 5000) {
        const toast = document.createElement('div');
        toast.className = `toast-notification toast-${type}`;
        toast.innerHTML = `
            <div class="toast-content">
                <i class="toast-icon las la-${type === 'success' ? 'check-circle' : 'info-circle'}"></i>
                <span class="toast-message">${message}</span>
                <button class="toast-close" onclick="this.parentElement.parentElement.remove()">
                    <i class="las la-times"></i>
                </button>
            </div>
        `;

        toast.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: white;
            border-left: 4px solid ${type === 'success' ? '#28a745' : type === 'warning' ? '#ffc107' : type === 'error' ? '#dc3545' : '#007bff'};
            padding: 1rem;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            z-index: 9999;
            max-width: 400px;
            transform: translateX(100%);
            transition: transform 0.3s ease;
        `;

        document.body.appendChild(toast);

        // Animate in
        setTimeout(() => {
            toast.style.transform = 'translateX(0)';
        }, 100);

        // Auto remove
        setTimeout(() => {
            toast.style.transform = 'translateX(100%)';
            setTimeout(() => toast.remove(), 300);
        }, duration);
    }
}

// Initialize dashboard when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.adminDashboard = new AdminDashboard();
});

// Global helper functions
window.markNotificationAsRead = function(id) {
    window.adminDashboard.markNotificationAsRead(id);
};

window.markAllNotificationsAsRead = function() {
    window.adminDashboard.markAllNotificationsAsRead();
};

window.exportTableData = function(format) {
    const table = document.querySelector('table');
    if (table) {
        window.adminDashboard.exportTable(table, format);
    }
};