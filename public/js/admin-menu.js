/**
 * Enhanced Menu Functionality for Security Service SaaS
 * Provides smooth animations, keyboard navigation, and better UX
 */

document.addEventListener('DOMContentLoaded', function() {
    
    // Initialize Bootstrap 5 dropdowns
    function initializeDropdowns() {
        // Check if Bootstrap is available
        if (typeof bootstrap !== 'undefined') {
            // Initialize all dropdown toggles
            const dropdownElements = document.querySelectorAll('.dropdown-toggle');
            dropdownElements.forEach(element => {
                new bootstrap.Dropdown(element);
            });
        } else {
            // Fallback for manual dropdown handling
            console.warn('Bootstrap not found, implementing manual dropdown handling');
            
            document.querySelectorAll('.dropdown-toggle').forEach(toggle => {
                toggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    const dropdownMenu = this.nextElementSibling;
                    
                    // Close other dropdowns
                    document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
                        if (menu !== dropdownMenu) {
                            menu.classList.remove('show');
                        }
                    });
                    
                    // Toggle current dropdown
                    dropdownMenu.classList.toggle('show');
                });
            });
            
            // Close dropdowns when clicking outside
            document.addEventListener('click', function(e) {
                if (!e.target.matches('.dropdown-toggle')) {
                    document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
                        menu.classList.remove('show');
                    });
                }
            });
        }
    }
    
    // Add smooth transitions to menu items
    const menuItems = document.querySelectorAll('.nav-item .nav-link');
    const dropdownItems = document.querySelectorAll('.dropdown-item');
    
    // Enhanced hover effects with staggered animation
    menuItems.forEach((item, index) => {
        item.style.animationDelay = `${index * 0.1}s`;
        
        item.addEventListener('mouseenter', function() {
            this.style.transform = 'translateX(5px) scale(1.02)';
            this.style.transition = 'all 0.3s cubic-bezier(0.4, 0, 0.2, 1)';
        });
        
        item.addEventListener('mouseleave', function() {
            this.style.transform = 'translateX(0) scale(1)';
        });
    });
    
    // Add ripple effect to menu items
    function createRipple(event) {
        const button = event.currentTarget;
        const circle = document.createElement('span');
        const diameter = Math.max(button.clientWidth, button.clientHeight);
        const radius = diameter / 2;
        
        circle.style.width = circle.style.height = `${diameter}px`;
        circle.style.left = `${event.clientX - button.offsetLeft - radius}px`;
        circle.style.top = `${event.clientY - button.offsetTop - radius}px`;
        circle.classList.add('menu-ripple');
        
        const ripple = button.getElementsByClassName('menu-ripple')[0];
        if (ripple) {
            ripple.remove();
        }
        
        button.appendChild(circle);
    }
    
    // Add CSS for ripple effect
    const rippleStyle = document.createElement('style');
    rippleStyle.innerHTML = `
        .menu-ripple {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.6);
            transform: scale(0);
            animation: ripple 0.6s linear;
            pointer-events: none;
        }
        
        @keyframes ripple {
            to {
                transform: scale(4);
                opacity: 0;
            }
        }
        
        .nav-link {
            position: relative;
            overflow: hidden;
        }
    `;
    document.head.appendChild(rippleStyle);
    
    // Apply ripple effect to menu items
    menuItems.forEach(item => {
        item.addEventListener('click', createRipple);
    });
    
    // Add breadcrumb-style navigation indicator
    function updateActiveMenuState() {
        const currentPath = window.location.pathname;
        
        menuItems.forEach(item => {
            const href = item.getAttribute('href');
            if (href && currentPath.includes(href.replace(window.location.origin, ''))) {
                item.classList.add('active', 'menu-active');
                
                // Add active indicator
                if (!item.querySelector('.active-indicator')) {
                    const indicator = document.createElement('span');
                    indicator.className = 'active-indicator';
                    indicator.innerHTML = '<i class="las la-chevron-right"></i>';
                    item.appendChild(indicator);
                }
            } else {
                item.classList.remove('active', 'menu-active');
                const indicator = item.querySelector('.active-indicator');
                if (indicator) {
                    indicator.remove();
                }
            }
        });
    }
    
    // Add CSS for active indicator
    const activeIndicatorStyle = document.createElement('style');
    activeIndicatorStyle.innerHTML = `
        .active-indicator {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #fff;
            font-size: 0.8rem;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }
        
        .menu-active {
            box-shadow: inset 4px 0 0 #007bff, 0 2px 8px rgba(0,123,255,0.3) !important;
            background: linear-gradient(135deg, rgba(0,123,255,0.1) 0%, rgba(0,123,255,0.05) 100%) !important;
        }
    `;
    document.head.appendChild(activeIndicatorStyle);
    
    // Update active state on load and navigation
    updateActiveMenuState();
    
    // Smart menu collapse for mobile
    function handleMobileMenu() {
        const sidebar = document.querySelector('.sidebar');
        const toggleBtn = document.querySelector('[data-widget="pushmenu"]');
        
        if (window.innerWidth <= 768) {
            sidebar?.classList.add('sidebar-mini');
        }
        
        // Auto-collapse submenu when clicking outside
        document.addEventListener('click', function(e) {
            const dropdowns = document.querySelectorAll('.dropdown-menu.show');
            dropdowns.forEach(dropdown => {
                if (!dropdown.contains(e.target) && !e.target.closest('.dropdown-toggle')) {
                    dropdown.classList.remove('show');
                }
            });
        });
    }
    
    // Keyboard navigation support
    function addKeyboardNavigation() {
        const menuLinks = document.querySelectorAll('.nav-link, .dropdown-item');
        let currentIndex = -1;
        
        document.addEventListener('keydown', function(e) {
            // Only activate when focus is within the sidebar
            if (!document.querySelector('.sidebar:focus-within')) return;
            
            switch(e.key) {
                case 'ArrowDown':
                    e.preventDefault();
                    currentIndex = Math.min(currentIndex + 1, menuLinks.length - 1);
                    menuLinks[currentIndex]?.focus();
                    break;
                case 'ArrowUp':
                    e.preventDefault();
                    currentIndex = Math.max(currentIndex - 1, 0);
                    menuLinks[currentIndex]?.focus();
                    break;
                case 'Enter':
                case ' ':
                    if (document.activeElement && menuLinks.includes(document.activeElement)) {
                        document.activeElement.click();
                    }
                    break;
            }
        });
    }
    
    // Add search functionality for menu items
    function addMenuSearch() {
        const searchContainer = document.createElement('div');
        searchContainer.className = 'menu-search-container p-3 border-bottom';
        searchContainer.innerHTML = `
            <div class="input-group input-group-sm">
                <input type="text" class="form-control" placeholder="Search menu..." id="menuSearch">
                <div class="input-group-append">
                    <span class="input-group-text">
                        <i class="las la-search"></i>
                    </span>
                </div>
            </div>
        `;
        
        const sidebar = document.querySelector('.sidebar .nav');
        if (sidebar) {
            sidebar.parentNode.insertBefore(searchContainer, sidebar);
            
            const searchInput = document.getElementById('menuSearch');
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                
                menuItems.forEach(item => {
                    const text = item.textContent.toLowerCase();
                    const parent = item.closest('.nav-item');
                    
                    if (text.includes(searchTerm) || searchTerm === '') {
                        parent.style.display = 'block';
                        if (searchTerm !== '') {
                            item.style.background = 'rgba(255, 193, 7, 0.2)';
                        } else {
                            item.style.background = '';
                        }
                    } else {
                        parent.style.display = 'none';
                    }
                });
            });
        }
    }
    
    // Add notification badges to menu items (example)
    function addNotificationBadges() {
        const badges = {
            'attendance': '3',
            'invoice': '5',
            'employee': '12'
        };
        
        Object.keys(badges).forEach(key => {
            const menuItem = document.querySelector(`[href*="${key}"]`);
            if (menuItem && !menuItem.querySelector('.badge')) {
                const badge = document.createElement('span');
                badge.className = 'badge badge-danger badge-pill ml-auto';
                badge.textContent = badges[key];
                badge.style.fontSize = '0.7rem';
                menuItem.appendChild(badge);
            }
        });
    }
    
    // Initialize all features
    initializeDropdowns(); // Initialize dropdown functionality first
    handleMobileMenu();
    addKeyboardNavigation();
    addMenuSearch();
    addNotificationBadges();
    
    // Add performance monitoring
    window.addEventListener('load', function() {
        console.log('Enhanced menu system loaded successfully');
    });
    
    // Smooth scrolling for anchor links
    document.querySelectorAll('.nav-link[href*="#"]').forEach(link => {
        link.addEventListener('click', function(e) {
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                e.preventDefault();
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
});

// Menu analytics (optional)
function trackMenuUsage(menuItem) {
    // This could send analytics data about menu usage
    console.log('Menu item clicked:', menuItem);
}