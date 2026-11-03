"""
Gera o Plano de Integração e Entrega Contínua (Módulo 4 - PI3)
baseado no pipeline real do Penomato (GitHub Actions → HostGator).
"""

from docx import Document
from docx.shared import Pt, RGBColor, Inches
from docx.enum.text import WD_ALIGN_PARAGRAPH
from docx.oxml.ns import qn
from docx.oxml import OxmlElement

OUTPUT = 'plano_cicd_penomato.docx'

doc = Document()

# ─── Estilos globais ────────────────────────────────────────────────────────
style = doc.styles['Normal']
style.font.name = 'Arial'
style.font.size = Pt(12)

for section in doc.sections:
    section.top_margin    = Inches(1)
    section.bottom_margin = Inches(1)
    section.left_margin   = Inches(1.18)
    section.right_margin  = Inches(1.18)

J = WD_ALIGN_PARAGRAPH.JUSTIFY
C = WD_ALIGN_PARAGRAPH.CENTER

# ─── Helpers ────────────────────────────────────────────────────────────────
def heading(text, level=1):
    p = doc.add_paragraph()
    p.alignment = J
    run = p.add_run(text)
    run.bold = True
    run.font.size = Pt(12 if level > 1 else 12)
    return p

def body(text, align=J):
    p = doc.add_paragraph()
    p.alignment = align
    p.add_run(text)
    return p

def label_value(label, value, align=J):
    p = doc.add_paragraph()
    p.alignment = align
    r1 = p.add_run(label)
    r1.bold = True
    p.add_run(value)
    return p

def spacer():
    doc.add_paragraph()

# ─── Cabeçalho ──────────────────────────────────────────────────────────────
body('Projeto de Extensão: Programa de Extensão UFMS Digital (95DX7.200525)', align=J)
label_value('Nome completo: ', 'Nilton Dobes Bakargi')
label_value('Disciplina: ', 'Projeto Integrador de Tecnologia da Informação III — T01-2026-1')
label_value('Semestre letivo: ', '2026/1')
label_value('Curso: ', 'Tecnologia da Informação')

spacer()

# ─── Título ─────────────────────────────────────────────────────────────────
titulo = doc.add_paragraph()
titulo.alignment = C
r = titulo.add_run(
    'PLANO DE INTEGRAÇÃO E ENTREGA CONTÍNUA PARA O SISTEMA PENOMATO '
    'DE CATALOGAÇÃO DE ESPÉCIES NATIVAS DO CERRADO'
)
r.bold = True
r.font.size = Pt(12)

spacer()

# ─── Resumo ─────────────────────────────────────────────────────────────────
label_value(
    'Resumo: ',
    'O presente plano descreve a estratégia de Integração Contínua (CI) e Entrega Contínua (CD) '
    'adotada no desenvolvimento da plataforma Penomato, sistema web colaborativo para catalogação '
    'de espécies arbóreas nativas do Cerrado. A infraestrutura de CI/CD automatiza a verificação '
    'de dependências, a geração segura das configurações de produção e o envio incremental dos '
    'arquivos ao servidor sempre que alterações são mescladas à branch principal, eliminando '
    'deploys manuais e reduzindo o risco de erros operacionais. O pipeline foi construído com '
    'GitHub Actions e integrado ao servidor de produção HostGator via protocolo FTP, com '
    'separação clara entre o ambiente de desenvolvimento local (XAMPP) e o ambiente público '
    '(penomato.app.br).',
    align=J
)

spacer()

label_value(
    'Palavras-chave: ',
    'Integração Contínua. Entrega Contínua. GitHub Actions. Deploy automatizado. '
    'Desenvolvimento web ágil.'
)

spacer()

# ─── Introdução ─────────────────────────────────────────────────────────────
heading('Introdução')

