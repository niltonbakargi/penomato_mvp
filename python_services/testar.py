"""
Script de teste rapido do servico de embeddings.
Roda enquanto o uvicorn esta ativo em outra janela.

Uso: python_services/venv/Scripts/python python_services/testar.py
"""

import base64
import requests
from PIL import Image, ImageDraw
import io

BASE_URL = "http://localhost:8000"

def imagem_teste_b64(cor=(34, 139, 34)):
    """Gera uma imagem solida simples para teste."""
    img = Image.new("RGB", (224, 224), color=cor)
    draw = ImageDraw.Draw(img)
    draw.ellipse([50, 50, 174, 174], fill=(0, 100, 0))
    buf = io.BytesIO()
    img.save(buf, format="JPEG")
    return base64.b64encode(buf.getvalue()).decode()

def testar():
    print("=== Teste do servico Penomato Embeddings ===\n")

    # 1. Health check
    print("1. Health check...")
    r = requests.get(f"{BASE_URL}/health")
    print(f"   {r.json()}\n")

    # 2. Embed — especie 1, folha
    print("2. Gerando perfil visual (especie_id=1, parte=folha)...")
    r = requests.post(f"{BASE_URL}/embed", json={
        "imagem_b64":    imagem_teste_b64((34, 139, 34)),
        "especie_id":    1,
        "parte":         "folha",
        "colaborador_id": 1,
        "fonte":         "teste_local",
    })
    print(f"   {r.json()}\n")

    # 3. Embed — especie 2, folha
    print("3. Gerando perfil visual (especie_id=2, parte=folha)...")
    r = requests.post(f"{BASE_URL}/embed", json={
        "imagem_b64":    imagem_teste_b64((0, 80, 0)),
        "especie_id":    2,
        "parte":         "folha",
        "colaborador_id": 1,
        "fonte":         "teste_local",
    })
    print(f"   {r.json()}\n")

    # 4. Stats
    print("4. Stats da especie 1...")
    r = requests.get(f"{BASE_URL}/stats/1")
    print(f"   {r.json()}\n")

    # 5. Search
    print("5. Buscando especie por similaridade...")
    r = requests.post(f"{BASE_URL}/search", json={
        "imagem_b64": imagem_teste_b64((30, 130, 30)),
        "parte":      "folha",
        "top_k":      3,
    })
    resultado = r.json()
    print(f"   Resultados: {resultado['resultados']}\n")

    print("=== Todos os testes concluidos ===")

if __name__ == "__main__":
    testar()
