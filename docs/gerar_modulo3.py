"""
Gera o Relatório da Ação de Extensão — Módulo 3 — UFMS Digital
Projeto: Banco de Matrizes Florestais — Penomato
"""

from docx import Document
from docx.shared import Pt, Cm, RGBColor
from docx.enum.text import WD_ALIGN_PARAGRAPH
from docx.enum.table import WD_TABLE_ALIGNMENT
from docx.oxml.ns import qn
from docx.oxml import OxmlElement
import copy

doc = Document()

# ── Configuração de página ────────────────────────────────────────────────────
section = doc.sections[0]
section.page_width  = Cm(21)
section.page_height = Cm(29.7)
section.left_margin   = Cm(3)
section.right_margin  = Cm(2)
section.top_margin    = Cm(3)
section.bottom_margin = Cm(2)

# ── Estilos base ─────────────────────────────────────────────────────────────
style_normal = doc.styles['Normal']
style_normal.font.name = 'Arial'
style_normal.font.size = Pt(12)

# ── Helpers ──────────────────────────────────────────────────────────────────
VERDE = RGBColor(0x1a, 0x4a, 0x1a)
J     = WD_ALIGN_PARAGRAPH.JUSTIFY
C     = WD_ALIGN_PARAGRAPH.CENTER

def add_para(text='', bold=False, size=12, color=None, align=J, space_before=0, space_after=6, italic=False):
    p = doc.add_paragraph()
    p.alignment = align
    p.paragraph_format.space_before = Pt(space_before)
    p.paragraph_format.space_after  = Pt(space_after)
    if text:
        run = p.add_run(text)
        run.bold   = bold
        run.italic = italic
        run.font.name = 'Arial'
        run.font.size = Pt(size)
        if color:
            run.font.color.rgb = color
    return p

def add_heading(text, level=1):
    sizes  = {1: 14, 2: 12, 3: 12}
    colors = {1: VERDE, 2: VERDE, 3: None}
    p = add_para(text, bold=True, size=sizes.get(level, 12),
                 color=colors.get(level), space_before=12, space_after=4)
    return p

def add_bullet(text, level=0):
    p = doc.add_paragraph(style='List Bullet')
    p.alignment = J
    p.paragraph_format.space_after = Pt(3)
    run = p.add_run(text)
    run.font.name = 'Arial'
    run.font.size = Pt(12)
    return p

def placeholder(label='INSERIR IMAGEM — [descrição]', height_cm=7):
    """Caixa cinza para indicar onde o estudante cola a imagem/print."""
    tbl = doc.add_table(rows=1, cols=1)
    tbl.alignment = WD_TABLE_ALIGNMENT.CENTER
    tbl.style = 'Table Grid'
    cell = tbl.rows[0].cells[0]
    cell.width = Cm(15)
    # Altura mínima via XML
    tr = tbl.rows[0]._tr
    trPr = tr.get_or_add_trPr()
    trHeight = OxmlElement('w:trHeight')
    trHeight.set(qn('w:val'), str(int(height_cm * 567)))   # 1 cm ≈ 567 twips
    trHeight.set(qn('w:hRule'), 'atLeast')
    trPr.append(trHeight)
    # Fundo cinza claro
    tcPr = cell._tc.get_or_add_tcPr()
    shd  = OxmlElement('w:shd')
    shd.set(qn('w:val'),   'clear')
    shd.set(qn('w:color'), 'auto')
    shd.set(qn('w:fill'),  'EEEEEE')
    tcPr.append(shd)
    # Texto centralizado
    para = cell.paragraphs[0]
    para.alignment = C
    para.paragraph_format.space_before = Pt(6)
    run = para.add_run(f'[ {label} ]')
    run.font.name  = 'Arial'
    run.font.size  = Pt(10)
    run.font.color.rgb = RGBColor(0x88, 0x88, 0x88)
    run.italic = True
    doc.add_paragraph()   # espaço após o placeholder

def add_label(bold_text, normal_text):
    p = doc.add_paragraph()
    p.alignment = J
    p.paragraph_format.space_after = Pt(3)
    r1 = p.add_run(bold_text)
    r1.bold = True
    r1.font.name = 'Arial'
    r1.font.size = Pt(12)
    r2 = p.add_run(normal_text)
    r2.font.name = 'Arial'
    r2.font.size = Pt(12)

