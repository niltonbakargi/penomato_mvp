from docx import Document
from docx.enum.text import WD_ALIGN_PARAGRAPH
from copy import deepcopy

def clone_row(table, src_idx=1):
    """Clona a última linha da tabela via lxml (contorna bug do add_row com float widths)."""
    tr = deepcopy(table.rows[src_idx]._tr)
    table._tbl.append(tr)
    # Limpa texto de todas as células da nova linha
    for cell in table.rows[-1].cells:
        for para in cell.paragraphs:
            for run in para.runs:
                run.text = ''

doc = Document('Cópia de [UFMS Digital] TEMPLATE DO ESTUDANTE - MÓDULO 2 - PLANEJAMENTO DA AÇÃO DE EXTENSÃO.docx')

def set_label_text(para, bold_label, normal_text, alignment=None):
    for run in para.runs:
        run.text = ''
    if para.runs:
        para.runs[0].text = bold_label
        para.runs[0].bold = True
        if len(para.runs) > 1:
            para.runs[1].text = normal_text
            para.runs[1].bold = False
        else:
            r = para.add_run(normal_text)
            r.bold = False
    if alignment is not None:
        para.alignment = alignment

def replace_para(para, text, bold=False, alignment=None):
    for run in para.runs:
        run.text = ''
    if para.runs:
        para.runs[0].text = text
        para.runs[0].bold = bold
    else:
        r = para.add_run(text)
        r.bold = bold
    if alignment is not None:
        para.alignment = alignment

J = WD_ALIGN_PARAGRAPH.JUSTIFY

# Cabeçalho
set_label_text(doc.paragraphs[6], 'Nome completo: ', '[SEU NOME COMPLETO]')
set_label_text(doc.paragraphs[7], 'Disciplina: ', 'Tópicos Interdisciplinares III')
p8 = doc.paragraphs[8]
for r in p8.runs: r.text = ''
if p8.runs:
    p8.runs[0].text = 'Semestre letivo: '
    p8.runs[0].bold = True
    p8.add_run('2026/1').bold = False
set_label_text(doc.paragraphs[9], 'Curso: ', 'Tecnologia da Informação')
p10 = doc.paragraphs[10]
for r in p10.runs: r.text = ''
if p10.runs:
    p10.runs[0].text = 'Público-alvo: '
    p10.runs[0].bold = True
    p10.add_run(
        'Engenheiros florestais, técnicos ambientais, viveiristas e estudantes de '
        'Engenharia Florestal da UEMS que necessitam localizar matrizes florestais '
        'nativas para coleta de sementes e restauração do Cerrado'
    ).bold = False
p11 = doc.paragraphs[11]
for r in p11.runs: r.text = ''
if p11.runs:
    p11.runs[0].text = 'Local de realização: '
    p11.runs[0].bold = True
    p11.add_run(
        'UFMS — Universidade Federal de Mato Grosso do Sul, em parceria com a '
        'UEMS — Universidade Estadual de Mato Grosso do Sul'
    ).bold = False

set_label_text(doc.paragraphs[13], 'Título da ação: ',
               'PENOMATO: DESENVOLVIMENTO DE PLATAFORMA DIGITAL COLABORATIVA PARA MAPEAMENTO DE MATRIZES FLORESTAIS DO CERRADO')

# Resumo
resumo = (
    'O presente planejamento descreve a ação de extensão realizada por meio do desenvolvimento da '
    'plataforma digital Penomato, criada em parceria com a UEMS — Universidade Estadual de Mato '
    'Grosso do Sul. O problema central motivador é a ausência de ferramentas específicas para o '
    'mapeamento colaborativo de árvores matrizes com potencial para coleta de sementes nativas, '
    'uma demanda real e recorrente entre profissionais e estudantes de Engenharia Florestal. A '
    'plataforma é desenvolvida como software de acesso público e gratuito, disponível em ambiente '
    'web, permitindo o registro georreferenciado de matrizes florestais com informações botânicas '
    'e fenológicas relevantes. A metodologia de desenvolvimento segue as etapas de ideação, '
    'prototipagem, testes com usuários reais, entrega e manutenção. O público-alvo direto da '
    'plataforma são profissionais e estudantes que trabalham com restauração florestal do Cerrado '
    'em Mato Grosso do Sul. Os resultados esperados incluem a entrega de um sistema funcional, '
    'testado com parceiros da UEMS, e disponível para uso da comunidade.'
)
set_label_text(doc.paragraphs[15], 'Resumo: ', resumo, J)

