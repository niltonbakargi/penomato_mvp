<?php
// ============================================================
// HELPER: montarAutoresArtigo
// Monta a lista de autores/contribuidores de um artigo
// conforme a hierarquia de responsabilidade científica:
//   1. Revisor científico (especialista)
//   2. Coletores de campo (fotógrafos), em ordem cronológica
//   3. Validador morfológico (confirmador dos dados)
//   4. Compilador de dados (insertor dos dados da internet)
//   5. Editor (gestor que publicou)
//
// Se a mesma pessoa aparece em múltiplos papéis, ela é listada
// uma única vez na posição de maior hierarquia, acumulando papéis.
// ============================================================

function montarAutoresArtigo(PDO $pdo, int $especie_id): array
{
    // ── Coleta bruta por papel ────────────────────────────────────────────────

    // 1. Revisor científico
    $revisor = $pdo->prepare("
        SELECT u.id, u.nome
        FROM artigos a
        JOIN usuarios u ON u.id = a.revisado_por
        WHERE a.especie_id = ? AND a.revisado_por IS NOT NULL
        LIMIT 1
    ");
    $revisor->execute([$especie_id]);
    $revisor = $revisor->fetch(PDO::FETCH_ASSOC);

    // 2. Coletores de campo — agrupados por coletor, ordenados pelo 1º upload
    $coletores_stmt = $pdo->prepare("
        SELECT
            u.id,
            u.nome,
            GROUP_CONCAT(DISTINCT ei.parte_planta ORDER BY ei.parte_planta SEPARATOR ', ') AS partes,
            MIN(ei.data_upload) AS primeiro_upload
        FROM especies_imagens ei
        JOIN usuarios u ON u.id = ei.coletor_id
        WHERE ei.especie_id = ? AND ei.coletor_id IS NOT NULL
          AND ei.origem = 'campo'
        GROUP BY u.id, u.nome
        ORDER BY primeiro_upload ASC
    ");
    $coletores_stmt->execute([$especie_id]);
    $coletores = $coletores_stmt->fetchAll(PDO::FETCH_ASSOC);

    // 3. Validador morfológico (confirmador)
    $confirmador = $pdo->prepare("
        SELECT u.id, u.nome
        FROM especies_administrativo ea
        JOIN usuarios u ON u.id = ea.autor_descrita_id
        WHERE ea.id = ? AND ea.autor_descrita_id IS NOT NULL
        LIMIT 1
    ");
    $confirmador->execute([$especie_id]);
    $confirmador = $confirmador->fetch(PDO::FETCH_ASSOC);

    // 4. Compilador de dados da internet
    $compilador = $pdo->prepare("
        SELECT u.id, u.nome
        FROM especies_administrativo ea
        JOIN usuarios u ON u.id = ea.autor_dados_internet_id
        WHERE ea.id = ? AND ea.autor_dados_internet_id IS NOT NULL
        LIMIT 1
    ");
    $compilador->execute([$especie_id]);
    $compilador = $compilador->fetch(PDO::FETCH_ASSOC);

    // 5. Editor (gestor que publicou)
    $editor = $pdo->prepare("
        SELECT u.id, u.nome
        FROM especies_administrativo ea
        JOIN usuarios u ON u.id = ea.autor_publicado_id
        WHERE ea.id = ? AND ea.autor_publicado_id IS NOT NULL
        LIMIT 1
    ");
    $editor->execute([$especie_id]);
    $editor = $editor->fetch(PDO::FETCH_ASSOC);

    // ── Montar lista com deduplicação ─────────────────────────────────────────
    // Estrutura: [usuario_id => ['nome' => ..., 'papeis' => [...], 'ordem' => N]]
    $mapa = [];

    $registrar = function(array $usuario, string $papel, int $ordem, string $detalhe = '') use (&$mapa) {
        $id = $usuario['id'];
        if (!isset($mapa[$id])) {
            $mapa[$id] = [
                'nome'  => $usuario['nome'],
                'papeis' => [],
                'ordem' => $ordem,
            ];
        } else {
            // Mantém a posição de maior hierarquia (menor número)
            if ($ordem < $mapa[$id]['ordem']) {
                $mapa[$id]['ordem'] = $ordem;
            }
        }
        $label = $detalhe ? "{$papel} ({$detalhe})" : $papel;
        $mapa[$id]['papeis'][] = $label;
    };

    if ($revisor) {
        $registrar($revisor, 'Revisão científica', 1);
    }

    foreach ($coletores as $index => $coletor) {
        $registrar(
            $coletor,
            'Coleta de campo',
            2 + $index,          // preserva ordem cronológica entre coletores
            $coletor['partes']
        );
    }

    if ($confirmador) {
        $registrar($confirmador, 'Validação morfológica', 100);
    }

    if ($compilador) {
        $registrar($compilador, 'Compilação de dados', 101);
    }

    if ($editor) {
        $registrar($editor, 'Gestão editorial', 102);
    }

    // ── Ordenar por hierarquia ────────────────────────────────────────────────
    uasort($mapa, fn($a, $b) => $a['ordem'] <=> $b['ordem']);

    return array_values($mapa);
}