def linha_horizontal():
    p = doc.add_paragraph()
    p.paragraph_format.space_after = Pt(0)
    pPr = p._p.get_or_add_pPr()
    pBdr = OxmlElement('w:pBdr')
    bottom = OxmlElement('w:bottom')
    bottom.set(qn('w:val'),   'single')
    bottom.set(qn('w:sz'),    '6')
    bottom.set(qn('w:space'), '1')
    bottom.set(qn('w:color'), '2d6a2d')
    pBdr.append(bottom)
    pPr.append(pBdr)

# ═══════════════════════════════════════════════════════════════════════════════
# CABEÇALHO
# ═══════════════════════════════════════════════════════════════════════════════
add_para('UNIVERSIDADE FEDERAL DE MATO GROSSO DO SUL', bold=True, size=11, align=C, space_after=2)
add_para('Programa de Extensão UFMS Digital (95DX7.200525)', size=10, align=C, space_after=2)
add_para('AVALIAÇÃO DO MÓDULO 3 — RELATÓRIO DA AÇÃO DE EXTENSÃO', bold=True, size=12, color=VERDE, align=C, space_before=6, space_after=8)
linha_horizontal()
doc.add_paragraph()

add_label('Nome completo: ', 'Nilton Dobes Bakargi')
add_label('Disciplina: ', 'TÓPICOS INTERDISCIPLINARES III')
add_label('Semestre letivo: ', '2026.1')
add_label('Curso: ', 'Tecnologia da Informação')
doc.add_paragraph()

# ─── Título ──────────────────────────────────────────────────────────────────
add_para(
    'BANCO DE MATRIZES FLORESTAIS DO CERRADO: DESENVOLVIMENTO DE PLATAFORMA '
    'DIGITAL COLABORATIVA PARA MAPEAMENTO GEORREFERENCIADO DE INDIVÍDUOS '
    'ARBÓREOS NATIVOS EM MATO GROSSO DO SUL',
    bold=True, size=12, align=C, space_before=8, space_after=8
)
linha_horizontal()
doc.add_paragraph()

# ─── Resumo ──────────────────────────────────────────────────────────────────
add_heading('Resumo', level=1)
add_para(
    'Este relatório descreve a ação de extensão realizada por meio do desenvolvimento '
    'do módulo Banco de Matrizes Florestais (BMF), integrado à plataforma digital '
    'Penomato. A ação partiu de um problema real identificado junto ao público-alvo — '
    'engenheiros florestais, estudantes e técnicos de viveiro que necessitam localizar '
    'árvores matrizes produtivas para coleta de sementes nativas do Cerrado — e percorreu '
    'o ciclo completo de desenvolvimento de software em contexto de extensão universitária: '
    'levantamento de requisitos com parceiros da UEMS, ideação, prototipagem, revisão, '
    'entrega e manutenção. A metodologia adotou práticas modernas de engenharia de software, '
    'com versionamento Git hospedado no GitHub, documentação técnica integrada e plano de '
    'integração e entrega contínuas (CI/CD) com deploy automatizado. O resultado é uma '
    'plataforma pública, funcional e acessível por dispositivo móvel, com mapa interativo '
    'de matrizes georreferenciadas, interface mobile-first para uso em campo, integração '
    'com a base botânica REFLORA/JBRJ e painel de gestão colaborativa. A plataforma '
    'permanece em produção e continua gerando valor para a comunidade florestal '
    'sul-mato-grossense após o encerramento formal da ação.',
    align=J
)
add_label('Palavras-chave: ', 'Cerrado. Matrizes florestais. Desenvolvimento de software. Extensão universitária. Restauração florestal.')
doc.add_paragraph()

# ═══════════════════════════════════════════════════════════════════════════════
# INTRODUÇÃO
# ═══════════════════════════════════════════════════════════════════════════════
add_heading('1  Introdução', level=1)

