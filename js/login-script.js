/**
 * Script xử lý đăng nhập
 */
document.addEventListener('DOMContentLoaded', function () {
    const loginForm = document.getElementById('login-form');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const rememberMeCheckbox = document.getElementById('remember-me');
    const loginAlert = document.getElementById('login-alert');    // Initialize the login form
    console.log('Login script loaded');

    // Handle login form submission
    loginForm.addEventListener('submit', function (e) {
        e.preventDefault();

        // Reset UI state
        emailInput.classList.remove('is-invalid');
        passwordInput.classList.remove('is-invalid');
        loginAlert.style.display = 'none';

        const email = emailInput.value.trim();
        const password = passwordInput.value;
        const rememberMe = rememberMeCheckbox.checked;

        // Basic validation
        let isValid = true;

        if (!email) {
            showInputError(emailInput, 'email-error', 'Vui lòng nhập địa chỉ email');
            isValid = false;
        } else if (!isValidEmail(email)) {
            showInputError(emailInput, 'email-error', 'Địa chỉ email không hợp lệ');
            isValid = false;
        }

        if (!password) {
            showInputError(passwordInput, 'password-error', 'Vui lòng nhập mật khẩu');
            isValid = false;
        }

        if (isValid) {
            // Call login function from auth.js
            if (typeof login === 'function') {
                console.log("Calling login API with:", email, "and remember:", rememberMe);

                login(email, password, rememberMe)
                    .then(result => {
                        console.log("Login API response:", result);

                        if (result.success) {
                            // Show success message
                            showAlert('success', 'Đăng nhập thành công! Đang chuyển hướng...');

                            // Redirect after a short delay
                            setTimeout(function () {
                                try {                                    console.log("Starting redirect process...");
                                    // Default fallback
                                    let redirectUrl = 'index.html';

                                    // Get redirect URL from auth.js if available
                                    if (typeof getRedirectUrl === 'function') {
                                        console.log("Getting redirect URL from getRedirectUrl()");
                                        redirectUrl = getRedirectUrl();
                                        console.log("getRedirectUrl returned:", redirectUrl);
                                    } else {
                                        console.warn("getRedirectUrl function not found, using index.html");
                                    }
                                    
                                    // Simple redirect validation - ensure we have .html extension
                                    if (!redirectUrl || !redirectUrl.trim()) {
                                        redirectUrl = 'index.html';
                                    }
                                    
                                    // Ensure we have .html extension
                                    if (!redirectUrl.endsWith('.html')) {
                                        redirectUrl = 'index.html';
                                    }
                                    
                                    console.log("Final redirect URL:", redirectUrl);

                                    console.log("Absolute final redirect URL:", redirectUrl);
                                    window.location.href = redirectUrl;
                                } catch (error) {
                                    console.error("Error during redirect:", error);
                                    window.location.href = 'index.html';
                                }
                            }, 1500);
                        } else {
                            // Show error message
                            console.error("Login failed:", result.error);
                            showAlert('danger', result.error || 'Đăng nhập thất bại. Vui lòng kiểm tra lại thông tin.');
                        }
                    })
                    .catch(error => {
                        console.error('Login API error:', error);
                        showAlert('danger', 'Có lỗi xảy ra khi đăng nhập. Vui lòng thử lại sau.');
                    });
            } else {
                // Fallback if auth.js login function is not available
                console.log('Đăng nhập với:', { email, password, rememberMe });
                showAlert('success', 'Đăng nhập thành công! Chuyển hướng đến trang chủ...');

                setTimeout(() => {
                    window.location.href = 'index.html';
                }, 1500);
            }
        }
    });

    // Helper functions
    function showInputError(inputElement, errorId, message) {
        inputElement.classList.add('is-invalid');
        document.getElementById(errorId).textContent = message;
    }

    function showAlert(type, message) {
        loginAlert.className = `alert alert-${type}`;
        loginAlert.textContent = message;
        loginAlert.style.display = 'block';
    }

    function isValidEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }

    // Check if there's an authentication error in URL parameters
    const urlParams = new URLSearchParams(window.location.search);
    const authError = urlParams.get('error');
    if (authError) {
        showAlert('danger', decodeURIComponent(authError));
    }

    // Check if there's a redirect message
    const message = urlParams.get('message');
    if (message) {
        showAlert('info', decodeURIComponent(message));
    }
});
