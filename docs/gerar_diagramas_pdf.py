"""
Gera um PDF por diagrama lendo direto do diagramas_bmf.html.
Cada card vira um screenshot e depois um PDF no tamanho exato do conteúdo.

Dependências:
  pip install playwright pillow
  python -m playwright install chromium
"""

import asyncio
from pathlib import Path
from PIL import Image

BASE_DIR  = Path(__file__).parent
HTML_FILE = BASE_DIR / "diagramas_bmf.html"
OUTPUT_DIR = BASE_DIR / "pdf_diagramas"
OUTPUT_DIR.mkdir(exist_ok=True)

NOMES = [
    "01_Diagrama_de_Contexto",
    "02_Atores_e_Casos_de_Uso",
    "03_Fluxo_Registrar_Matriz",
    "04_Fluxo_Consultar_Mapa",
    "05_Modelo_de_Dados_ER",
    "06_Arquitetura_do_Sistema",
]

async def gerar():
    from playwright.async_api import async_playwright

    print("Abrindo o HTML...")

    async with async_playwright() as p:
        browser = await p.chromium.launch()
        # Viewport largo para o Mermaid renderizar bem
        page = await browser.new_page(viewport={"width": 1600, "height": 900})
        await page.goto(f"file:///{HTML_FILE.as_posix()}")

        # Aguarda todos os SVGs renderizarem
        await page.wait_for_function(
            "() => document.querySelectorAll('.mermaid svg').length >= 6",
            timeout=20000
        )
        await page.wait_for_timeout(1000)

        cards = await page.query_selector_all(".card")
        print(f"  {len(cards)} cards encontrados")

        for i, card in enumerate(cards):
            nome = NOMES[i] if i < len(NOMES) else f"0{i+1}_diagrama"
            png_path = OUTPUT_DIR / f"BMF_{nome}.png"
            pdf_path = OUTPUT_DIR / f"BMF_{nome}.pdf"

            # Screenshot do card inteiro, no tamanho real
            await card.screenshot(path=str(png_path), type="png")

            # Converte PNG → PDF mantendo dimensões
            img = Image.open(png_path)
            w_pt = img.width  * 0.75   # px → pontos (96dpi → 72dpi)
            h_pt = img.height * 0.75
            img_rgb = img.convert("RGB")
            img_rgb.save(
                str(pdf_path),
                "PDF",
                resolution=96,
                pagesize=(w_pt, h_pt),
            )

            png_path.unlink()  # remove o PNG temporário
            print(f"  OK: {pdf_path.name}")

        await browser.close()

    print(f"\nPDFs salvos em: {OUTPUT_DIR}")


if __name__ == "__main__":
    asyncio.run(gerar())
