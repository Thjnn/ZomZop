# Tạo link công khai tạm thời cho ZomZop bằng Cloudflare Quick Tunnel.
# Cách dùng: nhấp đúp share.bat (hoặc chạy: powershell -ExecutionPolicy Bypass -File share.ps1)
# Hướng dẫn chi tiết: docs/huong-dan-chia-se-link-tam-thoi.md

param(
    [int]$Port = 8000
)

$ErrorActionPreference = 'Stop'
Set-Location $PSScriptRoot

$cloudflared = 'E:\laragon\bin\cloudflared\cloudflared.exe'
$php = Get-ChildItem 'E:\laragon\bin\php\php-*\php.exe' | Sort-Object FullName -Descending | Select-Object -First 1 -ExpandProperty FullName

if (-not $php) { throw 'Không tìm thấy php.exe trong E:\laragon\bin\php' }
if (-not (Test-Path $cloudflared)) { throw "Không tìm thấy $cloudflared (xem mục Cài đặt trong docs/huong-dan-chia-se-link-tam-thoi.md)" }
if (Test-Path 'public\hot') { throw 'Đang chạy "npm run dev". Hãy tắt nó rồi chạy "npm run build" trước khi chia sẻ.' }
if (-not (Test-Path 'public\build\manifest.json')) { throw 'Chưa có public\build. Hãy chạy "npm run build" trước.' }
if (Get-NetTCPConnection -State Listen -LocalPort $Port -ErrorAction SilentlyContinue) { throw "Cổng $Port đang bị chiếm. Thử: share.bat -Port 8001" }

# Người lạ sẽ truy cập được trang, nên tắt trang lỗi chi tiết (không lộ .env, đường dẫn, SQL).
$env:APP_DEBUG = 'false'

$server = Start-Process -FilePath $php `
    -ArgumentList 'artisan', 'serve', '--host=127.0.0.1', "--port=$Port", '--no-reload' `
    -WindowStyle Hidden -PassThru

try {
    Write-Host ''
    Write-Host "Web đang chạy nội bộ tại http://127.0.0.1:$Port" -ForegroundColor Green
    Write-Host 'Link công khai (https://....trycloudflare.com) sẽ hiện trong khung bên dưới.' -ForegroundColor Green
    Write-Host 'Nhấn Ctrl+C để tắt link.' -ForegroundColor Yellow
    Write-Host ''

    # cloudflared ghi log ra stderr; không để PowerShell coi đó là lỗi.
    $ErrorActionPreference = 'Continue'
    & $cloudflared tunnel --url "http://127.0.0.1:$Port"
}
finally {
    # artisan serve sinh thêm tiến trình php con, nên phải tắt cả cây tiến trình.
    cmd /c "taskkill /PID $($server.Id) /T /F >nul 2>&1"
    Write-Host 'Đã tắt link chia sẻ.' -ForegroundColor Yellow
}
