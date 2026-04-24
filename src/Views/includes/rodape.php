<?php
/**
 * RODAPÉ PADRÃO DO PENOMATO MVP
 * 
 * Inclui fechamento das tags HTML, scripts comuns,
 * informações institucionais e links úteis.
 * 
 * @package Penomato
 * @author Equipe Penomato
 * @version 1.0
 */

// ============================================================
// INICIALIZAÇÃO
// ============================================================

// Garantir que a sessão está iniciada (se precisar de dados no rodapé)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ============================================================
// CONFIGURAÇÕES DO RODAPÉ
// ============================================================

$ano_atual = date('Y');
$versao_sistema = 'MVP 1.0';

// ============================================================
// INÍCIO DO RODAPÉ
// ============================================================
?>

    <!-- ================================================== -->
    <!-- RODAPÉ PRINCIPAL -->
    <!-- ================================================== -->
    
    <footer class="footer-penomato mt-5">
        <div class="container-fluid">
            <div class="row">
                <!-- Coluna 1: Sobre -->
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="footer-logo">
                        <i class="fas fa-leaf"></i>
                        <span>Penomato</span>
                    </div>
                    <p class="footer-description">
                        Plataforma colaborativa para documentação botânica com validação científica. 
                        Construindo a enciclopédia viva da biodiversidade brasileira.
                    </p>
                    
                    <!-- Redes sociais -->
                    <div class="social-links mt-3">
                        <a href="#" class="social-link" title="Instagram">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="#" class="social-link" title="Facebook">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="social-link" title="Twitter/X">
                            <i class="fab fa-x-twitter"></i>
                        </a>
                        <a href="#" class="social-link" title="GitHub">
                            <i class="fab fa-github"></i>
                        </a>
                        <a href="#" class="social-link" title="YouTube">
                            <i class="fab fa-youtube"></i>
                        </a>
                    </div>
                </div>
                
                <!-- Coluna 2: Links Rápidos -->
                <div class="col-lg-2 col-md-6 mb-4">
                    <h5 class="footer-title">Links Rápidos</h5>
                    <ul class="footer-links">
                        <li>
                            <a href="/penomato_mvp/src/Views/publico/busca_caracteristicas.php">
                                <i class="fas fa-chevron-right"></i> Buscar Espécies
                            </a>
                        </li>
                        <li>
                            <a href="/penomato_mvp/src/Views/publico/sobre.php">
                                <i class="fas fa-chevron-right"></i> Sobre o Projeto
                            </a>
                        </li>
                        <li>
                            <a href="/penomato_mvp/src/Views/publico/contato.php">
                                <i class="fas fa-chevron-right"></i> Contato
                            </a>
                        </li>
                        <li>
                            <a href="/penomato_mvp/src/Views/publico/faq.php">
                                <i class="fas fa-chevron-right"></i> Perguntas Frequentes
                            </a>
                        </li>
                        <li>
                            <a href="/penomato_mvp/src/Views/publico/equipe.php">
                                <i class="fas fa-chevron-right"></i> Equipe
                            </a>
                        </li>
                    </ul>
                </div>
                
                <!-- Coluna 3: Para Colaboradores -->
                <div class="col-lg-2 col-md-6 mb-4">
                    <h5 class="footer-title">Para Colaboradores</h5>
                    <ul class="footer-links">
                        <li>
                            <a href="/penomato_mvp/src/Views/auth/cadastro.php">
                                <i class="fas fa-chevron-right"></i> Criar Conta
                            </a>
                        </li>
                        <li>
                            <a href="/penomato_mvp/src/Views/auth/login.php">
                                <i class="fas fa-chevron-right"></i> Acessar Sistema
                            </a>
                        </li>
                        <li>
                            <a href="/penomato_mvp/src/Views/publico/tutorial.php">
                                <i class="fas fa-chevron-right"></i> Tutorial de Uso
                            </a>
                        </li>
                        <li>
                            <a href="/penomato_mvp/src/Views/publico/manual-identificador.php">
                                <i class="fas fa-chevron-right"></i> Manual do Identificador
                            </a>
                        </li>
                        <li>
                            <a href="/penomato_mvp/src/Views/publico/politica-upload.php">
                                <i class="fas fa-chevron-right"></i> Política de Imagens
                            </a>
                        </li>
                    </ul>
                </div>
                
                <!-- Coluna 4: Contato e Localização -->
                <div class="col-lg-4 col-md-6 mb-4">
                    <h5 class="footer-title">Contato</h5>
                    
                    <div class="contact-info">
                        <div class="contact-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <span>
                                <strong>UEMS - Universidade Estadual de Mato Grosso do Sul</strong><br>
                                Dourados, MS - Brasil
                            </span>
                        </div>
                        
                        <div class="contact-item">
                            <i class="fas fa-envelope"></i>
                            <span>
                                <strong>Email:</strong> 
                                <a href="mailto:contato@penomato.org">contato@penomato.org</a>
                            </span>
                        </div>
                        
                        <div class="contact-item">
                            <i class="fas fa-phone"></i>
                            <span>
                                <strong>Telefone:</strong> 
                                <a href="tel:+5567999999999">(67) 99999-9999</a>
                            </span>
                        </div>
                        
                        <div class="contact-item">
                            <i class="fas fa-clock"></i>
                            <span>
                                <strong>Horário de atendimento:</strong><br>
                                Segunda a Sexta, 8h às 18h
                            </span>
                        </div>
                    </div>
                    
                    <!-- Selos de parceria -->
                    <div class="partner-badges mt-3">
                        <img src="/penomato_mvp/assets/imagens/logo-uems.png" 
                             alt="UEMS" 
                             class="partner-logo"
                             onerror="this.style.display='none'">
                        <img src="/penomato_mvp/assets/imagens/logo-cnpq.png" 
                             alt="CNPq" 
                             class="partner-logo"
                             onerror="this.style.display='none'">
                        <img src="/penomato_mvp/assets/imagens/logo-capes.png" 
                             alt="CAPES" 
                             class="partner-logo"
                             onerror="this.style.display='none'">
                    </div>
                </div>
            </div>
            
            <!-- Linha divisória -->
            <hr class="footer-divider">
            
            <!-- Rodapé inferior -->
            <div class="row footer-bottom">
                <div class="col-12 text-center mb-2">
                    <p class="mb-0 footer-credito-institucional">
                        Desenvolvido como Projeto Integrador do Curso de Tecnologia da Informação da UFMS,
                        em parceria com o Grupo de Estudos em Botânica e Recursos Florestais da UEMS,
                        com orientação e apoio do Prof. Dr. Norton Hayd Rêgo.
                    </p>
                </div>
                <div class="col-md-6 text-center text-md-start">
                    <p class="mb-0">
                        &copy; <?php echo $ano_atual; ?> Penomato. Todos os direitos reservados.
                    </p>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <p class="mb-0">
                        <a href="/penomato_mvp/src/Views/publico/privacidade.php">Política de Privacidade</a>
                        <span class="mx-2">|</span>
                        <a href="/penomato_mvp/src/Views/publico/termos.php">Termos de Uso</a>
                        <span class="mx-2">|</span>
                        <span>v<?php echo $versao_sistema; ?></span>
                    </p>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- ================================================== -->
    <!-- SCRIPTS GLOBAIS -->
    <!-- ================================================== -->
    
    <!-- jQuery (necessário para alguns plugins) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha384-vtXRMe3mGCbOeY7l30aIg8H9p3GdeSe4IFlP6G8JMa7o7lXvnz3GFKzPxzJdPfGK" crossorigin="anonymous"></script>
    
    <!-- Bootstrap JS (já incluído no cabeçalho, mas garantindo) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
    
    <!-- Scripts customizados do Penomato -->
    <script src="/penomato_mvp/assets/js/validacoes.js"></script>
    <script src="/penomato_mvp/assets/js/busca_ajax.js"></script>
    <script src="/penomato_mvp/assets/js/upload_preview.js"></script>
    
    <!-- Script para botão de voltar ao topo -->
    <script>
        // ==================================================
        // BOTÃO VOLTAR AO TOPO
        // ==================================================
        
        // Criar botão
        const backToTopButton = document.createElement('button');
        backToTopButton.innerHTML = '<i class="fas fa-arrow-up"></i>';
        backToTopButton.setAttribute('id', 'backToTop');
        backToTopButton.setAttribute('title', 'Voltar ao topo');
        backToTopButton.setAttribute('aria-label', 'Voltar ao topo da página');
        document.body.appendChild(backToTopButton);
        
        // Estilizar botão (inline para garantir)
        const style = document.createElement('style');
        style.textContent = `
            #backToTop {
                position: fixed;
                bottom: 30px;
                right: 30px;
                width: 50px;
                height: 50px;
                border-radius: 50%;
                background: var(--cor-primaria);
                color: white;
                border: none;
                box-shadow: 0 4px 12px rgba(0,0,0,0.2);
                cursor: pointer;
                display: none;
                align-items: center;
                justify-content: center;
                font-size: 1.2rem;
                transition: all 0.3s;
                z-index: 999;
                border: 2px solid white;
            }
            
            #backToTop:hover {
                background: #0a4e36;
                transform: translateY(-5px);
                box-shadow: 0 6px 16px rgba(0,0,0,0.3);
            }
            
            #backToTop.show {
                display: flex;
                animation: fadeIn 0.3s;
            }
            
            @keyframes fadeIn {
                from { opacity: 0; transform: translateY(20px); }
                to { opacity: 1; transform: translateY(0); }
            }
        `;
        document.head.appendChild(style);
        
        // Mostrar/esconder baseado no scroll
        window.addEventListener('scroll', function() {
            if (window.scrollY > 300) {
                backToTopButton.classList.add('show');
            } else {
                backToTopButton.classList.remove('show');
            }
        });
        
        // Ação de clique
        backToTopButton.addEventListener('click', function() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    </script>
    
    <!-- Script para ano automático no copyright -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Atualizar ano automático (caso queira fazer via JS)
            const yearElements = document.querySelectorAll('.current-year');
            yearElements.forEach(el => {
                el.textContent = new Date().getFullYear();
            });
        });
    </script>
    
    <!-- Script para tooltips do Bootstrap -->
    <script>
        // Inicializar tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
    </script>
    
    <!-- Script para popovers -->
    <script>
        // Inicializar popovers
        var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'))
        var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
            return new bootstrap.Popover(popoverTriggerEl)
        });
    </script>
    
    <!-- Script para lazy loading de imagens -->
    <script>
        if ('loading' in HTMLImageElement.prototype) {
            // Navegador suporta lazy loading nativo
            const images = document.querySelectorAll('img[loading="lazy"]');
            images.forEach(img => {
                img.loading = 'lazy';
            });
        } else {
            // Fallback para navegadores antigos
            // (poderia incluir um script de lazy loading aqui)
        }
    </script>
    
    <!-- Script para analytics (opcional) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-XXXXXXXXXX"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', 'G-XXXXXXXXXX');
    </script>