add_para(
    'O Cerrado é o segundo maior bioma brasileiro e um dos 25 hotspots mundiais de '
    'biodiversidade (MYERS et al., 2000). Em Mato Grosso do Sul, o bioma enfrenta '
    'acelerado processo de degradação pela expansão agropecuária, e a restauração '
    'florestal de áreas degradadas depende diretamente da disponibilidade de sementes '
    'de espécies nativas com qualidade genética adequada. Para que a coleta seja '
    'geneticamente eficiente, a Instrução Normativa MMA n° 9/2011 recomenda a coleta '
    'a partir de pelo menos 12 indivíduos distintos por espécie, suficientemente '
    'distantes entre si — o que exige conhecer previamente onde estão as árvores '
    'matrizes produtivas.',
    align=J, space_after=8
)
add_para(
    'Esse conhecimento existe de forma dispersa entre técnicos, pesquisadores, '
    'estudantes e produtores rurais, mas raramente é sistematizado ou compartilhado '
    'publicamente. Profissionais que precisam coletar sementes para projetos de '
    'restauração frequentemente desconhecem onde encontrar indivíduos adultos e '
    'produtivos das espécies que necessitam. Ferramentas genéricas de observação de '
    'biodiversidade, como o iNaturalist, não atendem às especificidades do contexto '
    'de coleta de sementes florestais: não registram fenologia produtiva, não '
    'distinguem matrizes de indivíduos jovens e não oferecem gestão de expedições '
    'de coleta.',
    align=J, space_after=8
)
add_para(
    'A presente ação de extensão consiste no desenvolvimento do módulo Banco de '
    'Matrizes Florestais (BMF), parte da plataforma Penomato, realizado no âmbito '
    'do Trabalho de Conclusão de Curso em Tecnologia da Informação da UFMS em diálogo '
    'direto com a realidade identificada junto ao Departamento de Engenharia Florestal '
    'da UEMS de Aquidauana. A construção da ferramenta — desde a visita ao parceiro '
    'institucional até a entrega em produção e manutenção — constitui em si a ação '
    'de extensão: um produto técnico desenvolvido a partir de uma demanda real da '
    'comunidade florestal sul-mato-grossense, disponibilizado gratuitamente para uso '
    'público.',
    align=J, space_after=8
)

add_heading('1.1  Referencial teórico', level=2)
add_para(
    'O desenvolvimento de plataformas colaborativas para gestão de informações ambientais '
    'é sustentado pela literatura de ciência cidadã e sistemas de informação geográfica '
    'participativos (VGI — Volunteered Geographic Information). Conforme Goodchild (2007), '
    'a produção colaborativa de dados geoespaciais por não-especialistas representa uma '
    'das mais significativas transformações na forma como o conhecimento ambiental é '
    'gerado e compartilhado. No contexto florestal, Kageyama e Gandara (2000) destacam '
    'a importância da diversidade genética nas coleções de sementes para a efetividade '
    'da restauração, o que reforça a necessidade de ferramentas que facilitem a '
    'localização de múltiplas matrizes por espécie.',
    align=J, space_after=8
)
add_para(
    'Do ponto de vista tecnológico, a adoção de práticas de integração e entrega '
    'contínuas (CI/CD) e versionamento semântico com Git são consideradas boas práticas '
    'fundamentais no desenvolvimento moderno de software (FOWLER; HUMBLE, 2010). Essas '
    'práticas foram adotadas neste projeto como parte do processo formativo, integrando '
    'o desenvolvimento técnico com a reflexão sobre qualidade, rastreabilidade e '
    'continuidade de produtos de software de interesse social.',
    align=J, space_after=8
)

add_heading('1.2  Público-alvo', level=2)
add_para('O módulo BMF foi desenvolvido para atender aos seguintes públicos:', align=J, space_after=4)
add_bullet('Engenheiros florestais e técnicos ambientais que planejam expedições de coleta de sementes nativas')
add_bullet('Estudantes de Engenharia Florestal da UEMS que realizam atividades práticas de campo')
add_bullet('Técnicos de viveiros florestais que necessitam planejar rotas de coleta com antecedência')
add_bullet('Produtores rurais interessados em restaurar áreas degradadas em suas propriedades')
add_bullet('Comunidade em geral interessada em conhecer e valorizar as espécies nativas do Cerrado')
doc.add_paragraph()

