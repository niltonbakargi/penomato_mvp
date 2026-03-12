# Script: fix-claude-path.ps1
# Descrição: Adiciona o Claude Code ao PATH do Windows (para instalações via NPM)

Write-Host "🔧 Corrigindo PATH para o Claude Code..." -ForegroundColor Cyan

# 1. Encontrar onde o NPM instala pacotes globais
$npmPrefix = npm config get prefix
$claudePath = Join-Path $npmPrefix "node_modules\@anthropic-ai\claude-code\bin"

Write-Host "📁 Pasta de instalação do Claude: $claudePath" -ForegroundColor Yellow

# 2. Verificar se o claude.cmd existe
if (Test-Path "$claudePath\claude.cmd") {
    Write-Host "✅ Arquivo claude.cmd encontrado!" -ForegroundColor Green
    
    # 3. Adicionar ao PATH do usuário
    $userPath = [Environment]::GetEnvironmentVariable("Path", "User")
    
    if ($userPath -notlike "*$npmPrefix*") {
        $newPath = "$userPath;$npmPrefix"
        [Environment]::SetEnvironmentVariable("Path", $newPath, "User")
        Write-Host "✅ Caminho adicionado ao PATH do usuário: $npmPrefix" -ForegroundColor Green
    } else {
        Write-Host "✅ Caminho já existe no PATH" -ForegroundColor Green
    }
    
    # 4. Atualizar o PATH da sessão atual
    $env:Path = [Environment]::GetEnvironmentVariable("Path", "Machine") + ";" + [Environment]::GetEnvironmentVariable("Path", "User")
    
    Write-Host ""
    Write-Host "🎉 CONCLUÍDO!" -ForegroundColor Green
    Write-Host "Agora você pode usar 'claude' no PowerShell." -ForegroundColor Cyan
    Write-Host ""
    Write-Host "Teste com: claude --version" -ForegroundColor Yellow
    
} else {
    Write-Host "❌ Arquivo claude.cmd não encontrado em: $claudePath" -ForegroundColor Red
    Write-Host ""
    Write-Host "Possíveis causas:" -ForegroundColor Yellow
    Write-Host "  • O Claude Code não foi instalado via NPM" -ForegroundColor Yellow
    Write-Host "  • A instalação está em outro local" -ForegroundColor Yellow
    Write-Host ""
    Write-Host "Para verificar onde está instalado:" -ForegroundColor Cyan
    Write-Host "  Get-Command claude -ErrorAction SilentlyContinue" -ForegroundColor White
}