set_label_text(doc.paragraphs[17], 'Palavras-chave: ',
               'Cerrado. Matrizes florestais. Desenvolvimento de software. Extensão universitária. Restauração florestal.')

# Introdução
replace_para(doc.paragraphs[19], 'Introdução', bold=True, alignment=J)

intro1 = (
    'O Cerrado é o segundo maior bioma brasileiro e um dos 25 hotspots mundiais de biodiversidade '
    '(MYERS et al., 2000). Em Mato Grosso do Sul, o bioma enfrenta contínua degradação pela '
    'expansão agropecuária, e a restauração florestal de áreas degradas depende diretamente da '
    'disponibilidade de sementes de espécies nativas com qualidade genética adequada. Para que a '
    'coleta seja geneticamente eficiente, a Instrução Normativa MMA n° 9/2011 recomenda a coleta '
    'a partir de pelo menos 12 indivíduos por espécie, suficientemente distantes entre si — o que '
    'exige conhecer previamente onde estão as árvores matrizes produtivas.'
)
intro2 = (
    'Esse conhecimento existe de forma dispersa entre técnicos, pesquisadores, estudantes e '
    'produtores rurais, mas raramente é sistematizado ou compartilhado publicamente. Profissionais '
    'que precisam coletar sementes para projetos de restauração frequentemente não sabem onde '
    'encontrar indivíduos adultos e produtivos das espécies que necessitam. Ferramentas genéricas '
    'de observação de biodiversidade, como o iNaturalist, não atendem às especificidades do '
    'contexto de coleta de sementes florestais: não registram fenologia produtiva, não distinguem '
    'matrizes de indivíduos jovens e não permitem a gestão de expedições de coleta.'
)
intro3 = (
    'A presente ação de extensão consiste no desenvolvimento da plataforma digital Penomato, '
    'realizado no âmbito do Trabalho de Conclusão de Curso em Tecnologia da Informação da UFMS, '
    'em diálogo direto com a realidade identificada junto ao curso de Engenharia Florestal da '
    'UEMS. A construção da ferramenta, desde a concepção até a entrega e manutenção, constitui '
    'em si a ação de extensão: um produto técnico desenvolvido a partir de uma demanda real da '
    'comunidade florestal sul-mato-grossense, disponibilizado gratuitamente para uso público.'
)
replace_para(doc.paragraphs[20], intro1, alignment=J)
replace_para(doc.paragraphs[21], intro2, alignment=J)
replace_para(doc.paragraphs[22], intro3, alignment=J)

# Objetivo geral
replace_para(doc.paragraphs[24], 'Objetivo geral', bold=True, alignment=J)
og = (
    'Desenvolver e disponibilizar a plataforma digital Penomato como ferramenta colaborativa e '
    'de acesso público para o mapeamento georreferenciado de matrizes florestais nativas do '
    'Cerrado, atendendo à demanda de engenheiros florestais, técnicos ambientais e estudantes '
    'que atuam com coleta de sementes e restauração florestal em Mato Grosso do Sul.'
)
replace_para(doc.paragraphs[25], og, alignment=J)

# Objetivos específicos
replace_para(doc.paragraphs[27], 'Objetivos específicos', bold=True, alignment=J)
oe = [
    'Levantar e validar os requisitos funcionais da plataforma junto a parceiros do curso de Engenharia Florestal da UEMS, identificando as necessidades reais dos profissionais que trabalham com coleta de sementes nativas.',
    'Desenvolver e publicar a plataforma Penomato com funcionalidades de cadastro georreferenciado de matrizes florestais, visualização em mapa interativo e gestão de registros por espécie.',
    'Realizar ciclos de testes com usuários reais — estudantes e profissionais parceiros da UEMS — e incorporar os feedbacks na entrega final da plataforma.',
]
replace_para(doc.paragraphs[28], oe[0], alignment=J)
replace_para(doc.paragraphs[29], oe[1], alignment=J)
replace_para(doc.paragraphs[30], oe[2], alignment=J)