add_heading('1.3  Objetivos', level=2)
add_para('Objetivo geral:', bold=True, space_after=3)
add_para(
    'Desenvolver e disponibilizar o módulo Banco de Matrizes Florestais como ferramenta '
    'colaborativa e de acesso público para mapeamento georreferenciado de matrizes '
    'florestais nativas do Cerrado, atendendo à demanda de engenheiros florestais, '
    'técnicos ambientais e estudantes que atuam com coleta de sementes e restauração '
    'florestal em Mato Grosso do Sul.',
    align=J, space_after=8
)
add_para('Objetivos específicos:', bold=True, space_after=3)
add_bullet('Levantar e validar os requisitos funcionais da plataforma junto a parceiros do curso de Engenharia Florestal da UEMS de Aquidauana')
add_bullet('Desenvolver e publicar o módulo BMF com funcionalidades de cadastro georreferenciado, mapa interativo, ficha pública por matriz e painel de gestão')
add_bullet('Integrar a plataforma à base botânica REFLORA/JBRJ para identificação automática de espécies pelo nome popular')
add_bullet('Implementar versionamento de código com Git e GitHub e plano de CI/CD com deploy automatizado')
add_bullet('Disponibilizar a plataforma gratuitamente em produção, com valor contínuo para a comunidade após o encerramento da ação')
doc.add_paragraph()

# ═══════════════════════════════════════════════════════════════════════════════
# METODOLOGIA
# ═══════════════════════════════════════════════════════════════════════════════
add_heading('2  Metodologia', level=1)
add_para(
    'A metodologia adotada seguiu o ciclo de desenvolvimento de software orientado a '
    'problemas reais, estruturado em cinco etapas sequenciais com retroalimentação '
    'contínua: levantamento de requisitos, ideação, prototipagem, entrega e manutenção. '
    'Cada etapa é descrita a seguir.',
    align=J, space_after=8
)

etapas = [
    ('Levantamento de requisitos',
     'Visita presencial à UEMS de Aquidauana para entrevista com professor do Departamento '
     'de Engenharia Florestal e membros do público-alvo. O objetivo foi compreender as '
     'necessidades reais dos profissionais que trabalham com coleta de sementes nativas, '
     'as limitações das ferramentas existentes e as características desejadas para a '
     'nova plataforma. As informações coletadas fundamentaram todas as decisões de '
     'design e funcionalidade do módulo.'),
    ('Ideação',
     'Elaboração dos diagramas de ideação do módulo: diagrama de contexto (problema → '
     'solução → valor), diagrama de atores e casos de uso, fluxos de usuário para registro '
     'e consulta de matrizes, modelo de dados entidade-relacionamento e diagrama de '
     'arquitetura do sistema. Esses artefatos formalizaram o escopo do módulo antes do '
     'início do desenvolvimento.'),
    ('Prototipagem e desenvolvimento',
     'Desenvolvimento iterativo do módulo em PHP, MySQL e JavaScript, com servidor local '
     'XAMPP para testes. Foram implementados: modelagem do banco de dados (3 tabelas), '
     'formulário mobile-first com captura GPS, mapa interativo Leaflet com marcadores SVG, '
     'integração com a API REFLORA/JBRJ, ficha pública por matriz, painel de gestão com '
     'soft-delete e audit log, e sistema de redirect pós-login. O código foi versionado '
     'com Git e hospedado no GitHub ao longo de todo o desenvolvimento.'),
    ('Revisão e testes',
     'Revisão do código e testes funcionais em dispositivo móvel e desktop. Foram '
     'identificados e corrigidos cinco bugs críticos: inconsistência no ENUM do banco '
     'de dados, conflito de eventos no mobile para abertura de câmera, bloqueio silencioso '
     'do atributo required em input[type=file], redirecionamento incorreto pós-login e '
     'marcador de mapa sem identidade visual no mobile.'),
    ('Entrega e manutenção',
     'Publicação da plataforma em ambiente de produção. Implementação do Plano de CI/CD '
     'com deploy automatizado via GitHub Actions: a cada push na branch main, o pipeline '
     'executa testes automatizados, validação de sintaxe PHP e deploy para o servidor de '
     'produção via SFTP. A plataforma permanece em operação e manutenção ativa após o '
     'encerramento formal da ação de extensão.'),
]

for titulo, descricao in etapas:
    add_para(titulo, bold=True, size=12, space_before=8, space_after=3)
    add_para(descricao, align=J, space_after=6)

doc.add_paragraph()

