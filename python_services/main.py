"""
Penomato — Servico de Embeddings (Impressao Digital Vegetal)
FastAPI + DINOv2 + FAISS

Rodar: python_services/venv/Scripts/uvicorn python_services.main:app --port 8000 --reload
"""

import base64
import io
import json
import os
import secrets
from pathlib import Path
from typing import Optional

import faiss
import numpy as np
from fastapi import FastAPI, HTTPException, Security
from fastapi.security.api_key import APIKeyHeader
from fastapi.middleware.cors import CORSMiddleware
from PIL import Image
from pydantic import BaseModel
from transformers import AutoImageProcessor, AutoModel
import torch

# ── Caminhos ──────────────────────────────────────────────────────────────────
BASE_DIR   = Path(__file__).parent
INDEX_PATH = BASE_DIR / "storage" / "faiss" / "penomato.index"
META_PATH  = BASE_DIR / "storage" / "faiss" / "meta.json"

# ── Autenticação por API key ──────────────────────────────────────────────────
# Defina PENOMATO_API_KEY no ambiente antes de iniciar o serviço.
# Exemplo: export PENOMATO_API_KEY=$(python -c "import secrets; print(secrets.token_hex(32))")
_API_KEY = os.environ.get("PENOMATO_API_KEY", "")
if not _API_KEY:
    raise RuntimeError("PENOMATO_API_KEY não definida. Configure a variável de ambiente antes de iniciar.")

_api_key_header = APIKeyHeader(name="X-API-Key", auto_error=False)

def verificar_api_key(key: Optional[str] = Security(_api_key_header)) -> str:
    if not key or not secrets.compare_digest(key, _API_KEY):
        raise HTTPException(status_code=401, detail="API key inválida ou ausente.")
    return key

# ── App ───────────────────────────────────────────────────────────────────────
app = FastAPI(title="Penomato Embeddings", version="1.0.0")

app.add_middleware(
    CORSMiddleware,
    allow_origins=["http://localhost", "https://penomato.app.br"],
    allow_methods=["*"],
    allow_headers=["*"],
)

# ── Modelos (carregados uma vez na inicialização) ──────────────────────────────
print("[Penomato] Carregando DINOv2...")
processor = AutoImageProcessor.from_pretrained("facebook/dinov2-small")
model     = AutoModel.from_pretrained("facebook/dinov2-small")
model.eval()
DIM = 384  # dinov2-small: 384 dims
print("[Penomato] DINOv2 pronto.")

# ── FAISS index ───────────────────────────────────────────────────────────────
def carregar_index():
    if INDEX_PATH.exists():
        index = faiss.read_index(str(INDEX_PATH))
    else:
        index = faiss.IndexFlatIP(DIM)  # Inner Product (coseno com vetores normalizados)
    return index

def salvar_index(index):
    faiss.write_index(index, str(INDEX_PATH))

def carregar_meta() -> list:
    if META_PATH.exists():
        return json.loads(META_PATH.read_text(encoding="utf-8"))
    return []

def salvar_meta(meta: list):
    META_PATH.write_text(json.dumps(meta, ensure_ascii=False, indent=2), encoding="utf-8")

index = carregar_index()
meta  = carregar_meta()

# ── Helpers ───────────────────────────────────────────────────────────────────
def imagem_para_embedding(img: Image.Image) -> np.ndarray:
    """Recebe PIL Image, retorna vetor normalizado (384 dims)."""
    inputs  = processor(images=img, return_tensors="pt")
    with torch.no_grad():
        outputs = model(**inputs)
    # CLS token
    vetor = outputs.last_hidden_state[:, 0, :].numpy().astype("float32")
    # Normalizar para coseno similarity via inner product
    faiss.normalize_L2(vetor)
    return vetor

def b64_para_imagem(b64: str) -> Image.Image:
    """Decodifica base64 para PIL Image."""
    data = base64.b64decode(b64)
    return Image.open(io.BytesIO(data)).convert("RGB")

# ── Schemas ───────────────────────────────────────────────────────────────────
class EmbedRequest(BaseModel):
    imagem_b64: str          # imagem base64
    especie_id: int
    parte: str               # folha | flor | fruto | caule | habito | semente
    colaborador_id: int
    fonte: Optional[str] = None

class SearchRequest(BaseModel):
    imagem_b64: str
    parte: Optional[str] = None   # filtra por parte se informado
    top_k: int = 5

# ── Endpoints ─────────────────────────────────────────────────────────────────

@app.get("/health")
def health():
    return {
        "status": "ok",
        "total_perfis": len(meta),
        "index_vetores": index.ntotal,
    }


@app.post("/embed")
def embed(req: EmbedRequest, _: str = Security(verificar_api_key)):
    """
    Recebe recorte de parte da planta em base64.
    Extrai embedding DINOv2, salva no FAISS e retorna confirmação.
    """
    global index, meta

    try:
        img = b64_para_imagem(req.imagem_b64)
    except Exception:
        raise HTTPException(status_code=400, detail="Imagem base64 inválida.")

    vetor = imagem_para_embedding(img)

    faiss_id = index.ntotal
    index.add(vetor)
    salvar_index(index)

    entrada = {
        "faiss_id":       faiss_id,
        "especie_id":     req.especie_id,
        "parte":          req.parte,
        "colaborador_id": req.colaborador_id,
        "fonte":          req.fonte,
    }
    meta.append(entrada)
    salvar_meta(meta)

    return {
        "status":    "ok",
        "faiss_id":  faiss_id,
        "especie_id": req.especie_id,
        "parte":     req.parte,
        "total_parte": sum(1 for m in meta if m["especie_id"] == req.especie_id and m["parte"] == req.parte),
    }


@app.post("/search")
def search(req: SearchRequest, _: str = Security(verificar_api_key)):
    """
    Recebe foto de parte da planta em base64.
    Retorna top-K espécies mais similares.
    """
    if index.ntotal == 0:
        return {"resultados": [], "mensagem": "Nenhum perfil cadastrado ainda."}

    try:
        img = b64_para_imagem(req.imagem_b64)
    except Exception:
        raise HTTPException(status_code=400, detail="Imagem base64 inválida.")

    vetor = imagem_para_embedding(img)

    # Busca nos vizinhos mais próximos
    k = min(req.top_k * 10, index.ntotal)  # busca ampla para filtrar por parte
    distancias, indices = index.search(vetor, k)

    vistos = {}
    resultados = []

    for dist, idx in zip(distancias[0], indices[0]):
        if idx < 0 or idx >= len(meta):
            continue
        m = meta[idx]

        # Filtra por parte se solicitado
        if req.parte and m["parte"] != req.parte:
            continue

        especie_id = m["especie_id"]
        if especie_id in vistos:
            continue
        vistos[especie_id] = True

        confianca = round(float(dist) * 100, 1)  # inner product normalizado = coseno [0-1]
        resultados.append({
            "especie_id": especie_id,
            "parte":      m["parte"],
            "confianca":  confianca,
            "faiss_id":   idx,
        })

        if len(resultados) >= req.top_k:
            break

    return {"resultados": resultados}


@app.get("/stats/{especie_id}")
def stats(especie_id: int):
    """Retorna contagem de perfis por parte para uma espécie."""
    partes = ["folha", "flor", "fruto", "caule", "habito", "semente"]
    contagem = {p: 0 for p in partes}
    for m in meta:
        if m["especie_id"] == especie_id and m["parte"] in contagem:
            contagem[m["parte"]] += 1
    return {"especie_id": especie_id, "perfis": contagem}
