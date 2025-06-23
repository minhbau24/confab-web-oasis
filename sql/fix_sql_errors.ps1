# ========================================================
# PowerShell Script to Fix SQL Errors
# Sửa lỗi tự động trong file schema_complete.sql
# ========================================================

Write-Host "Đang sửa lỗi SQL..." -ForegroundColor Green

# Backup file gốc
$sourceFile = "schema_complete.sql"
$backupFile = "schema_complete_backup_$(Get-Date -Format 'yyyyMMdd_HHmmss').sql"

if (Test-Path $sourceFile) {
    Copy-Item $sourceFile $backupFile
    Write-Host "Đã backup file gốc thành: $backupFile" -ForegroundColor Yellow
    
    # Đọc nội dung file
    $content = Get-Content $sourceFile -Raw -Encoding UTF8
    
    # Fix 1: Thêm DELETE statements để tránh duplicate
    $content = $content -replace "-- Dữ liệu mẫu cho bảng user_activity_logs`r?`nINSERT INTO", "-- Xóa dữ liệu cũ để tránh duplicate entries`nDELETE FROM \`user_activity_logs\`;`nDELETE FROM \`invoices\`;`nDELETE FROM \`invoice_items\`;`nDELETE FROM \`transactions\`;`nDELETE FROM \`error_logs\`;`n`n-- Dữ liệu mẫu cho bảng user_activity_logs`nINSERT INTO"
    
    # Fix 2: Xóa phần duplicate invoices và transactions (line 2141+)
    $content = $content -replace "-- Dữ liệu mẫu cho bảng invoices`r?`nINSERT INTO \`invoices\`.*?'TXN-2024-002'.*?NULL\);`r?`n`r?`n-- Dữ liệu mẫu cho bảng error_logs", "-- Dữ liệu mẫu cho bảng error_logs"
    
    # Ghi lại file
    Set-Content $sourceFile $content -Encoding UTF8
    
    Write-Host "Đã sửa xong các lỗi duplicate entries!" -ForegroundColor Green
    Write-Host "Còn lại cần sửa thủ công lỗi column count mismatch trong schedule_sessions." -ForegroundColor Yellow
    
} else {
    Write-Host "Không tìm thấy file $sourceFile" -ForegroundColor Red
}

Write-Host "Hoàn thành!" -ForegroundColor Green
