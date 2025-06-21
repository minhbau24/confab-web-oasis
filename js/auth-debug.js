// auth-debug.js - Helper functions to debug authentication state

/**
 * Prints the current authentication state to the console
 * Useful for debugging authentication issues
 */
function debugAuthState() {
    console.group('Authentication Debug Information');
    
    // Check direct global variable
    console.log('window.authCurrentUser:', window.authCurrentUser ? {
        id: window.authCurrentUser.id,
        name: window.authCurrentUser.name,
        email: window.authCurrentUser.email,
        role: window.authCurrentUser.role,
        hasToken: !!window.authCurrentUser.token
    } : 'Not defined');
    
    // Check global function
    console.log('window.getCurrentUser function:', typeof window.getCurrentUser === 'function' ? 'Available' : 'Not defined');
    
    if (typeof window.getCurrentUser === 'function') {
        try {
            const user = window.getCurrentUser();
            console.log('Result from window.getCurrentUser():', user ? {
                id: user.id,
                name: user.name,
                email: user.email,
                role: user.role,
                hasToken: !!user.token
            } : 'Null user');
        } catch (error) {
            console.error('Error calling window.getCurrentUser():', error);
        }
    }
    
    // Check localStorage
    const storedUser = localStorage.getItem('user');
    console.log('localStorage user:', storedUser ? 'Found' : 'Not found');
    
    if (storedUser) {
        try {
            const parsedUser = JSON.parse(storedUser);
            console.log('localStorage user data:', {
                id: parsedUser.id,
                name: parsedUser.name,
                email: parsedUser.email,
                role: parsedUser.role,
                hasToken: !!parsedUser.token
            });
        } catch (error) {
            console.error('Error parsing localStorage user:', error);
        }
    }
    
    // Check isLoggedIn function
    console.log('window.isLoggedIn function:', typeof window.isLoggedIn === 'function' ? 'Available' : 'Not defined');
    
    if (typeof window.isLoggedIn === 'function') {
        try {
            console.log('Result from window.isLoggedIn():', window.isLoggedIn());
        } catch (error) {
            console.error('Error calling window.isLoggedIn():', error);
        }
    }
    
    console.groupEnd();
}

// Export the debug function to the global scope
window.debugAuthState = debugAuthState;

// Auto-execute debug when script is loaded
document.addEventListener('DOMContentLoaded', function() {
    console.info('Auth Debug script loaded. Call window.debugAuthState() to debug authentication state.');
});
