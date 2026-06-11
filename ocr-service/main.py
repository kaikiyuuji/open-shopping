"""Microserviço de OCR para cupons/notas fiscais.

Recebe uma imagem, extrai o texto com Tesseract e devolve os itens
reconhecidos. Não persiste nada — o app Laravel é o dono dos dados.

Rodar:
    uvicorn main:app --port 8100
"""

from __future__ import annotations

import io
import os
import shutil
from pathlib import Path

import pytesseract
from fastapi import FastAPI, File, HTTPException, UploadFile
from PIL import Image, ImageOps

from parser import parse_cupom

# Localiza o binário do Tesseract (env > PATH > caminho padrão do Windows).
_TESSERACT_CMD = (
    os.environ.get("TESSERACT_CMD")
    or shutil.which("tesseract")
    or r"C:\Program Files\Tesseract-OCR\tesseract.exe"
)
pytesseract.pytesseract.tesseract_cmd = _TESSERACT_CMD

# Usa o pacote de idioma português local, se presente.
# TESSDATA_PREFIX em vez de --tessdata-dir: o quoting do path quebra no Windows.
_TESSDATA_DIR = Path(__file__).parent / "tessdata"
if (_TESSDATA_DIR / "por.traineddata").exists():
    os.environ["TESSDATA_PREFIX"] = str(_TESSDATA_DIR)
    _LANG = "por"
else:
    _LANG = "eng"

app = FastAPI(title="OpenShopping OCR", version="1.0.0")


@app.get("/health")
def health() -> dict:
    return {"status": "ok", "lang": _LANG}


@app.post("/ocr")
async def ocr(arquivo: UploadFile = File(...)) -> dict:
    if not (arquivo.content_type or "").startswith("image/"):
        raise HTTPException(status_code=422, detail="Envie um arquivo de imagem.")

    conteudo = await arquivo.read()
    try:
        imagem = Image.open(io.BytesIO(conteudo))
    except Exception:
        raise HTTPException(status_code=422, detail="Imagem inválida ou corrompida.")

    # Pré-processamento leve: escala de cinza + contraste automático.
    imagem = ImageOps.autocontrast(imagem.convert("L"))

    try:
        texto = pytesseract.image_to_string(imagem, lang=_LANG)
    except pytesseract.TesseractNotFoundError:
        raise HTTPException(
            status_code=500,
            detail=f"Tesseract não encontrado em '{_TESSERACT_CMD}'. "
            "Defina a variável de ambiente TESSERACT_CMD.",
        )

    return {"texto": texto, "itens": parse_cupom(texto)}