body(
    'O Penomato é uma plataforma web de ciência cidadã desenvolvida como Trabalho de Conclusão '
    'de Curso em Tecnologia da Informação na UFMS, em parceria com pesquisadores do curso de '
    'Engenharia Florestal da Universidade Estadual de Mato Grosso do Sul (UEMS). O sistema '
    'permite que colaboradores registrem espécies arbóreas nativas do Cerrado com dados '
    'morfológicos, fotografias de campo e localização georreferenciada, com cada contribuição '
    'submetida a revisão especializada antes de ser publicada no acervo público. A plataforma '
    'está disponível em ambiente de produção no endereço penomato.app.br, hospedada na '
    'HostGator.',
    align=J
)

body(
    'O desenvolvimento ocorre em ambiente local com XAMPP, com o código versionado no GitHub. '
    'Cada alteração aprovada pela equipe é mesclada à branch principal e automaticamente '
    'publicada no servidor de produção por meio de um pipeline de CI/CD implementado com '
    'GitHub Actions. Esse fluxo — já operacional — elimina a necessidade de deploys manuais '
    'via FTP e garante que cada versão em produção é rastreável a um commit específico no '
    'repositório.',
    align=J
)

body(
    'O objetivo deste plano é formalizar, descrever e propor melhorias para as práticas de '
    'CI/CD em uso no projeto, apresentando as ferramentas envolvidas, as atividades '
    'automatizadas, as justificativas técnicas de cada decisão e as estratégias de '
    'monitoramento e revisão contínua do pipeline.',
    align=J
)

spacer()

# ─── Escopo e tecnologias ───────────────────────────────────────────────────
heading('Escopo e tecnologias')

body(
    'O escopo do plano de CI/CD abrange todo o ciclo de desenvolvimento da plataforma Penomato: '
    'desde a escrita do código no ambiente local até a publicação automática no servidor de '
    'produção. O pipeline cobre a verificação de dependências, a geração segura das '
    'configurações de ambiente e a transferência incremental dos arquivos ao servidor.',
    align=J
)

body('As ferramentas e plataformas utilizadas são:', align=J)

tecnologias = [
    ('Linguagem de back-end', 'PHP 8.x — linguagem principal da aplicação'),
    ('Banco de dados', 'MySQL — armazenamento de espécies, usuários, exemplares e histórico'),
    ('Front-end', 'HTML5, CSS3, JavaScript (vanilla) — interface sem frameworks externos'),
    ('Versionamento', 'Git/GitHub — controle de versão e repositório central'),
    ('CI/CD', 'GitHub Actions — orquestrador dos workflows de integração e entrega contínua'),
    ('Servidor de produção', 'HostGator — hospedagem compartilhada sob o domínio penomato.app.br'),
    ('Ambiente de desenvolvimento', 'XAMPP (Windows) — servidor local Apache + PHP + MySQL'),
    ('Gerenciador de dependências', 'Composer — instalação do PHPMailer e demais pacotes PHP'),
    ('Protocolo de deploy', 'FTP — transferência de arquivos via SamKirkland/FTP-Deploy-Action@v4.3.5'),
    ('Gerenciamento de segredos', 'GitHub Secrets — armazenamento seguro de credenciais de banco e FTP'),
]

table = doc.add_table(rows=1, cols=2)
table.style = 'Table Grid'
hdr = table.rows[0].cells
hdr[0].text = 'Ferramenta / Plataforma'
hdr[1].text = 'Descrição e propósito'
for cell in hdr:
    for p in cell.paragraphs:
        for run in p.runs:
            run.bold = True

for nome, desc in tecnologias:
    row = table.add_row().cells
    row[0].text = nome
    row[1].text = desc

spacer()

body('Os ambientes do projeto e seus propósitos são:', align=J)

body(
    'Ambiente de desenvolvimento (XAMPP local): servidor Apache e MySQL executados na máquina '
    'do desenvolvedor. As configurações de banco de dados ficam em config/database.php, arquivo '
    'que está no .gitignore e nunca é enviado ao repositório. É o ambiente de escrita de código, '
    'testes manuais e depuração. Não tem acesso externo.',
    align=J
)

