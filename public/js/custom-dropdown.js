/**
 * Custom Dropdown Menu JavaScript
 * Enhances the dropdown functionality with smooth animations and better UX
 */

document.addEventListener('DOMContentLoaded', function() {
    // Get all dropdown elements
    const dropdowns = document.querySelectorAll('.nav-dropdown');
    
    dropdowns.forEach(function(dropdown) {
        const toggle = dropdown.querySelector('.nav-dropdown-toggle');
        const items = dropdown.querySelector('.nav-dropdown-items');
        
        if (!toggle || !items) return;
        
        let hoverTimeout;
        
        // Mouse enter event
        dropdown.addEventListener('mouseenter', function() {
            clearTimeout(hoverTimeout);
            
            // Close other dropdowns
            dropdowns.forEach(function(otherDropdown) {
                if (otherDropdown !== dropdown) {
                    const otherItems = otherDropdown.querySelector('.nav-dropdown-items');
                    const otherToggle = otherDropdown.querySelector('.nav-dropdown-toggle');
                    if (otherItems) {
                        otherItems.style.display = 'none';
                        otherToggle.setAttribute('aria-expanded', 'false');
                    }
                }
            });
            
            // Show current dropdown
            items.style.display = 'block';
            toggle.setAttribute('aria-expanded', 'true');
            
            // Add active class for styling
            dropdown.classList.add('dropdown-active');
        });
        
        // Mouse leave event with delay
        dropdown.addEventListener('mouseleave', function() {
            hoverTimeout = setTimeout(function() {
                items.style.display = 'none';
                toggle.setAttribute('aria-expanded', 'false');
                dropdown.classList.remove('dropdown-active');
            }, 150); // Small delay to prevent flicker
        });
        
        // Click event for mobile/touch devices
        toggle.addEventListener('click', function(e) {
            // Only handle click on mobile devices or when dropdown is not visible
            if (window.innerWidth <= 768 || items.style.display === 'none') {
                e.preventDefault();
                
                // Toggle current dropdown
                const isOpen = items.style.display === 'block';
                
                // Close all dropdowns
                dropdowns.forEach(function(otherDropdown) {
                    const otherItems = otherDropdown.querySelector('.nav-dropdown-items');
                    const otherToggle = otherDropdown.querySelector('.nav-dropdown-toggle');
                    if (otherItems) {
                        otherItems.style.display = 'none';
                        otherToggle.setAttribute('aria-expanded', 'false');
                        otherDropdown.classList.remove('dropdown-active');
                    }
                });
                
                // Toggle current dropdown
                if (!isOpen) {
                    items.style.display = 'block';
                    toggle.setAttribute('aria-expanded', 'true');
                    dropdown.classList.add('dropdown-active');
                } else {
                    items.style.display = 'none';
                    toggle.setAttribute('aria-expanded', 'false');
                    dropdown.classList.remove('dropdown-active');
                }
            }
        });
        
        // Add ARIA attributes for accessibility
        toggle.setAttribute('role', 'button');
        toggle.setAttribute('aria-expanded', 'false');
        toggle.setAttribute('aria-haspopup', 'true');
        
        if (items) {
            items.setAttribute('role', 'menu');
            
            // Add role to dropdown items
            const menuItems = items.querySelectorAll('.nav-link');
            menuItems.forEach(function(item) {
                item.setAttribute('role', 'menuitem');
            });
        }
    });
    
    // Close dropdowns when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.nav-dropdown')) {
            dropdowns.forEach(function(dropdown) {
                const items = dropdown.querySelector('.nav-dropdown-items');
                const toggle = dropdown.querySelector('.nav-dropdown-toggle');
                if (items) {
                    items.style.display = 'none';
                    toggle.setAttribute('aria-expanded', 'false');
                    dropdown.classList.remove('dropdown-active');
                }
            });
        }
    });
    
    // Handle keyboard navigation
    document.addEventListener('keydown', function(e) {
        // Escape key closes all dropdowns
        if (e.key === 'Escape') {
            dropdowns.forEach(function(dropdown) {
                const items = dropdown.querySelector('.nav-dropdown-items');
                const toggle = dropdown.querySelector('.nav-dropdown-toggle');
                if (items && items.style.display === 'block') {
                    items.style.display = 'none';
                    toggle.setAttribute('aria-expanded', 'false');
                    dropdown.classList.remove('dropdown-active');
                    toggle.focus(); // Return focus to toggle
                }
            });
        }
    });
    
    // Enhanced hover effects for dropdown items
    document.querySelectorAll('.nav-dropdown-items .nav-link').forEach(function(link) {
        link.addEventListener('mouseenter', function() {
            this.style.transform = 'translateX(5px)';
        });
        
        link.addEventListener('mouseleave', function() {
            this.style.transform = 'translateX(0)';
        });
    });
    
    // Add smooth scroll for long dropdown menus
    document.querySelectorAll('.nav-dropdown-items').forEach(function(items) {
        if (items.children.length > 8) {
            items.style.maxHeight = '300px';
            items.style.overflowY = 'auto';
            items.style.scrollbarWidth = 'thin';
        }
    });
});

// Add CSS transition styles programmatically if needed
function addTransitionStyles() {
    const style = document.createElement('style');
    style.textContent = `
        .nav-dropdown-toggle {
            transition: color 0.2s ease;
        }
        
        .nav-dropdown .nav-link {
            transition: all 0.2s ease;
        }
        
        .dropdown-active .nav-dropdown-toggle {
            color: #007bff !important;
        }
    `;
    document.head.appendChild(style);
}

// Initialize additional styles
addTransitionStyles();