/**
 * Utility functions for login debugging
 */

// Kiểm tra các URL redirect
function checkURL(urlStr) {
    console.group('URL Check: ' + urlStr);
    
    try {        // Check for absolute paths
        const hasProtocol = urlStr.includes('://');
        const hasWindowsDrive = urlStr.match(/^[A-Za-z]:/);
        const hasBackslash = urlStr.includes('\\');
        const hasLeadingSlash = urlStr.startsWith('/');
        const hasDirectoryTraversal = urlStr.includes('..');
          console.log('Has protocol (://): ', hasProtocol);
        console.log('Has Windows drive (C:): ', !!hasWindowsDrive);
        console.log('Has backslash (\\): ', hasBackslash);
        console.log('Has leading slash (/): ', hasLeadingSlash);
        console.log('Has directory traversal (..): ', hasDirectoryTraversal);
        
        // Check file extension
        const hasExtension = urlStr.includes('.');
        const isPHP = urlStr.endsWith('.php');
        const isHTML = urlStr.endsWith('.html');
        
        console.log('Has extension: ', hasExtension);
        console.log('Is PHP: ', isPHP);
        console.log('Is HTML: ', isHTML);
        
        // Apply sanitization rules
        let sanitized = urlStr;
          // Extract just the filename if absolute path or contains directory traversal
        if (hasProtocol || hasWindowsDrive || hasLeadingSlash || hasBackslash || hasDirectoryTraversal) {
            const parts = sanitized.split(/[\/\\]/);
            sanitized = parts[parts.length - 1] || 'index.html';
            console.log('Extracted filename: ', sanitized);
        }
        
        // Convert PHP to HTML
        if (isPHP) {
            sanitized = sanitized.replace('.php', '.html');
            console.log('Converted to HTML: ', sanitized);
        }
        
        // Add .html if no extension
        if (!sanitized.includes('.')) {
            sanitized += '.html';
            console.log('Added .html extension: ', sanitized);
        }
        
        console.log('Final sanitized URL: ', sanitized);
    } catch (e) {
        console.error('Error analyzing URL: ', e);
    }
    
    console.groupEnd();
}

// Test URL sanitization
function testURLs() {
    const testUrls = [
        'index.html',
        'index.php',
        'profile',
        '/profile.php',
        'C:\\xampp\\htdocs\\confab-web-oasis\\index.php',
        'http://localhost/index.php',
        'http://localhost/confab-web-oasis/index.php',
        '../../index.php'
    ];
    
    console.group('Test URL Sanitization');
    testUrls.forEach(url => {
        checkURL(url);
    });
    console.groupEnd();
}

// Add a debug button
function addDebugButton() {
    const btn = document.createElement('button');
    btn.textContent = 'Test URLs';
    btn.style.position = 'fixed';
    btn.style.bottom = '10px';
    btn.style.left = '10px';
    btn.style.zIndex = '9999';
    btn.style.fontSize = '12px';
    btn.style.padding = '5px';
    btn.style.opacity = '0.7';
    btn.onclick = testURLs;
    
    document.body.appendChild(btn);
}

// Run on load
document.addEventListener('DOMContentLoaded', function() {
    // Only add in development
    if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
        addDebugButton();
        
        // Check current page URL parameters
        const urlParams = new URLSearchParams(window.location.search);
        const redirect = urlParams.get('redirect');
        if (redirect) {
            console.log('Found redirect parameter:', redirect);
            checkURL(redirect);
        }
    }
});