body(
    'Ambiente de produção (HostGator — penomato.app.br): servidor Linux compartilhado na '
    'HostGator, acessível publicamente. As configurações de banco de dados e URL são '
    'fornecidas pelo arquivo config/producao.php, que não existe no repositório — ele é '
    'gerado automaticamente durante o pipeline a partir dos GitHub Secrets, garantindo que '
    'credenciais de produção nunca sejam expostas no código-fonte.',
    align=J
)

spacer()

# ─── Práticas de CI ─────────────────────────────────────────────────────────
heading('Práticas de CI')

body(
    'A Integração Contínua (CI) abrange as atividades automatizadas que ocorrem antes do '
    'deploy, com o objetivo de verificar que o código está correto, completo e pronto para '
    'ser publicado. No Penomato, o pipeline de CI é disparado automaticamente a cada push '
    'na branch principal (main) e executa as seguintes atividades:',
    align=J
)

# CI 1
heading('CI-1 — Checkout do repositório', level=2)
body(
    'Descrição: a primeira etapa do pipeline baixa a versão exata do código que gerou o '
    'evento de push, utilizando a action oficial actions/checkout@v4. Isso garante que todas '
    'as etapas seguintes operam sobre o estado real e atual do repositório, sem '
    'inconsistências entre o que foi commitado e o que será publicado.',
    align=J
)
body(
    'Justificativa: sem essa etapa, o ambiente de execução do GitHub Actions não teria acesso '
    'ao código do projeto. A ação garante reprodutibilidade: o mesmo commit sempre produz o '
    'mesmo resultado, independentemente de quando o pipeline for reexecutado.',
    align=J
)
body(
    'Atividades necessárias para implantação: a ação já está implementada como primeiro passo '
    'do workflow. Nenhuma configuração adicional é necessária além da presença do arquivo '
    '.github/workflows/deploy.yml no repositório.',
    align=J
)

# CI 2
heading('CI-2 — Instalação de dependências via Composer', level=2)
body(
    'Descrição: o pipeline executa composer install --no-dev --optimize-autoloader para '
    'instalar as dependências PHP do projeto — principalmente o PHPMailer, utilizado para '
    'envio de e-mails transacionais (notificações de aprovação, convites de usuário). A '
    'flag --no-dev exclui pacotes de desenvolvimento, reduzindo o tamanho do pacote '
    'enviado ao servidor. A flag --optimize-autoloader gera um mapa de classes otimizado '
    'para melhor desempenho em produção.',
    align=J
)
body(
    'Justificativa: as dependências PHP não são versionadas no repositório (a pasta vendor/ '
    'está no .gitignore). Sem essa etapa, o deploy enviaria um projeto incompleto ao '
    'servidor. A instalação automatizada garante que a versão exata das dependências '
    'declaradas no composer.lock seja sempre utilizada, eliminando inconsistências entre '
    'o ambiente local e o ambiente de produção.',
    align=J
)
body(
    'Atividades necessárias para implantação: manter o arquivo composer.json atualizado '
    'sempre que uma nova dependência for adicionada; commitar o composer.lock junto com '
    'o composer.json para garantir versões fixas; verificar que o servidor de produção '
    'HostGator executa versão compatível de PHP.',
    align=J
)

