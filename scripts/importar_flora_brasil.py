"""
importar_flora_brasil.py
========================
Lê angiospermsdatabase.xlsx e gymnospermsdatabase.xlsx (REFLORA/JBRJ),
filtra Rank=Espécie + Status=Nome aceito e gera flora_brasil_import.sql
pronto para importar no phpMyAdmin da HostGator.

Dependências: pip install openpyxl
Uso:          python scripts/importar_flora_brasil.py
"""

import openpyxl
import os
import re
from datetime import datetime

# ── Configuração ──────────────────────────────────────────────
BASE_DIR   = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
ARQUIVOS   = [
    os.path.join(BASE_DIR, 'angiospermsdatabase.xlsx'),
    os.path.join(BASE_DIR, 'gymnospermsdatabase.xlsx'),
]
SAIDA_SQL  = os.path.join(BASE_DIR, 'database', 'flora_brasil_import.sql')
LOTE       = 500   # linhas por INSERT (seguro para phpMyAdmin)

# ── Mapeamento de colunas do xlsx ─────────────────────────────
COLS = {
    'id':                0,
    'rank':              1,
    'grupo':             2,
    'familia':           6,
    'genero':            9,
    'epiteto':           10,
    'autor':             14,
    'status':            16,
    'origem':            19,
    'endemica':          20,
    'formas_vida':       24,
    'distr_uf':          27,
    'dom_fitogeografico':28,
    'nomes_vernaculares':32,
}

# ── Helpers ───────────────────────────────────────────────────
def esc(v):
    """Escapa string para SQL — retorna NULL se vazio."""
    if v is None or str(v).strip() == '':
        return 'NULL'
    s = str(v).strip()
    s = s.replace('\\', '\\\\').replace("'", "\\'")
    return f"'{s}'"

def to_date(v):
    """Converte datetime ou string para 'YYYY-MM-DD HH:MM:SS' ou NULL."""
    if v is None:
        return 'NULL'
    if isinstance(v, datetime):
        return f"'{v.strftime('%Y-%m-%d %H:%M:%S')}'"
    try:
        dt = datetime.strptime(str(v)[:19], '%Y-%m-%d %H:%M:%S')
        return f"'{dt.strftime('%Y-%m-%d %H:%M:%S')}'"
    except Exception:
        return 'NULL'

def ocorre(v):
    return '1' if v and str(v).strip().lower() == 'sim' else '0'

# ── Leitura e geração ─────────────────────────────────────────
registros = []

for caminho in ARQUIVOS:
    nome_arq = os.path.basename(caminho)
    print(f'Lendo {nome_arq}...')

    wb = openpyxl.load_workbook(caminho, read_only=True)
    ws = wb['Relatório']

    aceitos = 0
    ignorados = 0

    for i, row in enumerate(ws.iter_rows(min_row=2, values_only=True)):
        rank   = str(row[COLS['rank']] or '').strip()
        status = str(row[COLS['status']] or '').strip()

        dom = str(row[COLS['dom_fitogeografico']] or '')
        if rank != 'Espécie' or status != 'Nome aceito' or 'Cerrado' not in dom:
            ignorados += 1
            continue

        genero  = str(row[COLS['genero']] or '').strip()
        epiteto = str(row[COLS['epiteto']] or '').strip()
        nome_cientifico = f'{genero} {epiteto}'.strip()

        registros.append({
            'id':                 row[COLS['id']],
            'grupo':              row[COLS['grupo']],
            'familia':            row[COLS['familia']],
            'genero':             genero,
            'nome_cientifico':    nome_cientifico,
            'autor':              row[COLS['autor']],
            'origem':             row[COLS['origem']],
            'endemica':           row[COLS['endemica']],
            'formas_vida':        row[COLS['formas_vida']],
            'distr_uf':           row[COLS['distr_uf']],
            'dom_fitogeografico': dom,
            'nomes_vernaculares': row[COLS['nomes_vernaculares']],
        })
        aceitos += 1

    print(f'  Aceitos: {aceitos} | Ignorados: {ignorados} (sinonimos/ranks superiores)')

print(f'\nTotal de registros a importar: {len(registros)}')

# ── Geração do SQL ────────────────────────────────────────────
print(f'Gerando {SAIDA_SQL}...')

COLUNAS_INSERT = (
    '`id`, `grupo`, `familia`, `genero`, `nome_cientifico`, `autor`, '
    '`origem`, `endemica`, `formas_vida`, `distr_uf`, `dom_fitogeografico`, '
    '`nomes_vernaculares`'
)

with open(SAIDA_SQL, 'w', encoding='utf-8') as f:
    f.write('-- ============================================================\n')
    f.write('-- flora_brasil_import.sql\n')
    f.write(f'-- Gerado em: {datetime.now().strftime("%Y-%m-%d %H:%M")}\n')
    f.write(f'-- Registros: {len(registros)} (angiospermas + gimnospermas, nome aceito, rank espécie)\n')
    f.write('-- Fonte: REFLORA/JBRJ — CC-BY\n')
    f.write('-- ============================================================\n\n')
    f.write('SET NAMES utf8mb4;\n')
    f.write('SET foreign_key_checks = 0;\n\n')

    for i in range(0, len(registros), LOTE):
        lote = registros[i:i + LOTE]
        f.write(f'INSERT INTO `flora_brasil_plantas` ({COLUNAS_INSERT}) VALUES\n')

        linhas = []
        for r in lote:
            linha = (
                f"({r['id']}, "
                f"{esc(r['grupo'])}, "
                f"{esc(r['familia'])}, "
                f"{esc(r['genero'])}, "
                f"{esc(r['nome_cientifico'])}, "
                f"{esc(r['autor'])}, "
                f"{esc(r['origem'])}, "
                f"{esc(r['endemica'])}, "
                f"{esc(r['formas_vida'])}, "
                f"{esc(r['distr_uf'])}, "
                f"{esc(r['dom_fitogeografico'])}, "
                f"{esc(r['nomes_vernaculares'])})"
            )
            linhas.append(linha)

        f.write(',\n'.join(linhas))
        f.write(';\n\n')

    f.write('SET foreign_key_checks = 1;\n')

print(f'Concluído. Arquivo gerado: {SAIDA_SQL}')

# ── Tamanho do arquivo ────────────────────────────────────────
tamanho_mb = os.path.getsize(SAIDA_SQL) / (1024 * 1024)
print(f'Tamanho: {tamanho_mb:.1f} MB')
if tamanho_mb > 50:
    print('AVISO: arquivo > 50 MB — considere aumentar o max_upload no phpMyAdmin ou dividir em partes.')
else:
    print('OK para importar direto pelo phpMyAdmin.')
