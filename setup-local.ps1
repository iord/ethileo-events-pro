# Ethileo Events Pro - Local Setup Script

Write-Host "🚀 Setting up Ethileo Events Pro locally..." -ForegroundColor Green
Write-Host ""

# Check if Docker is running
Write-Host "📦 Checking Docker..." -ForegroundColor Cyan
$dockerRunning = Get-Process "Docker Desktop" -ErrorAction SilentlyContinue
if (-not $dockerRunning) {
    Write-Host "⚠️  Docker Desktop is not running. Starting it..." -ForegroundColor Yellow
    Start-Process "C:\Program Files\Docker\Docker\Docker Desktop.exe"
    Write-Host "Waiting for Docker to start (60 seconds)..." -ForegroundColor Yellow
    Start-Sleep -Seconds 60
}

Write-Host "✅ Docker is running" -ForegroundColor Green
Write-Host ""

# Start containers
Write-Host "🐳 Starting Docker containers..." -ForegroundColor Cyan
docker-compose up -d

Write-Host ""
Write-Host "⏳ Waiting for containers to be ready (30 seconds)..." -ForegroundColor Cyan
Start-Sleep -Seconds 30

Write-Host ""
Write-Host "✅ Setup complete!" -ForegroundColor Green
Write-Host ""
Write-Host "📍 Access your site at:" -ForegroundColor Cyan
Write-Host "   WordPress:   http://localhost:8080" -ForegroundColor White
Write-Host "   Admin:       http://localhost:8080/wp-admin" -ForegroundColor White
Write-Host "   PHPMyAdmin:  http://localhost:8081" -ForegroundColor White
Write-Host ""
Write-Host "🔑 Default credentials:" -ForegroundColor Cyan
Write-Host "   Username: admin" -ForegroundColor White
Write-Host "   Password: admin" -ForegroundColor White
Write-Host ""
Write-Host "💡 First time setup:" -ForegroundColor Cyan
Write-Host "   1. Go to http://localhost:8080" -ForegroundColor White
Write-Host "   2. Follow WordPress installation wizard" -ForegroundColor White
Write-Host "   3. Go to Plugins and activate 'Ethileo Events Pro'" -ForegroundColor White
Write-Host ""

# Open browser
Write-Host "🌐 Opening browser..." -ForegroundColor Cyan
Start-Process "http://localhost:8080"
