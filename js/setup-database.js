// JavaScript for setup-database.html

document.addEventListener('DOMContentLoaded', function() {
    // Show setup actions
    document.getElementById('setup-actions').style.display = 'block';
    
    // Setup event listeners
    document.getElementById('btn-setup-db').addEventListener('click', setupDatabase);
    
    // Check if database is already set up
    checkDatabaseStatus();
});

/**
 * Check database status to see if it's already set up
 */
async function checkDatabaseStatus() {
    try {
        const response = await fetch('api/conferences.php');
        if (response.ok) {
            // If we can access conferences API, database is likely set up
            showDatabaseSetupSuccess();
            return;
        }
    } catch (error) {
        console.log('Database may not be set up yet:', error);
    }
    
    // If we reach here, database may not be set up
    const statusDiv = document.getElementById('setup-status');
    statusDiv.innerHTML = `
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle me-2"></i>
            Cơ sở dữ liệu có thể chưa được thiết lập. Nhấn nút "Thiết lập cơ sở dữ liệu" để tiếp tục.
        </div>
    `;
}

/**
 * Setup database by calling the API
 */
async function setupDatabase() {
    const statusDiv = document.getElementById('setup-status');
    
    try {
        // Show loading
        statusDiv.innerHTML = `
            <div class="alert alert-info">
                <i class="fas fa-spinner fa-spin me-2"></i>
                Đang thiết lập cơ sở dữ liệu...
            </div>
        `;
        
        // Call API to setup database
        const response = await fetch('api/setup_database.php');
        const result = await response.json();
        
        if (result.success) {
            showDatabaseSetupSuccess();
        } else {
            // Show error
            let messageHtml = '';
            result.messages.forEach(msg => {
                messageHtml += `<p>${msg}</p>`;
            });
            
            statusDiv.innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-times-circle me-2"></i>
                    <h4>Thiết lập thất bại</h4>
                    ${messageHtml}
                </div>
            `;
            
            // Show troubleshooting info
            document.getElementById('setup-instructions').style.display = 'block';
        }
        
    } catch (error) {
        // Show error
        statusDiv.innerHTML = `
            <div class="alert alert-danger">
                <i class="fas fa-times-circle me-2"></i>
                <h4>Thiết lập thất bại</h4>
                <p>Lỗi: ${error.message}</p>
            </div>
        `;
        
        // Show troubleshooting info
        document.getElementById('setup-instructions').style.display = 'block';
    }
}

/**
 * Show success message and enable sample data import button
 */
function showDatabaseSetupSuccess() {
    const statusDiv = document.getElementById('setup-status');
    
    statusDiv.innerHTML = `
        <div class="alert alert-success">
            <i class="fas fa-check-circle me-2"></i>
            <h4>Thành công!</h4>
            <p>Cấu trúc cơ sở dữ liệu đã được thiết lập thành công.</p>
            <p>Bạn có thể tiếp tục nhập dữ liệu mẫu hoặc quay lại trang chủ.</p>
        </div>
    `;
    
    // Show import sample data button
    const importBtn = document.getElementById('btn-import-sample');
    importBtn.style.display = 'inline-block';
    importBtn.addEventListener('click', importSampleData);
}

/**
 * Import sample data by calling the API
 */
async function importSampleData() {
    const statusDiv = document.getElementById('setup-status');
    
    try {
        // Show loading
        statusDiv.innerHTML = `
            <div class="alert alert-info">
                <i class="fas fa-spinner fa-spin me-2"></i>
                Đang nhập dữ liệu mẫu...
            </div>
        `;
        
        // Call API to import sample data
        const response = await fetch('api/import_sample_data.php');
        const result = await response.json();
        
        if (result.success) {
            // Show success
            statusDiv.innerHTML = `
                <div class="alert alert-success">
                    <i class="fas fa-check-circle me-2"></i>
                    <h4>Thành công!</h4>
                    <p>Dữ liệu mẫu đã được nhập thành công.</p>
                    <p>Bạn có thể tiếp tục khám phá hệ thống bằng cách quay lại trang chủ.</p>
                </div>
            `;
            
            // Hide import button to prevent double import
            document.getElementById('btn-import-sample').style.display = 'none';
            
        } else {
            // Show error
            let messageHtml = '';
            result.messages.forEach(msg => {
                messageHtml += `<p>${msg}</p>`;
            });
            
            statusDiv.innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-times-circle me-2"></i>
                    <h4>Nhập dữ liệu mẫu thất bại</h4>
                    ${messageHtml}
                </div>
            `;
        }
        
    } catch (error) {
        // Show error
        statusDiv.innerHTML = `
            <div class="alert alert-danger">
                <i class="fas fa-times-circle me-2"></i>
                <h4>Nhập dữ liệu mẫu thất bại</h4>
                <p>Lỗi: ${error.message}</p>
            </div>
        `;
    }
}