<!-- ================================================== -->
<!-- FIM DO RODAPÉ -->
<!-- ================================================== -->

</body>
</html>

<?php
// ============================================================
// FUNÇÕES AUXILIARES PARA O RODAPÉ
// ============================================================

/**
 * Esta função pode ser usada para estatísticas rápidas
 * que aparecem no rodapé (opcional)
 */
function getEstatisticasRodape() {
    // Aqui você pode buscar do banco:
    // - Total de espécies
    // - Total de usuários
    // - Total de imagens
    // - Última atualização
    
    return [
        'especies' => 1247,
        'usuarios' => 89,
        'imagens' => 3502,
        'ultima_atualizacao' => '16/02/2026'
    ];
}

// Exemplo de uso (descomentar se quiser exibir)
// $estatisticas = getEstatisticasRodape();
?>

<style>
    /* ================================================== */
    /* ESTILOS EXCLUSIVOS DO RODAPÉ */
    /* ================================================== */
    
    .footer-penomato {
        background: linear-gradient(135deg, #1a2a2a 0%, #0b2a2a 100%);
        color: #e0e0e0;
        padding: 50px 0 20px;
        font-size: 0.95rem;
        border-top: 4px solid var(--cor-primaria);
        margin-top: auto;
    }
    
    .footer-logo {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 20px;
    }
    
    .footer-logo i {
        font-size: 2.5rem;
        color: var(--cor-primaria);
        background: rgba(255,255,255,0.1);
        width: 50px;
        height: 50px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .footer-logo span {
        font-size: 1.5rem;
        font-weight: 700;
        color: white;
    }
    
    .footer-description {
        line-height: 1.6;
        opacity: 0.8;
        margin-bottom: 20px;
    }
    
    .social-links {
        display: flex;
        gap: 15px;
    }
    
    .social-link {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: rgba(255,255,255,0.1);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        transition: all 0.3s;
        text-decoration: none;
    }
    
    .social-link:hover {
        background: var(--cor-primaria);
        transform: translateY(-3px);
        color: white;
    }
    
    .footer-title {
        color: white;
        font-weight: 600;
        margin-bottom: 20px;
        position: relative;
        padding-bottom: 10px;
    }
    
    .footer-title::after {
        content: '';
        position: absolute;
        left: 0;
        bottom: 0;
        width: 40px;
        height: 3px;
        background: var(--cor-primaria);
    }
    
    .footer-links {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    
    .footer-links li {
        margin-bottom: 12px;
    }
    
    .footer-links a {
        color: #e0e0e0;
        text-decoration: none;
        transition: all 0.3s;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .footer-links a i {
        font-size: 0.7rem;
        color: var(--cor-primaria);
        transition: transform 0.3s;
    }
    
    .footer-links a:hover {
        color: white;
        padding-left: 5px;
    }
    
    .footer-links a:hover i {
        transform: translateX(3px);
    }
    
    .contact-info {
        display: flex;
        flex-direction: column;
        gap: 15px;
    }
    
    .contact-item {
        display: flex;
        gap: 12px;
        align-items: flex-start;
    }
    
    .contact-item i {
        color: var(--cor-primaria);
        font-size: 1.2rem;
        min-width: 25px;
        margin-top: 3px;
    }
    
    .contact-item a {
        color: #e0e0e0;
        text-decoration: none;
    }
    
    .contact-item a:hover {
        color: white;
        text-decoration: underline;
    }
    
    .partner-badges {
        display: flex;
        gap: 15px;
        flex-wrap: wrap;
        margin-top: 20px;
    }
    
    .partner-logo {
        height: 40px;
        width: auto;
        background: white;
        padding: 5px 10px;
        border-radius: 5px;
    }
    
    .footer-divider {
        border-color: rgba(255,255,255,0.1);
        margin: 30px 0;
    }
    
    .footer-bottom {
        font-size: 0.85rem;
        opacity: 0.7;
    }
    
    .footer-bottom a {
        color: #e0e0e0;
        text-decoration: none;
    }
    
    .footer-bottom a:hover {
        color: white;
        text-decoration: underline;
    }

    .footer-credito-institucional {
        font-size: 0.8rem;
        opacity: 0.6;
        font-style: italic;
        border-bottom: 1px solid rgba(255,255,255,0.1);
        padding-bottom: 10px;
        margin-bottom: 10px;
    }

    /* Responsividade */
    @media (max-width: 768px) {
        .footer-penomato {
            text-align: center;
        }
        
        .footer-title::after {
            left: 50%;
            transform: translateX(-50%);
        }
        
        .footer-links a {
            justify-content: center;
        }
        
        .contact-item {
            justify-content: center;
        }
        
        .social-links {
            justify-content: center;
        }
        
        .partner-badges {
            justify-content: center;
        }
    }
</style>