# Metodologia
replace_para(doc.paragraphs[33], 'Metodologia', bold=True)

t0 = doc.tables[0]
met_rows = [
    ('Ideação',
     'Levantamento do problema junto a profissionais e estudantes de Engenharia Florestal da UEMS; '
     'pesquisa de soluções existentes e suas limitações; definição dos requisitos funcionais e '
     'não-funcionais da plataforma; elaboração do modelo de dados e arquitetura do sistema.'),
    ('Prototipagem',
     'Desenvolvimento iterativo da plataforma Penomato: modelagem do banco de dados, implementação '
     'do sistema de cadastro de matrizes com georreferenciamento, mapa interativo com filtros por '
     'espécie, painel de gestão e sistema de validação científica dos registros.'),
    ('Testes',
     'Realização de testes funcionais e de usabilidade com usuários reais parceiros da UEMS; '
     'coleta de feedback estruturado sobre fluxo de uso, clareza das informações e adequação '
     'às necessidades de campo; correção de bugs e ajustes de interface com base nos retornos.'),
    ('Entrega',
     'Publicação da plataforma em ambiente de produção com acesso público e gratuito; '
     'disponibilização do mapa de matrizes cadastradas; divulgação da ferramenta para a '
     'comunidade florestal de Mato Grosso do Sul em parceria com a UEMS.'),
    ('Manutenção',
     'Monitoramento do funcionamento da plataforma em produção; correção de falhas reportadas '
     'pelos usuários; implementação de melhorias incrementais com base no uso real; '
     'documentação técnica do sistema para continuidade futura do projeto.'),
]

# Garante linhas suficientes na tabela de metodologia
while len(t0.rows) < len(met_rows) + 1:
    clone_row(t0)

for i, (etapa, desc) in enumerate(met_rows):
    row = t0.rows[i + 1]
    row.cells[0].paragraphs[0].clear()
    row.cells[0].paragraphs[0].add_run(etapa).bold = True
    row.cells[1].paragraphs[0].clear()
    row.cells[1].paragraphs[0].add_run(desc)

# Cronograma
replace_para(doc.paragraphs[35], 'Cronograma de execução', bold=True)
replace_para(doc.paragraphs[36], 'Cada etapa contempla as atividades detalhadas abaixo.')
replace_para(doc.paragraphs[37], '')
replace_para(
    doc.paragraphs[38],
    'O cronograma totaliza 68 horas, distribuídas entre as fases de desenvolvimento da plataforma: '
    'ideação, prototipagem, testes com usuários, entrega e manutenção.',
    alignment=J
)

t1 = doc.tables[1]
crono_rows = [
    ('Ideação',    'Levantamento de requisitos com parceiros da UEMS, pesquisa de soluções e definição da arquitetura',      '19/08 a 10/09/2025', '12h'),
    ('Prototipagem','Desenvolvimento da plataforma: banco de dados, cadastro de matrizes, mapa interativo e painel de gestão', '11/09 a 31/10/2025', '24h'),
    ('Testes',     'Testes funcionais e de usabilidade com usuários reais; coleta de feedback e correção de bugs',            '03/11 a 21/11/2025', '16h'),
    ('Entrega',    'Publicação em produção, divulgação da plataforma e disponibilização pública do mapa de matrizes',         '24/11 a 05/12/2025', '10h'),
    ('Manutenção', 'Monitoramento em produção, correção de falhas reportadas e melhorias incrementais',                       '08/12 a 19/12/2025', '6h'),
]

while len(t1.rows) < len(crono_rows) + 1:
    clone_row(t1)

