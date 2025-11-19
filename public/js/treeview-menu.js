/**
 * Simple TreeView Menu Implementation for Backpack
 * Uses AdminLTE-style treeview functionality
 */

document.addEventListener('DOMContentLoaded', function() {
    console.log('TreeView menu script loaded');
    
    // Initialize treeview menus
    function initTreeView() {
        const treeViewItems = document.querySelectorAll('.has-treeview > a');
        
        treeViewItems.forEach(function(item) {
            item.addEventListener('click', function(e) {
                e.preventDefault();
                
                const parentItem = this.parentElement;
                const subMenu = parentItem.querySelector('.nav-treeview');
                const icon = this.querySelector('.las.la-angle-left');
                
                if (!subMenu) return;
                
                // Toggle current menu
                const isOpen = parentItem.classList.contains('menu-open');
                
                if (isOpen) {
                    // Close menu
                    parentItem.classList.remove('menu-open');
                    subMenu.style.display = 'none';
                    if (icon) icon.style.transform = 'rotate(0deg)';
                } else {
                    // Close all other menus first
                    treeViewItems.forEach(function(otherItem) {
                        const otherParent = otherItem.parentElement;
                        const otherSubMenu = otherParent.querySelector('.nav-treeview');
                        const otherIcon = otherItem.querySelector('.las.la-angle-left');
                        
                        if (otherParent !== parentItem) {
                            otherParent.classList.remove('menu-open');
                            if (otherSubMenu) otherSubMenu.style.display = 'none';
                            if (otherIcon) otherIcon.style.transform = 'rotate(0deg)';
                        }
                    });
                    
                    // Open current menu
                    parentItem.classList.add('menu-open');
                    subMenu.style.display = 'block';
                    if (icon) icon.style.transform = 'rotate(-90deg)';
                }
            });
        });
        
        // Initialize first menu as open (optional)
        const firstTreeView = document.querySelector('.has-treeview.menu-open');
        if (firstTreeView) {
            const subMenu = firstTreeView.querySelector('.nav-treeview');
            const icon = firstTreeView.querySelector('.las.la-angle-left');
            if (subMenu) subMenu.style.display = 'block';
            if (icon) icon.style.transform = 'rotate(-90deg)';
        }
    }
    
    // Add basic styling
    const style = document.createElement('style');
    style.innerHTML = `
        .nav-treeview {
            display: none;
            padding-left: 1rem;
            background-color: rgba(0,0,0,0.05);
        }
        
        .nav-treeview .nav-item {
            margin: 0;
        }
        
        .nav-treeview .nav-link {
            padding: 0.5rem 1rem;
            font-size: 0.9rem;
            color: rgba(0,0,0,0.7);
        }
        
        .nav-treeview .nav-link:hover {
            background-color: rgba(0,123,255,0.1);
            color: #007bff;
        }
        
        .has-treeview > a .las.la-angle-left {
            transition: transform 0.3s ease;
        }
        
        .nav-header {
            font-size: 0.8rem;
            font-weight: bold;
            color: #6c757d;
            padding: 1rem 1rem 0.5rem;
            margin-top: 1rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .nav-item .nav-link {
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
        }
        
        .nav-item .nav-link:hover {
            background-color: rgba(0,123,255,0.1);
            transform: translateX(3px);
        }
        
        .nav-icon {
            width: 20px;
            text-align: center;
            margin-right: 0.5rem;
        }
    `;
    document.head.appendChild(style);
    
    // Initialize the treeview
    initTreeView();
    
    console.log('TreeView menus initialized successfully');
});