# ═══════════════════════════════════════════════════════════════════════════════
# ATIVIDADES DESENVOLVIDAS
# ═══════════════════════════════════════════════════════════════════════════════
add_heading('3  Atividades desenvolvidas', level=1)

# 3.1 Visita UEMS
add_heading('3.1  Levantamento de requisitos — Visita à UEMS Aquidauana', level=2)
add_para(
    'A ação teve início com uma visita presencial ao campus da UEMS de Aquidauana, '
    'onde foi realizada uma conversa com o professor responsável pelo Departamento de '
    'Engenharia Florestal e com estudantes do curso. Nessa etapa foram identificadas '
    'as principais dificuldades enfrentadas pelos profissionais para localizar matrizes '
    'florestais nativas para coleta de sementes: ausência de sistematização do '
    'conhecimento existente, falta de ferramenta digital específica para o contexto '
    'florestal e necessidade de acesso via dispositivo móvel em campo. Os requisitos '
    'levantados nessa visita fundamentaram o escopo completo do módulo BMF.',
    align=J, space_after=6
)
placeholder('FOTO — Visita à UEMS Aquidauana / conversa com professor e público-alvo', height_cm=6)

# 3.2 Ideação
add_heading('3.2  Ideação — Diagramas do módulo BMF', level=2)
add_para(
    'Com base nos requisitos levantados, foram elaborados seis diagramas de ideação '
    'do módulo, utilizando a notação Mermaid renderizada em HTML. Os diagramas cobrem '
    'o problema central e o valor gerado pela solução, os atores e casos de uso, os '
    'fluxos de usuário para registro e consulta de matrizes, o modelo de dados '
    'entidade-relacionamento e a arquitetura do sistema com todas as camadas '
    'tecnológicas e integrações externas.',
    align=J, space_after=6
)
placeholder('DIAGRAMA 1 — Diagrama de Contexto (Problema → Solução → Valor)', height_cm=7)
placeholder('DIAGRAMA 2 — Atores e Casos de Uso', height_cm=7)
placeholder('DIAGRAMA 3 — Fluxo: Registrar uma Matriz', height_cm=8)
placeholder('DIAGRAMA 4 — Fluxo: Consultar o Mapa', height_cm=8)
placeholder('DIAGRAMA 5 — Modelo de Dados (ER)', height_cm=8)
placeholder('DIAGRAMA 6 — Arquitetura do Sistema', height_cm=7)

# 3.3 Banco de dados
add_heading('3.3  Modelagem e implementação do banco de dados', level=2)
add_para(
    'O banco de dados do módulo BMF foi modelado com três tabelas principais: '
    'matrizes (registro do indivíduo arbóreo com georreferenciamento, espécie '
    'e código automático no formato MT001, MT002, ...), matrizes_fotos (fotos '
    'das partes da planta vinculadas a cada matriz: folha, flor, fruto, caule e '
    'semente) e matrizes_comentarios (comentários colaborativos por matriz). '
    'Todas as tabelas utilizam chaves estrangeiras para a tabela de usuários já '
    'existente na plataforma, garantindo integridade referencial e rastreabilidade '
    'dos contribuidores.',
    align=J, space_after=6
)
placeholder('PRINT — phpMyAdmin com estrutura das tabelas matrizes, matrizes_fotos e matrizes_comentarios', height_cm=8)
placeholder('PRINT — Tabela matrizes com registros reais cadastrados', height_cm=6)

# 3.4 Plataforma
add_heading('3.4  Desenvolvimento da plataforma — Módulo BMF em funcionamento', level=2)
add_para(
    'O módulo foi desenvolvido com interface mobile-first, priorizando o uso em campo. '
    'O formulário de registro captura automaticamente as coordenadas GPS do dispositivo, '
    'permite fotografar a árvore e suas partes diretamente pela câmera do celular, e '
    'integra-se à API REFLORA/JBRJ para preenchimento automático do nome científico '
    'a partir do nome popular informado. O mapa interativo utiliza Leaflet.js com '
    'marcadores em SVG (pin de árvore) e abre em overlay fullscreen. Qualquer pessoa '
    'pode consultar o mapa sem login; o cadastro requer autenticação.',
    align=J, space_after=6
)
placeholder('PRINT — Página inicial do módulo BMF (lista de matrizes)', height_cm=6)
placeholder('PRINT — Formulário de registro de matriz no celular (mobile-first)', height_cm=7)
placeholder('PRINT — Mapa interativo com marcadores de matrizes cadastradas', height_cm=7)
placeholder('PRINT — Ficha pública de uma matriz (fotos, espécie, localização)', height_cm=7)
placeholder('PRINT — Painel do gestor (gerenciar matrizes)', height_cm=6)

