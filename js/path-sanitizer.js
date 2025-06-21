/**
 * Path sanitizer for preventing bad redirects
 * This script automatically checks and corrects the current URL if it contains
 * problematic paths like absolute Windows paths or PHP file references
 * 
 * ĐÂY LÀ SCRIPT QUAN TRỌNG NHẤT - CHẠY TRƯỚC KHI BẤT KỲ SCRIPT NÀO KHÁC CHẠY
 */

(function() {
    // Thêm biến global vào window để các script khác có thể truy cập
    window.appConfig = {
        basePath: '/confab-web-oasis/',
        apiBasePath: '/confab-web-oasis/api/',
        debug: true
    };
    
    // Function to run immediately when script loads
    function checkAndFixCurrentUrl() {
        const currentUrl = window.location.href;
        console.log('PathSanitizer - Checking URL:', currentUrl);
        
    // Check if URL contains problematic Windows path patterns only
        const hasWindowsPath = currentUrl.includes(':\\') || currentUrl.match(/\/[A-Za-z]:/);
        const hasAbsolutePath = currentUrl.match(/\/\/[^/]+\/[A-Za-z]:/);
        
        // Only redirect if problematic patterns found
        if (hasWindowsPath || hasAbsolutePath) {
            console.error('PathSanitizer - Detected bad URL pattern:', {
                hasWindowsPath,
                hasAbsolutePath
            });
            
            // Get current origin
            const origin = window.location.origin;
              // Get just the filename from the path, ignoring Windows absolute paths
            const pathParts = window.location.pathname.split(/[\/\\]/);
            const fileName = pathParts[pathParts.length - 1] || 'index.html';
            
            // Use the file name as is - no need to replace extensions
            const correctedFileName = fileName;
            
            // Preserve query string and hash if any
            const queryString = window.location.search || '';
            const hash = window.location.hash || '';
            
            // Construct the correct URL
            const basePath = '/confab-web-oasis/'; // Adjust for your installation path
            const correctedUrl = origin + basePath + correctedFileName + queryString + hash;
            
            console.log('PathSanitizer - Redirecting to correct URL:', correctedUrl);
            
            // Perform the redirect
            window.location.replace(correctedUrl);
            return true; // Redirect happened
        }
        
        return false; // No redirect needed
    }
    
    // Run the check immediately
    // Using setTimeout to ensure this runs after all other scripts have loaded
    setTimeout(() => {
        const redirectHappened = checkAndFixCurrentUrl();
        if (!redirectHappened) {
            console.log('PathSanitizer - URL is clean, no redirect needed');
        }
    }, 0);
})();
