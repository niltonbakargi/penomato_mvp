-- ============================================================
-- Adiciona 'Pinada' ao ENUM forma_folha
-- e corrige espécies de palmas (Arecaceae) com forma incorreta
-- ============================================================

-- 1. Ampliar o ENUM com o novo valor
ALTER TABLE especies_caracteristicas
  MODIFY COLUMN forma_folha ENUM(
    'Lanceolada','Linear','Elíptica','Ovada','Orbicular',
    'Cordiforme','Espatulada','Sagitada','Reniforme','Obovada',
    'Trilobada','Palmada','Pinada','Lobada'
  );

-- 2. Corrigir espécies da família Arecaceae cujo forma_folha
--    estava como 'Palmada' (incorreto — folhas pinadas em palmas)
UPDATE especies_caracteristicas ec
JOIN   especies_administrativo   ea  ON ea.id  = ec.especie_id
JOIN   especies_caracteristicas  ec2 ON ec2.especie_id = ea.id
SET    ec.forma_folha = 'Pinada'
WHERE  ec2.familia = 'Arecaceae'
  AND  ec.forma_folha = 'Palmada';

-- 3. Corrigir Acrocomia aculeata especificamente (caso não esteja
--    com família preenchida ou tenha sido importado errado)
UPDATE especies_caracteristicas ec
JOIN   especies_administrativo   ea ON ea.id = ec.especie_id
SET    ec.forma_folha = 'Pinada'
WHERE  ea.nome_cientifico LIKE '%Acrocomia aculeata%'
  AND  ec.forma_folha = 'Palmada';
