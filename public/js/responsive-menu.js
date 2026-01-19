/**
 * Responsive Menu JavaScript
 * Security Services SAAS - SecureGuard
 * Handles mobile menu toggle, dropdowns, and accessibility
 */

(function() {
    'use strict';
    
    // Wait for DOM to be ready
    document.addEventListener('DOMContentLoaded', function() {
        initMobileMenu();
        initDropdowns();
        initAccessibility();
    });
    
    /**
     * Initialize Mobile Menu Toggle
     */
    function initMobileMenu() {
        const toggler = document.querySelector('.navbar-toggler');
        const collapse = document.querySelector('.navbar-collapse');
        const body = document.body;
        
        if (!toggler || !collapse) return;
        
        // Create mobile menu header if it doesn't exist
        const nav = collapse.querySelector('.navbar-nav');
        if (nav && !nav.querySelector('.mobile-menu-header')) {
            const header = document.createElement('div');
            header.className = 'mobile-menu-header';
            header.innerHTML = `
                <span class="mobile-menu-title">Menu</span>
                <button class="mobile-menu-close" aria-label="Close menu">×</button>
            `;
            nav.insertBefore(header, nav.firstChild);
        }
        
        // Toggle menu on button click
        toggler.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            toggleMenu();
        });
        
        // Close menu on close button click
        const closeBtn = document.querySelector('.mobile-menu-close');
        if (closeBtn) {
            closeBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                closeMenu();
            });
        }
        
        // Close menu on overlay click
        collapse.addEventListener('click', function(e) {
            if (e.target === collapse) {
                closeMenu();
            }
        });
        
        // Close menu on ESC key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && collapse.classList.contains('show')) {
                closeMenu();
            }
        });
        
        // Prevent body scroll when menu is open
        function toggleMenu() {
            const isShown = collapse.classList.toggle('show');
            toggler.classList.toggle('active');
            body.style.overflow = isShown ? 'hidden' : '';
            
            // Update aria attributes
            toggler.setAttribute('aria-expanded', isShown);
            collapse.setAttribute('aria-hidden', !isShown);
            
            // Focus management
            if (isShown) {
                // Focus first menu item
                const firstItem = collapse.querySelector('.nav-link');
                if (firstItem) {
                    setTimeout(() => firstItem.focus(), 300);
                }
            } else {
                // Return focus to toggler
                toggler.focus();
            }
        }
        
        function closeMenu() {
            collapse.classList.remove('show');
            toggler.classList.remove('active');
            body.style.overflow = '';
            toggler.setAttribute('aria-expanded', 'false');
            collapse.setAttribute('aria-hidden', 'true');
            toggler.focus();
        }
        
        // Close menu on window resize to desktop
        let resizeTimer;
        window.addEventListener('resize', function() {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(function() {
                if (window.innerWidth > 768 && collapse.classList.contains('show')) {
                    closeMenu();
                }
            }, 250);
        });
    }
    
    /**
     * Initialize Dropdown Functionality
     */
    function initDropdowns() {
        const dropdowns = document.querySelectorAll('.nav-dropdown');
        
        dropdowns.forEach(function(dropdown) {
            const toggle = dropdown.querySelector('.nav-dropdown-toggle');
            const items = dropdown.querySelector('.nav-dropdown-items');
            
            if (!toggle || !items) return;
            
            // Mobile: Click to toggle
            if (window.innerWidth <= 768) {
                toggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    // Close other dropdowns
                    dropdowns.forEach(function(other) {
                        if (other !== dropdown) {
                            other.classList.remove('active');
                        }
                    });
                    
                    // Toggle current dropdown
                    dropdown.classList.toggle('active');
                    
                    // Update aria
                    const isExpanded = dropdown.classList.contains('active');
                    toggle.setAttribute('aria-expanded', isExpanded);
                });
            } else {
                // Desktop: Hover (already handled by CSS)
                // But we can add click functionality as backup
                toggle.addEventListener('click', function(e) {
                    if (toggle.getAttribute('href') === '#') {
                        e.preventDefault();
                    }
                });
            }
        });
        
        // Re-initialize on resize
        let resizeTimer;
        window.addEventListener('resize', function() {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(function() {
                // Remove active class on desktop
                if (window.innerWidth > 768) {
                    dropdowns.forEach(function(dropdown) {
                        dropdown.classList.remove('active');
                    });
                }
            }, 250);
        });
    }
    
    /**
     * Initialize Accessibility Features
     */
    function initAccessibility() {
        // Add keyboard navigation
        const navLinks = document.querySelectorAll('.nav-link');
        
        navLinks.forEach(function(link, index) {
            link.addEventListener('keydown', function(e) {
                let targetLink = null;
                
                switch(e.key) {
                    case 'ArrowDown':
                    case 'Down':
                        e.preventDefault();
                        targetLink = navLinks[index + 1];
                        break;
                    case 'ArrowUp':
                    case 'Up':
                        e.preventDefault();
                        targetLink = navLinks[index - 1];
                        break;
                    case 'Home':
                        e.preventDefault();
                        targetLink = navLinks[0];
                        break;
                    case 'End':
                        e.preventDefault();
                        targetLink = navLinks[navLinks.length - 1];
                        break;
                }
                
                if (targetLink) {
                    targetLink.focus();
                }
            });
        });
        
        // Add aria labels if missing
        const toggler = document.querySelector('.navbar-toggler');
        if (toggler && !toggler.getAttribute('aria-label')) {
            toggler.setAttribute('aria-label', 'Toggle navigation menu');
        }
        
        const collapse = document.querySelector('.navbar-collapse');
        if (collapse && !collapse.getAttribute('aria-label')) {
            collapse.setAttribute('aria-label', 'Main navigation');
        }
    }
    
    /**
     * Highlight Active Menu Item
     */
    function highlightActiveMenuItem() {
        const currentPath = window.location.pathname;
        const navLinks = document.querySelectorAll('.nav-link');
        
        navLinks.forEach(function(link) {
            const href = link.getAttribute('href');
            if (href && currentPath.includes(href.replace(/^.*\/admin/, '/admin'))) {
                link.classList.add('active');
                
                // Also expand parent dropdown if inside one
                const parentDropdown = link.closest('.nav-dropdown');
                if (parentDropdown) {
                    parentDropdown.classList.add('active');
                }
            }
        });
    }
    
    // Run on load
    highlightActiveMenuItem();
    
    /**
     * Smooth Scroll for Internal Links
     */
    function initSmoothScroll() {
        const links = document.querySelectorAll('a[href^="#"]');
        
        links.forEach(function(link) {
            link.addEventListener('click', function(e) {
                const targetId = this.getAttribute('href');
                if (targetId === '#') return;
                
                const target = document.querySelector(targetId);
                if (target) {
                    e.preventDefault();
                    
                    // Close mobile menu if open
                    const collapse = document.querySelector('.navbar-collapse');
                    if (collapse && collapse.classList.contains('show')) {
                        collapse.classList.remove('show');
                        document.body.style.overflow = '';
                    }
                    
                    // Smooth scroll to target
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    }
    
    initSmoothScroll();
    
    /**
     * Add Touch Event Handlers for Better Mobile UX
     */
    if ('ontouchstart' in window) {
        const navLinks = document.querySelectorAll('.nav-link');
        
        navLinks.forEach(function(link) {
            let touchStartY = 0;
            
            link.addEventListener('touchstart', function(e) {
                touchStartY = e.touches[0].clientY;
            });
            
            link.addEventListener('touchend', function(e) {
                const touchEndY = e.changedTouches[0].clientY;
                const diff = Math.abs(touchEndY - touchStartY);
                
                // If it's not a scroll, treat as a tap
                if (diff < 10) {
                    this.click();
                }
            });
        });
    }
    
})();

/**
 * Export for use in other scripts if needed
 */
window.ResponsiveMenu = {
    close: function() {
        const collapse = document.querySelector('.navbar-collapse');
        const toggler = document.querySelector('.navbar-toggler');
        if (collapse) {
            collapse.classList.remove('show');
            toggler.classList.remove('active');
            document.body.style.overflow = '';
        }
    },
    
    open: function() {
        const collapse = document.querySelector('.navbar-collapse');
        const toggler = document.querySelector('.navbar-toggler');
        if (collapse) {
            collapse.classList.add('show');
            toggler.classList.add('active');
            document.body.style.overflow = 'hidden';
        }
    },
    
    toggle: function() {
        const collapse = document.querySelector('.navbar-collapse');
        if (collapse && collapse.classList.contains('show')) {
            this.close();
        } else {
            this.open();
        }
    }
};
