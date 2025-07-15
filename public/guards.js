// Page Guards - Include this in pages that need auth protection
// Usage: Add script tag: <script src="/guards.js"></script>

// Get page type from meta tag or URL
function getPageType() {
    // Check for meta tag first
    const metaTag = document.querySelector('meta[name="page-type"]');
    if (metaTag) {
        return metaTag.getAttribute('content');
    }
    
    // Fallback to URL-based detection
    const path = window.location.pathname;
    
    if (path === '/login' || path === '/register') {
        return 'guest';
    } else if (path.startsWith('/admin')) {
        return 'admin';
    } else if (path === '/user' || path === '/story-detail' || path.includes('profile')) {
        return 'protected';
    } else {
        return 'public';
    }
}

// Apply appropriate guard based on page type
function applyPageGuard() {
    const pageType = getPageType();
    
    switch(pageType) {
        case 'guest':
            // Login/Register pages - redirect if already logged in
            if (typeof requireGuest === 'function') {
                requireGuest();
            }
            break;
            
        case 'admin':
            // Admin pages - require admin role
            if (typeof requireAdmin === 'function') {
                requireAdmin();
            }
            break;
            
        case 'protected':
            // Protected pages - require any authentication
            if (typeof requireAuth === 'function') {
                requireAuth();
            }
            break;
            
        case 'public':
        default:
            // Public pages - no guard needed, just update UI
            if (typeof updateAuthUI === 'function') {
                updateAuthUI();
            }
            break;
    }
}

// Apply guards when DOM is loaded
document.addEventListener('DOMContentLoaded', applyPageGuard);