# CI 3
heading('CI-3 — Geração segura do arquivo de configuração de produção', level=2)
body(
    'Descrição: o arquivo config/producao.php contém as credenciais de banco de dados e '
    'a URL da aplicação em produção. Esse arquivo não existe no repositório (está no '
    '.gitignore). Durante o pipeline, ele é gerado dinamicamente a partir dos GitHub '
    'Secrets — variáveis criptografadas armazenadas nas configurações do repositório: '
    'DB_HOST, DB_NAME, DB_USER, DB_PASS. O arquivo é criado com os valores corretos '
    'apenas no ambiente de execução do GitHub Actions e enviado ao servidor como parte '
    'do deploy.',
    align=J
)
body(
    'Justificativa: expor credenciais de banco de dados no código-fonte é uma das '
    'vulnerabilidades mais críticas em desenvolvimento web (OWASP A02:2021 — Cryptographic '
    'Failures). O uso de GitHub Secrets garante que senhas e nomes de banco nunca aparecem '
    'em nenhum commit, log público ou fork do repositório, mantendo as credenciais de '
    'produção isoladas e sob controle exclusivo do proprietário do projeto.',
    align=J
)
body(
    'Atividades necessárias para implantação: cadastrar os secrets DB_HOST, DB_NAME, '
    'DB_USER e DB_PASS nas configurações do repositório GitHub (Settings → Secrets and '
    'variables → Actions); manter o template de geração do arquivo no workflow atualizado '
    'sempre que novas constantes de configuração forem adicionadas ao projeto.',
    align=J
)

spacer()

# ─── Práticas de CD ─────────────────────────────────────────────────────────
heading('Práticas de CD')

body(
    'A Entrega Contínua (CD) abrange as atividades automatizadas que publicam o código '
    'verificado no ambiente de produção. O pipeline de CD do Penomato opera imediatamente '
    'após a conclusão bem-sucedida de todas as etapas de CI, sem intervenção manual.',
    align=J
)

# CD 1
heading('CD-1 — Deploy incremental via FTP', level=2)
body(
    'Descrição: a entrega é realizada por meio da action SamKirkland/FTP-Deploy-Action@v4.3.5, '
    'que compara o estado atual do servidor com o conteúdo do repositório e transfere apenas '
    'os arquivos que foram criados ou modificados desde o último deploy. As credenciais de '
    'acesso FTP (servidor, usuário, senha) são fornecidas pelos GitHub Secrets FTP_SERVER, '
    'FTP_USERNAME e FTP_PASSWORD.',
    align=J
)
body(
    'Justificativa: o deploy incremental reduz drasticamente o tempo de publicação — em vez '
    'de reenviar todos os arquivos do projeto a cada deploy, apenas os arquivos alterados são '
    'transferidos. Isso é especialmente relevante em hospedagem compartilhada, onde a '
    'velocidade de transferência FTP pode ser um gargalo. Além disso, o uso de secrets '
    'garante que as credenciais FTP nunca aparecem no histórico do repositório.',
    align=J
)
body(
    'Atividades necessárias para implantação: cadastrar os secrets FTP_SERVER, FTP_USERNAME '
    'e FTP_PASSWORD nas configurações do repositório; configurar o parâmetro server-dir com '
    'o diretório raiz correto da hospedagem; verificar o protocolo FTP suportado pelo '
    'servidor HostGator (porta 21, protocolo ftp).',
    align=J
)

# CD 2
heading('CD-2 — Exclusão de arquivos sensíveis e desnecessários do deploy', level=2)
body(
    'Descrição: o pipeline define uma lista explícita de padrões de arquivos que não devem '
    'ser transferidos ao servidor de produção, incluindo: diretórios de configuração local '
    '(.git/, .claude/), módulos de desenvolvimento (node_modules/), arquivos de banco de '
    'dados e migrações (database/), documentação interna (docs/), logs, scripts utilitários, '
    'uploads de usuários e o arquivo de configuração local config/database.php.',
    align=J
)
body(
    'Justificativa: enviar arquivos de configuração local, logs internos ou scripts de '
    'desenvolvimento ao servidor de produção representa tanto um risco de segurança quanto '
    'um problema de manutenção. A exclusão explícita garante que somente o código da '
    'aplicação chegue ao servidor, mantendo o ambiente de produção limpo e previsível.',
    align=J
)
body(
    'Atividades necessárias para implantação: revisar e atualizar a lista de exclusões '
    'sempre que novos diretórios ou tipos de arquivo forem adicionados ao projeto; '
    'documentar a razão de cada exclusão no arquivo deploy.yml para facilitar a manutenção.',
    align=J
)

