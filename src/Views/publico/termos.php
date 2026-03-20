<?php
$titulo_pagina = 'Termo de Uso — Penomato';
$descricao_pagina = 'Termos e condições de uso da plataforma Penomato';
require_once __DIR__ . '/../includes/cabecalho.php';
?>

<main class="container" style="max-width: 820px; padding-top: 2rem; padding-bottom: 4rem;">

    <!-- Cabeçalho da página -->
    <div class="text-center mb-4">
        <i class="fas fa-file-contract fa-3x mb-3" style="color: var(--cor-primaria);"></i>
        <h1 class="fw-bold" style="color: var(--cor-primaria);">Termo de Uso</h1>
        <p class="text-muted">Versão 1.0 &mdash; vigência a partir de [DATA DE PUBLICAÇÃO]</p>
    </div>

    <!-- Card resumido -->
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body p-4">

            <p class="text-muted mb-4">
                O <strong>Penomato</strong> é uma plataforma científica e educacional para documentação de
                plantas nativas do Cerrado, desenvolvida em parceria entre UFMS e UEMS.
                Ao usar a plataforma, você concorda com os pontos abaixo.
            </p>

            <div class="row g-4">

                <div class="col-md-6">
                    <div class="d-flex gap-3">
                        <div style="min-width:36px;">
                            <i class="fas fa-search fa-lg" style="color: var(--cor-primaria); margin-top: 3px;"></i>
                        </div>
                        <div>
                            <h6 class="fw-semibold mb-1">O que você pode fazer</h6>
                            <p class="text-muted small mb-0">
                                Consultar todas as espécies publicadas gratuitamente, sem cadastro.
                                Usar e compartilhar o conteúdo para fins acadêmicos ou pessoais,
                                desde que cite a fonte e os autores.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="d-flex gap-3">
                        <div style="min-width:36px;">
                            <i class="fas fa-hand-holding fa-lg" style="color: var(--cor-primaria); margin-top: 3px;"></i>
                        </div>
                        <div>
                            <h6 class="fw-semibold mb-1">O que esperamos de você ao contribuir</h6>
                            <p class="text-muted small mb-0">
                                Envie apenas fotos que você mesmo tirou ou para as quais tem autorização.
                                Insira dados com base em fontes reais e devidamente citadas.
                                Confirme um atributo somente após verificar a informação.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="d-flex gap-3">
                        <div style="min-width:36px;">
                            <i class="fas fa-id-badge fa-lg" style="color: var(--cor-primaria); margin-top: 3px;"></i>
                        </div>
                        <div>
                            <h6 class="fw-semibold mb-1">Seus créditos são permanentes</h6>
                            <p class="text-muted small mb-0">
                                Seu nome ficará registrado em todas as publicações nas quais você contribuiu
                                — como fotógrafo, coletor de dados ou revisor. Esse registro não pode ser removido.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="d-flex gap-3">
                        <div style="min-width:36px;">
                            <i class="fas fa-creative-commons fa-lg" style="color: var(--cor-primaria); margin-top: 3px;"></i>
                        </div>
                        <div>
                            <h6 class="fw-semibold mb-1">Licença do conteúdo</h6>
                            <p class="text-muted small mb-0">
                                Todo o conteúdo publicado está sob
                                <strong>Creative Commons CC BY 4.0</strong>: livre para usar e compartilhar,
                                com atribuição obrigatória aos autores.
                                O código-fonte da plataforma está sob <strong>MIT License</strong>.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="d-flex gap-3">
                        <div style="min-width:36px;">
                            <i class="fas fa-exclamation-triangle fa-lg" style="color: var(--cor-primaria); margin-top: 3px;"></i>
                        </div>
                        <div>
                            <h6 class="fw-semibold mb-1">Conduta</h6>
                            <p class="text-muted small mb-0">
                                Dados fabricados, imagens falsas ou plágio resultam em suspensão da conta
                                e comunicação à sua instituição de origem.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="d-flex gap-3">
                        <div style="min-width:36px;">
                            <i class="fas fa-university fa-lg" style="color: var(--cor-primaria); margin-top: 3px;"></i>
                        </div>
                        <div>
                            <h6 class="fw-semibold mb-1">Gestão institucional</h6>
                            <p class="text-muted small mb-0">
                                A plataforma é mantida por UFMS e UEMS.
                                O acesso para contribuição requer aprovação do gestor institucional.
                            </p>
                        </div>
                    </div>
                </div>

            </div><!-- /row -->

        </div><!-- /card-body -->

        <div class="card-footer bg-white border-0 px-4 pb-4 pt-0 d-flex align-items-center gap-3 flex-wrap">
            <button
                type="button"
                class="btn btn-outline-secondary btn-sm"
                data-bs-toggle="modal"
                data-bs-target="#modalTermoCompleto">
                <i class="fas fa-file-alt me-1"></i> Ver termo completo
            </button>
            <span class="text-muted small">
                Dúvidas?
                <a href="/penomato_mvp/src/Views/publico/contato.php" style="color: var(--cor-primaria);">Entre em contato</a>
            </span>
        </div>

    </div><!-- /card -->

