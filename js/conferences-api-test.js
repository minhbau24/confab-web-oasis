// Test script to verify conferences.html works with API data

console.log("=== Conferences API Test ===");

// Function to check if API endpoint is accessible
async function testAPI() {
    try {
        console.log("Testing API endpoint...");
        const response = await fetch('api/conferences.php');
        
        if (!response.ok) {
            throw new Error(`HTTP error! Status: ${response.status}`);
        }
        
        const result = await response.json();
        console.log("API Response:", result);
        
        if (result.status && result.data) {
            console.log("✅ API working correctly!");
            console.log(`Found ${result.data.length} conferences`);
            return true;
        } else {
            console.error("❌ API returned success but no data");
            return false;
        }
    } catch (error) {
        console.error("❌ API test failed:", error);
        return false;
    }
}

// Check if required scripts are loaded
function checkScripts() {
    console.log("Checking required scripts...");
    
    const scripts = [
        { name: 'api-helper.js', variable: 'typeof apiGet === "function"' },
        { name: 'auth.js', variable: 'typeof getCurrentUser === "function"' }
    ];
    
    let allLoaded = true;
    
    scripts.forEach(script => {
        try {
            const isLoaded = eval(script.variable);
            console.log(`${script.name}: ${isLoaded ? '✅ Loaded' : '❌ Not loaded'}`);
            if (!isLoaded) allLoaded = false;
        } catch (e) {
            console.error(`${script.name}: ❌ Error checking`, e);
            allLoaded = false;
        }
    });
    
    return allLoaded;
}

// Run tests when DOM is loaded
document.addEventListener('DOMContentLoaded', async function() {
    console.log("Running tests...");
    
    const scriptsLoaded = checkScripts();
    console.log("Scripts loaded:", scriptsLoaded ? '✅ Yes' : '❌ No');
    
    const apiWorking = await testAPI();
    console.log("API working:", apiWorking ? '✅ Yes' : '❌ No');
    
    if (scriptsLoaded && apiWorking) {
        console.log("✅ All tests passed! The page should work correctly.");
    } else {
        console.error("❌ Some tests failed. Check the console for details.");
    }
});
