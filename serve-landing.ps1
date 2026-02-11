# Serve Landing Page
Write-Host "🚀 Starting Ethileo Events Pro Landing Page..." -ForegroundColor Green
Write-Host ""

$port = 8080

# Check if port is in use
$portInUse = Get-NetTCPConnection -LocalPort $port -ErrorAction SilentlyContinue

if ($portInUse) {
    Write-Host "⚠️  Port $port is in use. Trying port 8090..." -ForegroundColor Yellow
    $port = 8090
}

Write-Host "📍 Starting server on http://localhost:$port" -ForegroundColor Cyan
Write-Host "🌐 Opening browser..." -ForegroundColor Cyan
Write-Host ""
Write-Host "Press Ctrl+C to stop the server" -ForegroundColor Yellow
Write-Host ""

# Start simple HTTP server
$publicPath = Join-Path $PSScriptRoot "public"
Set-Location $publicPath

# Open browser after 2 seconds
Start-Job -ScriptBlock {
    Start-Sleep -Seconds 2
    Start-Process "http://localhost:$using:port"
} | Out-Null

# Start Python HTTP server (most systems have Python installed)
if (Get-Command python -ErrorAction SilentlyContinue) {
    python -m http.server $port
} elseif (Get-Command python3 -ErrorAction SilentlyContinue) {
    python3 -m http.server $port
} else {
    Write-Host "❌ Python not found. Opening file directly..." -ForegroundColor Red
    Start-Process (Join-Path $publicPath "index.html")
}