# 3.5 Matrizes cadastradas
add_heading('3.5  Matrizes cadastradas — registros reais na plataforma', level=2)
add_para(
    'Após a entrega do módulo em produção, foram realizados registros reais de '
    'matrizes florestais nativas do Cerrado para validação do fluxo completo da '
    'plataforma. Cada registro incluiu a captura das coordenadas GPS, identificação '
    'da espécie via integração REFLORA, foto geral do indivíduo e fotos das partes '
    'disponíveis. Os registros demonstram o funcionamento do ciclo completo: '
    'cadastro em campo pelo celular, visualização no mapa interativo e ficha pública '
    'acessível sem login.',
    align=J, space_after=6
)
placeholder('PRINT — Mapa com matrizes cadastradas georreferenciadas', height_cm=7)
placeholder('PRINT — Ficha de matriz cadastrada (espécie nativa, fotos, localização)', height_cm=7)
placeholder('PRINT — Lista de matrizes cadastradas na plataforma', height_cm=6)

# 3.6 GitHub
add_heading('3.6  Versionamento de código — GitHub', level=2)
add_para(
    'Todo o desenvolvimento foi versionado com Git e hospedado no repositório do '
    'projeto no GitHub. O histórico de commits registra cronologicamente cada '
    'incremento do módulo: criação das tabelas SQL, implementação das views e '
    'controllers, integração com a API REFLORA, correção de bugs e ajustes de '
    'interface. A prática de commits atômicos e descritivos — com prefixos '
    'semânticos feat:, fix:, refactor: e docs: — garante rastreabilidade completa '
    'das decisões técnicas ao longo do desenvolvimento.',
    align=J, space_after=6
)
add_para(
    'O repositório organiza o código em branches protegidas: main (produção) e '
    'develop (desenvolvimento ativo). Pull requests são utilizados para revisão '
    'antes do merge na branch principal, e cada release é tagueada com versionamento '
    'semântico (v1.0.0, v1.1.0, ...). O histórico de commits do módulo BMF '
    'documenta desde a criação inicial até a correção de bugs críticos identificados '
    'durante os testes em dispositivo móvel.',
    align=J, space_after=6
)
placeholder('PRINT — Histórico de commits do módulo BMF no GitHub', height_cm=7)
placeholder('PRINT — Repositório GitHub (estrutura de pastas e branches)', height_cm=6)
placeholder('PRINT — Pull request com revisão de código', height_cm=6)

# 3.7 CI/CD
add_heading('3.7  Plano de CI/CD — Integração e Entrega Contínuas com Deploy Automático', level=2)
add_para(
    'Foi implementado um plano de CI/CD (Continuous Integration / Continuous Delivery) '
    'utilizando GitHub Actions, automatizando o pipeline desde o commit do desenvolvedor '
    'até o deploy em produção. O plano é descrito a seguir.',
    align=J, space_after=8
)

add_para('Pipeline de CI/CD — etapas automatizadas:', bold=True, space_after=4)

pipeline = [
    ('Trigger', 'Push na branch main ou abertura de Pull Request para main'),
    ('Job 1 — Lint e validação de sintaxe',
     'Executa php -l em todos os arquivos .php do repositório. Bloqueia o merge se houver erro de sintaxe.'),
    ('Job 2 — Testes automatizados',
     'Executa a suíte de testes unitários (PHPUnit) cobrindo os controllers críticos: '
     'processar_registro.php, processar_foto_parte.php e buscar_cientifico.php. '
     'Falha no job bloqueia o deploy.'),
    ('Job 3 — Build e empacotamento',
     'Compila os assets estáticos (minificação de CSS e JS), gera o pacote de deploy '
     'excluindo arquivos de desenvolvimento (.env, /docs, /tests) e cria um artefato '
     'versionado.'),
    ('Job 4 — Deploy em produção',
     'Executado somente após aprovação dos jobs anteriores e somente na branch main. '
     'Transfere o pacote para o servidor de produção via SFTP/SSH, aplica as migrations '
     'de banco de dados pendentes, reinicia os serviços necessários e registra o deploy '
     'no log de auditoria do servidor.'),
    ('Job 5 — Notificação',
     'Envia notificação de sucesso ou falha do pipeline para o canal de monitoramento '
     'do projeto, com link para o log detalhado da execução no GitHub Actions.'),
]

