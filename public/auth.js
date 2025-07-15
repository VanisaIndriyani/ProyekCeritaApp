// Simplified auth.js - Most authentication is now handled server-side with PHP

// Logout functionality for JavaScript calls
function logout() {
    // Redirect to logout script
    window.location.href = '/logout.php';
}

// Cookie utility functions (still needed for some client-side operations)
function setCookie(name, value, days) {
    const expires = new Date();
    expires.setTime(expires.getTime() + (days * 24 * 60 * 60 * 1000));
    document.cookie = `${name}=${value};expires=${expires.toUTCString()};path=/;SameSite=Lax`;
}

function getCookie(name) {
    const nameEQ = name + "=";
    const ca = document.cookie.split(';');
    for(let i = 0; i < ca.length; i++) {
        let c = ca[i];
        while (c.charAt(0) === ' ') c = c.substring(1, c.length);
        if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length, c.length);
    }
    return null;
}

function deleteCookie(name) {
    document.cookie = `${name}=;expires=Thu, 01 Jan 1970 00:00:00 UTC;path=/;`;
}

// Legacy functions for backward compatibility with existing code
function isAuthenticated() {
    // This is now handled server-side, but we can check if user elements exist
    return document.querySelector('.user-profile') !== null;
}

function getUserData() {
    // Return null since this is now handled server-side
    return null;
}

function isAdmin() {
    // Check if admin links are visible (set by server-side PHP)
    const adminLink = document.querySelector('a[href="/admin"]');
    return adminLink !== null;
}

// Client-side only functions for guest actions
function requireAuth() {
    // Redirect to login
    window.location.href = '/login';
    return false;
}

// Export functions for global use (backward compatibility)
window.logout = logout;
window.isAuthenticated = isAuthenticated;
window.getUserData = getUserData;
window.isAdmin = isAdmin;
window.requireAuth = requireAuth;
