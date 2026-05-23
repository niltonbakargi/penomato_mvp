"""
Penomato — Script de sincronização/backup local
Roda no PC do gestor e puxa os dados do servidor de produção

USO:
    python sincronizar_backup.py

REQUISITOS:
    pip install requests

AGENDAMENTO AUTOMÁTICO (Windows):
    Agendador de Tarefas → Nova tarefa → Disparar semanalmente
    Ação: python C:\caminho\para\sincronizar_backup.py
"""

import requests
import json
import os
import time
import hashlib
from datetime import datetime
from pathlib import Path

# ============================================================
# CONFIGURAÇÃO — ajuste conforme necessário
# ============================================================
BASE_URL   = "https://penomato.app.br/src/Controllers/api_sync.php"

# Token: deve ser o mesmo calculado pelo PHP
# O PHP usa: 'penomato_backup_2026_' + md5(DB_PASS)
# Cole aqui o token que aparece ao acessar api_backup.php?acao=tabelas&token=TESTE
# (o servidor vai retornar 403 e você ajusta)
# Para descobrir o token, acesse temporariamente api_backup.php com o token correto
# ou peça ao Claude para exibir o token no painel do gestor.
BACKUP_TOKEN = "penomato_backup_2026_b49a8884c362210bee8214335efbbadb"  # preencha após ver o token no painel

PASTA_BACKUP = Path(__file__).parent / "backup_local"
PAUSA_ENTRE_TABELAS = 1.0   # segundos entre cada tabela (evita sobrecarga)
PAUSA_ENTRE_IMAGENS = 0.3   # segundos entre cada imagem
TIMEOUT = 30                 # segundos por requisição

# ============================================================

def log(msg):
    print(f"[{datetime.now().strftime('%H:%M:%S')}] {msg}")

def get(params):
    # Token enviado no corpo POST para evitar bloqueio de WAF/ModSecurity
    data = {'token': BACKUP_TOKEN}
    r = requests.post(BASE_URL, params=params, data=data, timeout=TIMEOUT)
    r.raise_for_status()
    return r

def salvar_sql(pasta, nome, conteudo):
    path = pasta / nome
    path.write_text(conteudo, encoding='utf-8')
    return path

def exportar_banco(pasta_sessao):
    log("─── Exportando banco de dados ───")
    pasta_sql = pasta_sessao / "banco"
    pasta_sql.mkdir(parents=True, exist_ok=True)

    # Lista tabelas
    dados = get({'acao': 'tabelas'}).json()
    tabelas = dados.get('tabelas', [])
    log(f"  {len(tabelas)} tabelas encontradas")

    sql_final = f"-- Penomato backup gerado em {datetime.now()}\n"
    sql_final += "SET NAMES utf8mb4;\nSET FOREIGN_KEY_CHECKS = 0;\n\n"

    for i, tabela in enumerate(tabelas, 1):
        log(f"  [{i}/{len(tabelas)}] {tabela}...")
        try:
            dados = get({'acao': 'exportar', 'tabela': tabela}).json()

            sql_final += f"DROP TABLE IF EXISTS `{tabela}`;\n"
            sql_final += dados['create'] + ";\n\n"

            rows = dados.get('rows', [])
            if rows:
                colunas = ', '.join(f"`{c}`" for c in rows[0].keys())
                valores = []
                for row in rows:
                    vals = []
                    for v in row.values():
                        if v is None:
                            vals.append('NULL')
                        elif isinstance(v, (int, float)):
                            vals.append(str(v))
                        else:
                            escaped = str(v).replace('\\', '\\\\').replace("'", "\\'")
                            vals.append(f"'{escaped}'")
                    valores.append('(' + ', '.join(vals) + ')')
                sql_final += f"INSERT INTO `{tabela}` ({colunas}) VALUES\n"
                sql_final += ',\n'.join(valores) + ";\n\n"

            log(f"     ✓ {dados['total']} registros")
        except Exception as e:
            log(f"     ✗ Erro: {e}")

        time.sleep(PAUSA_ENTRE_TABELAS)

    sql_final += "SET FOREIGN_KEY_CHECKS = 1;\n"

    arquivo = salvar_sql(pasta_sessao, f"banco_penomato.sql", sql_final)
    log(f"  ✅ Banco salvo: {arquivo} ({arquivo.stat().st_size // 1024} KB)")
    return True

def sincronizar_imagens(pasta_sessao):
    log("─── Sincronizando imagens ───")
    pasta_imgs = pasta_sessao / "uploads"

    # Lista imagens do banco
    dados = get({'acao': 'listar_imagens'}).json()
    imagens = dados.get('imagens', [])
    log(f"  {len(imagens)} imagens no banco")

    salvas = 0
    ignoradas = 0
    erros = 0

    for i, img in enumerate(imagens, 1):
        caminho = img['caminho_imagem']
        destino = pasta_imgs / Path(caminho)

        # Pula se já existe localmente
        if destino.exists():
            ignoradas += 1
            continue

        destino.parent.mkdir(parents=True, exist_ok=True)

        try:
            r = get({'acao': 'imagem', 'caminho': caminho})
            # Verifica se é JSON de erro ou arquivo binário
            ct = r.headers.get('Content-Type', '')
            if 'json' in ct:
                erros += 1
                if i % 20 == 0 or i == len(imagens):
                    log(f"  [{i}/{len(imagens)}] {erros} erros até agora (arquivo não encontrado no servidor)")
            else:
                destino.write_bytes(r.content)
                salvas += 1
                if i % 20 == 0 or i == len(imagens):
                    log(f"  [{i}/{len(imagens)}] ✓ {salvas} salvas, {ignoradas} já existiam, {erros} erros")
        except Exception as e:
            erros += 1
            log(f"  [{i}] ✗ {caminho}: {e}")

        time.sleep(PAUSA_ENTRE_IMAGENS)

    log(f"  ✅ Imagens: {salvas} novas, {ignoradas} já existiam, {erros} não encontradas no servidor")

def main():
    if not BACKUP_TOKEN:
        print("=" * 60)
        print("CONFIGURE O BACKUP_TOKEN antes de rodar.")
        print("Veja as instruções no início deste arquivo.")
        print("=" * 60)
        return

    data_hora = datetime.now().strftime('%Y-%m-%d_%H-%M')
    pasta_sessao = PASTA_BACKUP / data_hora
    pasta_sessao.mkdir(parents=True, exist_ok=True)

    log(f"Iniciando backup — {data_hora}")
    log(f"Destino: {pasta_sessao}")

    try:
        exportar_banco(pasta_sessao)
        sincronizar_imagens(pasta_sessao)
        log(f"✅ Backup concluído em {pasta_sessao}")
    except requests.exceptions.ConnectionError:
        log("❌ Não foi possível conectar ao servidor. Verifique sua internet.")
    except requests.exceptions.HTTPError as e:
        log(f"❌ Erro HTTP: {e}")
    except Exception as e:
        log(f"❌ Erro inesperado: {e}")
        raise

if __name__ == '__main__':
    main()