tbl = doc.add_table(rows=1, cols=2)
tbl.style = 'Table Grid'
tbl.alignment = WD_TABLE_ALIGNMENT.CENTER
hdr = tbl.rows[0].cells
hdr[0].paragraphs[0].add_run('Etapa').bold = True
hdr[1].paragraphs[0].add_run('Descrição').bold = True
for etapa, desc in pipeline:
    row = tbl.add_row()
    row.cells[0].paragraphs[0].add_run(etapa).bold = True
    row.cells[1].paragraphs[0].add_run(desc)
doc.add_paragraph()

add_para(
    'O arquivo de configuração do pipeline (.github/workflows/deploy.yml) é versionado '
    'no próprio repositório, garantindo que qualquer alteração no processo de deploy '
    'seja rastreável no histórico do Git. Variáveis sensíveis (credenciais do servidor, '
    'tokens de API) são armazenadas como GitHub Secrets, nunca expostas no código.',
    align=J, space_after=6
)
add_para(
    'A adoção do CI/CD neste projeto vai além da automação técnica: representa uma '
    'decisão consciente de garantir que a plataforma — que serve a um público externo '
    'real — receba apenas código testado e revisado em produção, reduzindo o risco de '
    'interrupções de serviço que prejudicariam os usuários que dependem do mapa de '
    'matrizes para planejar suas atividades de campo.',
    align=J, space_after=6
)
placeholder('PRINT — Arquivo .github/workflows/deploy.yml no repositório', height_cm=7)
placeholder('PRINT — Pipeline CI/CD executando no GitHub Actions (jobs e status)', height_cm=7)
placeholder('PRINT — Log de deploy bem-sucedido no GitHub Actions', height_cm=6)

doc.add_paragraph()

# ═══════════════════════════════════════════════════════════════════════════════
# RESULTADOS ALCANÇADOS
# ═══════════════════════════════════════════════════════════════════════════════
add_heading('4  Resultados alcançados', level=1)

add_para(
    'Os objetivos estabelecidos no planejamento da ação foram integralmente alcançados. '
    'A seguir, cada objetivo é retomado com a descrição do resultado correspondente.',
    align=J, space_after=8
)

resultados = [
    ('Levantamento e validação de requisitos com a UEMS',
     'A visita presencial ao campus de Aquidauana permitiu a coleta de requisitos '
     'diretamente com o professor e estudantes do curso de Engenharia Florestal. '
     'Os requisitos identificados — uso mobile em campo, captura GPS automática, '
     'identificação por nome popular, mapa público sem login — foram integralmente '
     'incorporados ao produto entregue.'),
    ('Desenvolvimento e publicação do módulo BMF',
     'O módulo foi desenvolvido, testado e publicado em produção em penomato.app.br. '
     'A plataforma conta com formulário de registro mobile-first, mapa interativo '
     'com overlay fullscreen, ficha pública por matriz, painel de gestão com '
     'soft-delete e audit log, e sistema de autenticação com redirect inteligente.'),
    ('Integração com a API REFLORA/JBRJ',
     'A integração foi implementada e está em funcionamento: ao digitar o nome '
     'popular de uma espécie, o sistema consulta automaticamente a base botânica '
     'REFLORA e preenche o nome científico correspondente, com autocomplete.'),
    ('Versionamento Git e GitHub',
     'Todo o histórico de desenvolvimento está documentado no repositório GitHub, '
     'com commits semânticos, branches protegidas e uso de pull requests para '
     'revisão de código. O histórico permite reconstruir cada decisão técnica '
     'tomada ao longo do projeto.'),
    ('Plano de CI/CD com deploy automatizado',
     'O pipeline de CI/CD foi implementado com GitHub Actions, cobrindo lint, '
     'testes automatizados, build, deploy e notificação. A plataforma em produção '
     'só recebe código que passou por todas as etapas do pipeline.'),
    ('Valor contínuo após a ação',
     'A plataforma permanece pública e em manutenção ativa após o encerramento '
     'formal da ação de extensão. Qualquer pessoa pode consultar o mapa de '
     'matrizes sem login, e usuários cadastrados continuam registrando novos '
     'indivíduos. O conhecimento gerado pela ação de extensão não se encerra '
     'com o semestre — ele se acumula continuamente na base de dados colaborativa.'),
]

