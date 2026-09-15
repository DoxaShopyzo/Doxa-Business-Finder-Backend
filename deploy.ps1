param(
    [string]$Message = "Update Doxa Business Finder"
)

Write-Host "🚀 [1/3] Adding and committing files..." -ForegroundColor Cyan
git add .
git commit -m $Message

Write-Host "☁️ [2/3] Pushing to GitHub..." -ForegroundColor Cyan
git push origin main

Write-Host "⚡ [3/3] Triggering automated deployment on cPanel live server..." -ForegroundColor Cyan
try {
    $response = Invoke-RestMethod -Uri "https://businessfinder.doxainfoplus.com/deploy.php?secret=DoxaDeploy2026" -Method Get -TimeoutSec 30
    Write-Host "✅ DEPLOY SUCCESS: $($response.message)" -ForegroundColor Green
    Write-Host "⏰ Time: $($response.time)" -ForegroundColor Green
} catch {
    Write-Host "⚠️ Webhook triggered (Server processing deployment)" -ForegroundColor Yellow
}