for i, (etapa, det, per, ch) in enumerate(crono_rows):
    row = t1.rows[i + 1]
    row.cells[0].paragraphs[0].clear()
    row.cells[0].paragraphs[0].add_run(etapa).bold = True
    row.cells[1].paragraphs[0].clear()
    row.cells[1].paragraphs[0].add_run(det)
    row.cells[2].paragraphs[0].clear()
    row.cells[2].paragraphs[0].add_run(per)
    row.cells[3].paragraphs[0].clear()
    row.cells[3].paragraphs[0].add_run(ch).bold = True

# Recursos
replace_para(doc.paragraphs[40], 'Recursos', bold=True)
recursos = (
    '• Computador para desenvolvimento: editor de código, servidor local (XAMPP) e navegador web\n'
    '• Linguagens e tecnologias: PHP, MySQL, JavaScript, Leaflet.js, HTML5 e CSS3\n'
    '• Plataforma Penomato em produção (acesso gratuito via navegador)\n'
    '• API de mapas para georreferenciamento dos registros de matrizes\n'
    '• Ferramentas de comunicação com parceiros da UEMS para levantamento de requisitos e testes\n'
    '• Formulário digital de feedback para coleta de avaliações dos usuários durante os testes'
)
replace_para(doc.paragraphs[41], recursos)

# Resultados esperados
replace_para(doc.paragraphs[43], 'Resultados esperados', bold=True, alignment=J)
resultados = (
    '• Plataforma Penomato funcional, publicada e disponível gratuitamente para uso público\n'
    '• Sistema de cadastro georreferenciado de matrizes florestais com mapa interativo por espécie\n'
    '• Validação da ferramenta com usuários reais parceiros da UEMS, com feedback incorporado\n'
    '• Redução da barreira de acesso à informação sobre localização de matrizes para coleta de sementes\n'
    '• Base tecnológica estabelecida para expansão futura do banco colaborativo de matrizes do Cerrado'
)
replace_para(doc.paragraphs[44], resultados, alignment=J)

# Feedback
replace_para(doc.paragraphs[46], 'Feedback dos participantes', bold=True, alignment=J)
fb1 = (
    'A coleta de feedback será realizada por meio de formulário digital aplicado aos usuários '
    'parceiros da UEMS ao longo da fase de testes e após a entrega da plataforma.'
)
fb2 = (
    'O formulário contemplará: (1) avaliação da facilidade de uso da plataforma em campo e no '
    'computador (escala de 1 a 5); (2) percepção sobre a utilidade das informações disponíveis '
    'para a tomada de decisão na coleta de sementes (escala de 1 a 5); (3) avaliação da '
    'adequação da plataforma às necessidades reais do trabalho florestal (resposta aberta); '
    '(4) identificação de funcionalidades ausentes ou que deveriam ser aprimoradas; '
    '(5) intenção de uso continuado e de indicação da plataforma para outros profissionais '
    '(sim/não com justificativa). Os resultados serão analisados ao final da fase de testes '
    'e incorporados à fase de manutenção como base para melhorias prioritárias.'
)
replace_para(doc.paragraphs[47], fb1)
replace_para(doc.paragraphs[48], fb2)

# Referências
replace_para(doc.paragraphs[50], 'Referências', bold=True)
refs = (
    'BRASIL. Instrução Normativa MMA n° 9, de 6 de julho de 2011. Estabelece diretrizes para '
    'coleta de germoplasma vegetal. Diário Oficial da União, Brasília, DF, 2011.\n\n'
    'KAGEYAMA, P. Y.; GANDARA, F. B. Recuperação de áreas ciliares. In: RODRIGUES, R. R.; '
    'LEITÃO FILHO, H. F. (Ed.). Matas ciliares: conservação e recuperação. São Paulo: EDUSP, 2000.\n\n'
    'MYERS, N. et al. Biodiversity hotspots for conservation priorities. Nature, v. 403, '
    'p. 853-858, 2000.\n\n'
    'REFLORA — Herbário Virtual. Jardim Botânico do Rio de Janeiro. Disponível em: '
    '<http://reflora.jbrj.gov.br>. Acesso em: 20 maio 2026.'
)
replace_para(doc.paragraphs[51], refs, alignment=J)

doc.save('modulo2_planejamento_extensao_penomato.docx')
print('Arquivo gerado com sucesso.')
