// Debug script to check for user auth issues

console.log("=== User Authentication Test ===");

document.addEventListener('DOMContentLoaded', function() {
    console.log("Testing user authentication functions...");
    
    // Test 1: Check if window.getCurrentUser is exposed
    console.log("Test 1: window.getCurrentUser exposed?", typeof window.getCurrentUser === 'function' ? "✅ Yes" : "❌ No");
    
    // Test 2: Check isLoggedIn function
    if (typeof window.isLoggedIn === 'function') {
        const loggedIn = window.isLoggedIn();
        console.log("Test 2: User logged in?", loggedIn ? "✅ Yes" : "❌ No");
    } else {
        console.log("Test 2: isLoggedIn function not found ❌");
    }
      // Test 3: Check currentUser object through function
    if (typeof window.getCurrentUser === 'function') {
        try {
            const user = window.getCurrentUser();
            console.log("Test 3a: User from function:", user ? "✅ Found" : "❌ Not found");
            
            if (user) {
                console.log("  - User ID:", user.id);
                console.log("  - User Name:", user.name);
                console.log("  - User Role:", user.role);
            }
        } catch (e) {
            console.error("Error accessing getCurrentUser function:", e);
        }
    } else {
        console.log("Test 3a: getCurrentUser function not found ❌");
    }
    
    // Test 3b: Check direct authCurrentUser object
    if (typeof window.authCurrentUser !== 'undefined') {
        const user = window.authCurrentUser;
        console.log("Test 3b: Direct user object:", user ? "✅ Found" : "❌ Not found");
        
        if (user) {
            console.log("  - User ID:", user.id);
            console.log("  - User Name:", user.name);
            console.log("  - User Role:", user.role);
        }
    } else {
        console.log("Test 3b: authCurrentUser object not found ❌");
    }
    
    // Test 4: Check localStorage
    try {
        const storedUser = localStorage.getItem('user');
        console.log("Test 4: User in localStorage?", storedUser ? "✅ Yes" : "❌ No");
        
        if (storedUser) {
            const parsedUser = JSON.parse(storedUser);
            console.log("  - Stored User ID:", parsedUser.id);
            console.log("  - Stored User Name:", parsedUser.name);
            console.log("  - Stored User Role:", parsedUser.role);
        }
    } catch (e) {
        console.error("Error checking localStorage:", e);
    }
    
    console.log("Authentication tests completed.");
});