</main>

<!-- ============================================================ -->
<!-- MODAL — TERMO COMPLETO                                       -->
<!-- ============================================================ -->

<div class="modal fade" id="modalTermoCompleto" tabindex="-1" aria-labelledby="modalTermoCompletoLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">

            <div class="modal-header" style="background: var(--cor-primaria);">
                <h5 class="modal-title text-white fw-bold" id="modalTermoCompletoLabel">
                    <i class="fas fa-file-contract me-2"></i> Termo de Uso — Penomato
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>

            <div class="modal-body px-4 py-4" style="font-size: 0.92rem; line-height: 1.75;">

                <p class="text-muted mb-4">
                    <strong>Versão 1.0</strong> &mdash; vigência a partir de [DATA DE PUBLICAÇÃO]<br>
                    Instituições mantenedoras: UFMS e UEMS
                </p>

                <!-- PREÂMBULO -->
                <h6 class="fw-bold text-uppercase mb-2" style="color: var(--cor-primaria); letter-spacing:.05em;">Preâmbulo</h6>
                <p>
                    O presente Termo de Uso regula as condições de acesso, utilização e contribuição à plataforma
                    <strong>Penomato</strong>, sistema colaborativo de documentação fitomorfológica de espécies nativas
                    do Cerrado brasileiro, desenvolvido em parceria acadêmica entre a Universidade Federal de Mato
                    Grosso do Sul (UFMS) e o Departamento de Botânica do curso de Engenharia Florestal da
                    Universidade Estadual de Mato Grosso do Sul (UEMS).
                </p>
                <p>
                    Ao acessar ou utilizar a plataforma Penomato, o usuário declara ter lido, compreendido e concordado
                    integralmente com os termos e condições aqui estabelecidos.
                </p>

                <hr>

                <!-- CAP I -->
                <h6 class="fw-bold text-uppercase mb-2" style="color: var(--cor-primaria); letter-spacing:.05em;">Capítulo I — Da Plataforma e sua Finalidade</h6>
                <p><strong>Art. 1º</strong> A plataforma Penomato é um sistema de informação de natureza científico-educacional, de acesso público, destinado à documentação colaborativa de características fitomorfológicas de espécies vegetais nativas do bioma Cerrado, com ênfase no estado de Mato Grosso do Sul.</p>
                <p><strong>Art. 2º</strong> A plataforma tem como objetivos: (I) reunir, organizar e publicar descrições morfológicas de espécies vegetais com rigor científico; (II) registrar fotografias de exsicatas de campo vinculadas a indivíduos físicos identificados; (III) produzir artigos científicos colaborativos submetidos à revisão por especialistas antes da publicação; (IV) promover a educação ambiental e a difusão do conhecimento botânico.</p>
                <p><strong>Art. 3º</strong> O conteúdo publicado na plataforma é de natureza científica e não possui finalidade comercial.</p>

                <hr>

                <!-- CAP II -->
                <h6 class="fw-bold text-uppercase mb-2" style="color: var(--cor-primaria); letter-spacing:.05em;">Capítulo II — Do Acesso e dos Perfis de Usuário</h6>
                <p><strong>Art. 4º</strong> O acesso à consulta de espécies publicadas é livre e não requer cadastro.</p>
                <p><strong>Art. 5º</strong> A participação como contribuidor ativo requer cadastro prévio aprovado pelo gestor institucional responsável.</p>
                <p><strong>Art. 6º</strong> A plataforma reconhece os seguintes perfis de usuário: (I) <strong>Colaborador</strong> — insere dados morfológicos e envia fotografias; (II) <strong>Gestor</strong> — administra o fluxo de documentação e atribui responsabilidades; (III) <strong>Especialista</strong> — revisa e aprova artigos antes da publicação.</p>
                <p><strong>Art. 7º</strong> Cada perfil possui limites operacionais definidos pelo sistema, sendo vedado contornar as restrições associadas ao seu nível de acesso.</p>

                <hr>

                <!-- CAP III -->
                <h6 class="fw-bold text-uppercase mb-2" style="color: var(--cor-primaria); letter-spacing:.05em;">Capítulo III — Das Licenças</h6>
                <p><strong>Art. 8º</strong> Todo o conteúdo científico publicado — artigos, fichas de espécies, descrições morfológicas, dados de coleta e fotografias — é disponibilizado sob a licença <strong>Creative Commons Atribuição 4.0 Internacional (CC BY 4.0)</strong>.</p>
                <p><strong>§ 1º</strong> Nos termos da licença CC BY 4.0, qualquer pessoa é livre para compartilhar, copiar, redistribuir, adaptar e criar a partir do material para qualquer fim, inclusive comercial.</p>
                <p><strong>§ 2º</strong> O exercício desses direitos está condicionado à atribuição adequada de crédito, com indicação do nome dos autores, da plataforma Penomato e das instituições mantenedoras.</p>
                <p><strong>§ 3º</strong> Os créditos dos colaboradores são permanentes e não podem ser removidos por qualquer usuário ou administrador da plataforma.</p>
                <p><strong>Art. 9º</strong> O código-fonte da plataforma Penomato é disponibilizado sob a <strong>MIT License</strong>, permitindo uso, cópia, modificação e distribuição, desde que mantidos os avisos de copyright originais.</p>

                <hr>

                <!-- CAP IV -->
                <h6 class="fw-bold text-uppercase mb-2" style="color: var(--cor-primaria); letter-spacing:.05em;">Capítulo IV — Das Responsabilidades do Colaborador</h6>
                <p><strong>Art. 10</strong> Ao submeter dados morfológicos, o colaborador declara que: (I) os dados são transcrições fiéis de fontes devidamente identificadas ou observações diretas fundamentadas; (II) as fontes foram corretamente citadas; (III) os dados não foram fabricados, falsificados ou copiados sem atribuição.</p>
                <p><strong>Art. 11</strong> Ao enviar fotografias, o colaborador declara que: (I) é o autor das imagens ou possui autorização expressa do autor; (II) as imagens não violam direitos de terceiros; (III) concorda com a publicação sob CC BY 4.0 com atribuição permanente ao fotógrafo.</p>
                <p><strong>Art. 12</strong> O ato de confirmar um atributo morfológico constitui declaração formal de responsabilidade científica do colaborador.</p>
                <p><strong>Art. 13</strong> É vedado ao colaborador: (I) enviar imagens sem autorização; (II) inserir dados sem fonte; (III) confirmar atributos sem verificação; (IV) utilizar a plataforma para fins alheios à sua finalidade científico-educacional.</p>

                <hr>

                <!-- CAP V -->
                <h6 class="fw-bold text-uppercase mb-2" style="color: var(--cor-primaria); letter-spacing:.05em;">Capítulo V — Da Atribuição e dos Créditos</h6>
                <p><strong>Art. 14</strong> O sistema registra automaticamente e de forma permanente a identidade de todos os contribuidores, incluindo: coletor de dados, fotógrafo(s) e especialista revisor.</p>
                <p><strong>Art. 15</strong> Os créditos registrados não podem ser alterados ou suprimidos após o envio, exceto mediante solicitação formal por erro material comprovado.</p>
                <p><strong>Art. 16</strong> As instituições mantenedoras poderão utilizar os dados e artigos publicados em publicações acadêmicas e materiais de divulgação, sempre mantendo os créditos individuais.</p>

                <hr>

                <!-- CAP VI -->
                <h6 class="fw-bold text-uppercase mb-2" style="color: var(--cor-primaria); letter-spacing:.05em;">Capítulo VI — Da Integridade Científica</h6>
                <p><strong>Art. 17</strong> A plataforma adota os princípios de integridade científica estabelecidos pelo CNPq e pelas normas institucionais da UFMS e UEMS.</p>
                <p><strong>Art. 18</strong> Constituem violações graves passíveis de suspensão e comunicação institucional: (I) fabricação de dados ou metadados; (II) submissão de imagens falsas ou manipuladas; (III) plágio de descrições morfológicas; (IV) contestação maliciosa de identificações publicadas.</p>
                <p><strong>Art. 19</strong> O sistema mantém registro histórico imutável de todas as operações, constituindo trilha de auditoria permanente.</p>

                <hr>

                <!-- CAP VII -->
                <h6 class="fw-bold text-uppercase mb-2" style="color: var(--cor-primaria); letter-spacing:.05em;">Capítulo VII — Do Fluxo de Publicação e da Garantia de Qualidade</h6>
                <p><strong>Art. 20</strong> Nenhum artigo é publicado sem revisão prévia por especialista habilitado.</p>
                <p><strong>Art. 21</strong> As instituições mantenedoras não se responsabilizam por imprecisões em conteúdo que não tenha concluído o fluxo de revisão.</p>
                <p><strong>Art. 22</strong> Em caso de contestação de identificação publicada, o sistema inicia novo ciclo de revisão, preservando o histórico completo de edições anteriores.</p>

                <hr>

                <!-- CAP VIII -->
                <h6 class="fw-bold text-uppercase mb-2" style="color: var(--cor-primaria); letter-spacing:.05em;">Capítulo VIII — Dos Dados Pessoais</h6>
                <p><strong>Art. 23</strong> A plataforma coleta: nome completo, e-mail, instituição de vínculo e perfil de acesso.</p>
                <p><strong>Art. 24</strong> Os dados pessoais são utilizados exclusivamente para: (I) autenticação; (II) atribuição de créditos; (III) comunicação sobre contribuições.</p>
                <p><strong>Art. 25</strong> O nome do colaborador é exibido publicamente nas publicações das quais participou, em razão das exigências da licença CC BY 4.0.</p>
                <p><strong>Art. 26</strong> A exclusão da conta não implica remoção dos créditos em publicações já realizadas.</p>

                <hr>

                <!-- CAP IX -->
                <h6 class="fw-bold text-uppercase mb-2" style="color: var(--cor-primaria); letter-spacing:.05em;">Capítulo IX — Das Disposições Finais</h6>
                <p><strong>Art. 27</strong> As instituições mantenedoras reservam-se o direito de atualizar este Termo a qualquer tempo, com publicação da nova versão com antecedência mínima de 15 dias.</p>
                <p><strong>Art. 28</strong> O uso continuado da plataforma após nova versão implica aceitação das alterações.</p>
                <p><strong>Art. 29</strong> Os casos omissos serão resolvidos pelos gestores institucionais, à luz dos princípios da integridade científica e da boa-fé.</p>
                <p><strong>Art. 30</strong> Fica eleito o foro da Comarca de Campo Grande, Mato Grosso do Sul, para dirimir quaisquer controvérsias, com renúncia a qualquer outro.</p>

                <p class="text-muted small mt-4 mb-0">
                    <em>Plataforma Penomato — desenvolvida em parceria UFMS/UEMS &mdash; Bioma Cerrado, Mato Grosso do Sul, Brasil.</em>
                </p>

            </div><!-- /modal-body -->

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Fechar</button>
            </div>

        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/rodape.php'; ?>