# CD 3
heading('CD-3 — Proteção dos dados persistentes do servidor', level=2)
body(
    'Descrição: o parâmetro dangerous-clean-slate: false garante que o deploy nunca apaga '
    'arquivos que existem no servidor mas não estão no repositório. Isso protege '
    'especificamente as pastas uploads/ (imagens enviadas pelos colaboradores) e logs/ '
    '(registros de atividade do sistema), que são gerados em produção e não fazem parte '
    'do código versionado.',
    align=J
)
body(
    'Justificativa: a opção clean-slate, se ativada, apagaria todos os arquivos do servidor '
    'antes de cada deploy — incluindo as fotografias de campo enviadas pelos colaboradores, '
    'que são o principal conteúdo científico do sistema. A proteção desses dados é crítica: '
    'a perda de imagens de campo não pode ser desfeita e comprometeria o trabalho de todos '
    'os colaboradores cadastrados.',
    align=J
)
body(
    'Atividades necessárias para implantação: confirmar que a configuração '
    'dangerous-clean-slate: false está presente e mantida no workflow; documentar que '
    'os diretórios uploads/ e logs/ são excluídos do deploy justamente para serem '
    'preservados no servidor entre deployments.',
    align=J
)

spacer()

# ─── Monitoramento e revisão contínua ───────────────────────────────────────
heading('Monitoramento e revisão contínua')

body(
    'O monitoramento das atividades automatizadas do pipeline é realizado pelos seguintes meios:',
    align=J
)

body(
    'Logs de execução do GitHub Actions: cada execução do workflow gera um log completo '
    'com o resultado de cada etapa (sucesso ou falha), o tempo de execução, os arquivos '
    'transferidos e eventuais mensagens de erro. Esses logs ficam disponíveis na aba Actions '
    'do repositório e são retidos por 90 dias. O desenvolvedor responsável verifica os logs '
    'após cada push para confirmar que o deploy foi bem-sucedido.',
    align=J
)

body(
    'Notificações automáticas por e-mail: o GitHub envia automaticamente uma notificação '
    'por e-mail ao proprietário do repositório sempre que um workflow falha. Isso garante '
    'que qualquer falha no pipeline — seja por erro de código, dependência faltante ou '
    'problema de conexão FTP — seja identificada imediatamente, sem necessidade de '
    'monitoramento ativo.',
    align=J
)

body(
    'Verificação pós-deploy no ambiente de produção: após cada deploy bem-sucedido, '
    'é realizada uma verificação manual rápida na URL penomato.app.br para confirmar '
    'que a aplicação está respondendo corretamente. Essa verificação cobre as páginas '
    'principais (listagem de espécies, login, mapa de matrizes) e é executada pelo '
    'desenvolvedor imediatamente após a notificação de sucesso do workflow.',
    align=J
)

body(
    'Revisão periódica do workflow: mensalmente, o arquivo deploy.yml é revisado para '
    'verificar se as versões das actions utilizadas (actions/checkout@v4, '
    'SamKirkland/FTP-Deploy-Action@v4.3.5) têm atualizações disponíveis com correções '
    'de segurança ou melhorias relevantes. Da mesma forma, as dependências declaradas '
    'no composer.json são revisadas para identificar versões desatualizadas.',
    align=J
)

body(
    'Proposta de revisão contínua: a médio prazo, propõe-se a inclusão de uma etapa '
    'de verificação de sintaxe PHP (php -l) no pipeline de CI, que percorra todos os '
    'arquivos PHP antes do deploy e rejeite automaticamente qualquer commit com erro '
    'de sintaxe. Essa etapa é simples de implementar, não requer dependências externas '
    'e reduz o risco de publicar código quebrado em produção.',
    align=J
)

