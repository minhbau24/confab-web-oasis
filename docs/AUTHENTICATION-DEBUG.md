# Authentication Debug and Fix Documentation

This document explains recent changes to fix authentication issues in the Confab Web Oasis project.

## Issues Fixed

1. **Stack Overflow Error in conferences-api.js**: Fixed an infinite recursion issue caused by circular references when accessing user data.

2. **Authentication State Management**: Improved how user state is preserved across modules by ensuring proper sharing of the authentication data.

## Changes Made

### 1. Fixed the `getCurrentUser()` method in conferences-api.js

The original implementation could potentially cause recursion when accessing `window.authCurrentUser`. The updated version creates a shallow copy of the user object to avoid reference issues:

```javascript
function getCurrentUser() {
    // Use a simple approach that can't cause recursion
    // First try using the exposed global object directly
    if (typeof window.authCurrentUser !== 'undefined') {
        // Create a shallow copy to avoid reference issues
        const user = window.authCurrentUser;
        return {
            id: user.id,
            name: user.name,
            email: user.email,
            role: user.role,
            token: user.token
        };
    }
    
    // Try to get from localStorage as fallback
    const storedUser = localStorage.getItem('user');
    return storedUser ? JSON.parse(storedUser) : { 
        id: null, 
        name: null, 
        email: user.email, 
        role: 'guest' 
    };
}
```

### 2. Added auth-debug.js for Authentication Debugging

Created a new debugging utility that can help diagnose authentication issues:

- Provides `debugAuthState()` function that inspects all authentication mechanisms
- Checks for the presence of global variables, functions, and localStorage data
- Can be triggered from the console: `window.debugAuthState()`
- Automatically included in login.html and conferences.html pages

## How Authentication Works

The project uses a multi-layered approach to maintain authentication state:

1. **auth.js**: The primary authentication manager that:
   - Exposes `window.authCurrentUser` for direct access to the current user object 
   - Provides `window.getCurrentUser()` as a function to get the current user
   - Exposes `window.isLoggedIn()` for quick authentication checks

2. **localStorage**: Used as a backup mechanism to persist user data between page loads
   - Key 'user' stores the serialized user object

3. **API Token**: Stored with the user object for authenticating API requests

## Best Practices

1. **Prefer Using Exposed Functions**: Use `window.isLoggedIn()` and `window.getCurrentUser()` when possible

2. **Avoid Recursion**: When implementing custom authentication checks, use direct property access on `window.authCurrentUser` rather than calling functions that might lead to recursion

3. **Debug Authentication Issues**: Use `window.debugAuthState()` from the browser console when troubleshooting login or permissions problems

## Future Improvements

1. **Event-based Authentication**: Consider implementing an event system so components can subscribe to authentication changes

2. **Token Refresh**: Implement token refresh mechanism for longer sessions

3. **Session Timeout**: Add automatic session timeout and warning for security
