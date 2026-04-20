@echo off
echo.
echo ==========================================
echo   OLLAMA — Modelos instalados
echo ==========================================
echo.

curl -s http://localhost:11434/api/tags > temp_ollama.json 2>&1

if errorlevel 1 (
    echo  ERRO: Ollama nao esta rodando.
    echo  Inicie o Ollama e tente novamente.
    goto fim
)

echo  Modelos disponiveis:
echo.

:: Mostra os modelos de forma legivel
curl -s http://localhost:11434/api/tags | python -c "
import sys, json
try:
    data = json.load(sys.stdin)
    models = data.get('models', [])
    if not models:
        print('  Nenhum modelo instalado.')
    for m in models:
        name = m.get('name', '')
        size = m.get('size', 0)
        size_gb = size / (1024**3)
        modified = m.get('modified_at', '')[:10]
        print(f'  {name:<30} {size_gb:.1f} GB    instalado: {modified}')
except:
    print('  Erro ao ler resposta do Ollama.')
"

echo.
echo ==========================================
echo   Modelos configurados no Penomato
echo ==========================================
echo.
echo   Texto:  qwen3:8b
echo   Visao:  qwen2.5vl:7b
echo.
echo   Para trocar, edite config/app.php
echo.

:fim
if exist temp_ollama.json del temp_ollama.json
pause