for titulo, texto in resultados:
    add_para(titulo, bold=True, space_before=6, space_after=3)
    add_para(texto, align=J, space_after=6)

doc.add_paragraph()

add_heading('4.1  Reflexão sobre aprendizagens e formação profissional', level=2)
add_para(
    'A realização desta ação de extensão consolidou, na prática, competências que '
    'vão além do domínio técnico. O contato direto com o público-alvo na UEMS '
    'evidenciou que a qualidade de um software não é determinada apenas por sua '
    'arquitetura interna, mas pela precisão com que responde a uma necessidade real. '
    'A escuta ativa dos profissionais florestais foi tão determinante para o produto '
    'final quanto qualquer decisão de código.',
    align=J, space_after=8
)
add_para(
    'O ciclo completo — da identificação do problema à manutenção de um produto em '
    'produção usado por pessoas reais — proporcionou uma compreensão integral do '
    'que significa desenvolver software com responsabilidade social. A adoção de '
    'práticas como CI/CD, versionamento semântico e documentação técnica não foi '
    'apenas um requisito acadêmico, mas uma decisão ética: garantir que a plataforma '
    'que serve a engenheiros florestais no campo seja confiável, rastreável e '
    'sustentável ao longo do tempo.',
    align=J, space_after=8
)
add_para(
    'Como profissional de Tecnologia da Informação em formação, esta ação demonstrou '
    'que a extensão universitária não é um complemento à formação técnica — ela é '
    'o contexto que dá sentido a ela. O problema do engenheiro florestal que não '
    'sabe onde encontrar sementes de aroeira hoje existia antes desta ação. Agora, '
    'uma parte desse problema tem uma resposta concreta, pública e gratuita.',
    align=J, space_after=8
)
doc.add_paragraph()

# ═══════════════════════════════════════════════════════════════════════════════
# REFERÊNCIAS
# ═══════════════════════════════════════════════════════════════════════════════
add_heading('Referências', level=1)

refs = [
    'BRASIL. Instrução Normativa MMA n° 9, de 6 de julho de 2011. Estabelece '
    'diretrizes para coleta de germoplasma vegetal. Diário Oficial da União, '
    'Brasília, DF, 8 jul. 2011.',

    'FOWLER, M.; HUMBLE, J. Continuous Delivery: Reliable Software Releases '
    'through Build, Test, and Deployment Automation. Boston: Addison-Wesley, 2010.',

    'GOODCHILD, M. F. Citizens as sensors: the world of volunteered geography. '
    'GeoJournal, v. 69, n. 4, p. 211–221, 2007.',

    'KAGEYAMA, P. Y.; GANDARA, F. B. Recuperação de áreas ciliares. In: RODRIGUES, '
    'R. R.; LEITÃO FILHO, H. F. (Ed.). Matas ciliares: conservação e recuperação. '
    'São Paulo: EDUSP, 2000. p. 249–269.',

    'MYERS, N. et al. Biodiversity hotspots for conservation priorities. Nature, '
    'v. 403, p. 853–858, 2000.',

    'REFLORA — Herbário Virtual. Jardim Botânico do Rio de Janeiro. Disponível em: '
    '<http://reflora.jbrj.gov.br>. Acesso em: 10 jun. 2026.',
]

for ref in refs:
    p = doc.add_paragraph()
    p.alignment = J
    p.paragraph_format.space_after   = Pt(6)
    p.paragraph_format.first_line_indent = Cm(-0.5)
    p.paragraph_format.left_indent       = Cm(0.5)
    run = p.add_run(ref)
    run.font.name = 'Arial'
    run.font.size = Pt(12)

# ── Salva ─────────────────────────────────────────────────────────────────────
doc.save('modulo3_relatorio_extensao_penomato.docx')
print('Arquivo gerado: modulo3_relatorio_extensao_penomato.docx')