body(
    'Os benefícios do processo proposto incluem: rastreabilidade completa de cada versão '
    'publicada; identificação imediata de falhas no pipeline; proteção contra deploys '
    'acidentais de código inválido; redução do esforço manual de monitoramento; e '
    'histórico documentado de todas as entregas realizadas ao longo do desenvolvimento.',
    align=J
)

spacer()

# ─── Resultados esperados ───────────────────────────────────────────────────
heading('Resultados esperados')

body(
    'Com a implementação e manutenção do plano de CI/CD descrito neste documento, '
    'esperam-se os seguintes resultados para o desenvolvimento e operação da plataforma Penomato:',
    align=J
)

resultados = [
    'Eliminação de deploys manuais: nenhuma alteração de código precisará ser enviada '
    'manualmente ao servidor. Todo o processo de publicação ocorre de forma automática '
    'e reproduzível a cada merge na branch principal.',

    'Rastreabilidade total das entregas: cada versão em produção corresponde exatamente '
    'a um commit identificável no repositório GitHub, com data, autor e descrição da '
    'alteração. Isso torna possível reconstruir o histórico completo de entregas e '
    'identificar quando qualquer mudança foi publicada.',

    'Proteção de credenciais e dados sensíveis: senhas de banco de dados, usuários '
    'FTP e chaves de acesso nunca aparecem no código-fonte, nos commits ou nos logs '
    'públicos do repositório. O risco de exposição acidental de credenciais de produção '
    'é eliminado pelo uso exclusivo de GitHub Secrets.',

    'Preservação do conteúdo científico: as fotografias de campo enviadas pelos '
    'colaboradores e os logs de atividade do sistema são protegidos automaticamente '
    'a cada deploy, sem risco de perda acidental por sobrescrita.',

    'Ciclo de desenvolvimento mais ágil e confiável: o tempo entre a aprovação de '
    'uma alteração e sua disponibilidade em produção é reduzido de minutos (deploy '
    'manual) para o tempo de execução automática do pipeline, com garantia de que '
    'apenas código com dependências verificadas e configurações corretas é publicado.',

    'Base para evolução futura do pipeline: o pipeline atual, já funcional, serve '
    'como fundação para a inclusão progressiva de novas etapas de CI, como verificação '
    'de sintaxe PHP, testes automatizados e análise estática de código, conforme '
    'o projeto avança para além do MVP.',
]

for i, r in enumerate(resultados, 1):
    p = doc.add_paragraph(style='List Number')
    p.alignment = J
    p.add_run(r)

spacer()

# ─── Referências ────────────────────────────────────────────────────────────
heading('Referências')

refs = [
    'GITHUB. GitHub Actions documentation. Disponível em: <https://docs.github.com/actions>. '
    'Acesso em: 8 jun. 2026.',

    'KIRKLAND, Sam. FTP-Deploy-Action. GitHub, 2023. Disponível em: '
    '<https://github.com/SamKirkland/FTP-Deploy-Action>. Acesso em: 8 jun. 2026.',

    'COMPOSER. Introduction — Composer. Disponível em: <https://getcomposer.org/doc/00-intro.md>. '
    'Acesso em: 8 jun. 2026.',

    'HUMBLE, Jez; FARLEY, David. Continuous Delivery: Reliable Software Releases through '
    'Build, Test, and Deployment Automation. Boston: Addison-Wesley, 2010.',

    'OWASP. OWASP Top Ten 2021 — A02:2021 Cryptographic Failures. Open Web Application '
    'Security Project, 2021. Disponível em: <https://owasp.org/Top10/A02_2021-Cryptographic_Failures/>. '
    'Acesso em: 8 jun. 2026.',
]

for ref in refs:
    p = doc.add_paragraph()
    p.alignment = J
    p.add_run(ref)
    p.paragraph_format.first_line_indent = Pt(0)

# ─── Salva ──────────────────────────────────────────────────────────────────
doc.save(OUTPUT)
print(f'Documento gerado: {OUTPUT}')
