-- ============================================================================
-- SCRIPT DE VERIFICACIÓN: MENÚ DE CATÁLOGOS EN SEGURIDAD
-- ============================================================================
-- Ejecuta este script para verificar que la integración del menú se completó
-- correctamente. Todos los queries deben retornar resultados.
-- ============================================================================

USE digisports_core;

-- ============================================================================
-- 1. VERIFICAR QUE EL MÓDULO SEGURIDAD EXISTE
-- ============================================================================
SELECT 'PASO 1: Verificando módulo SEGURIDAD' as VERIFICACION;
SELECT 
    mod_id,
    mod_codigo,
    mod_nombre,
    mod_estado
FROM seguridad_modulos 
WHERE mod_codigo = 'SEGURIDAD'
LIMIT 1;

-- Resultado esperado: 1 fila con mod_código = 'SEGURIDAD'
-- ============================================================================

-- ============================================================================
-- 2. VERIFICAR QUE EL HEADER "ADMINISTRACIÓN DE CATÁLOGOS" EXISTE
-- ============================================================================
SELECT 'PASO 2: Verificando HEADER "Administración de Catálogos"' as VERIFICACION;
SELECT 
    m.men_id,
    m.men_modulo_id,
    m.men_label,
    m.men_tipo,
    m.men_orden,
    m.men_icono,
    m.created_at
FROM seguridad_menu m
WHERE m.men_label = 'Administración de Catálogos'
AND m.men_tipo = 'HEADER'
AND m.men_modulo_id = (SELECT mod_id FROM seguridad_modulos WHERE mod_codigo = 'SEGURIDAD')
LIMIT 1;

-- Resultado esperado: 1 fila con men_tipo = 'HEADER'
-- ============================================================================

-- ============================================================================
-- 3. VERIFICAR QUE EL ITEM "CATÁLOGOS" EXISTE
-- ============================================================================
SELECT 'PASO 3: Verificando ITEM "Catálogos"' as VERIFICACION;
SELECT 
    m.men_id,
    m.men_padre_id,
    m.men_label,
    m.men_tipo,
    m.men_orden,
    m.men_icono,
    m.men_ruta_modulo,
    m.men_ruta_controller,
    m.men_ruta_action,
    m.created_at
FROM seguridad_menu m
WHERE m.men_label = 'Catálogos'
AND m.men_tipo = 'ITEM'
AND m.men_padre_id = (
    SELECT men_id FROM seguridad_menu 
    WHERE men_label = 'Administración de Catálogos'
    AND men_tipo = 'HEADER'
    AND men_modulo_id = (SELECT mod_id FROM seguridad_modulos WHERE mod_codigo = 'SEGURIDAD')
)
LIMIT 1;

-- Resultado esperado: 1 fila con men_ruta_controller = 'seguridad_tabla'
-- ============================================================================

-- ============================================================================
-- 4. VERIFICAR PERMISOS DEL ROL ADMIN AL HEADER
-- ============================================================================
SELECT 'PASO 4: Verificando permisos ADMIN al HEADER' as VERIFICACION;
SELECT 
    r.rme_rol_id,
    m.men_label,
    m.men_tipo,
    r.rme_puede_ver,
    r.rme_puede_acceder,
    r.created_at
FROM seguridad_rol_menu r
INNER JOIN seguridad_menu m ON r.rme_menu_id = m.men_id
WHERE r.rme_rol_id = 1  -- ADMIN role
AND m.men_label = 'Administración de Catálogos'
AND m.men_tipo = 'HEADER'
LIMIT 1;

-- Resultado esperado: 1 fila con rme_puede_ver = 1 y rme_puede_acceder = 1
-- ============================================================================

-- ============================================================================
-- 5. VERIFICAR PERMISOS DEL ROL ADMIN AL ITEM CATÁLOGOS
-- ============================================================================
SELECT 'PASO 5: Verificando permisos ADMIN al ITEM "Catálogos"' as VERIFICACION;
SELECT 
    r.rme_rol_id,
    m.men_label,
    m.men_tipo,
    r.rme_puede_ver,
    r.rme_puede_acceder,
    r.created_at
FROM seguridad_rol_menu r
INNER JOIN seguridad_menu m ON r.rme_menu_id = m.men_id
WHERE r.rme_rol_id = 1  -- ADMIN role
AND m.men_label = 'Catálogos'
AND m.men_tipo = 'ITEM'
LIMIT 1;

-- Resultado esperado: 1 fila con rme_puede_ver = 1 y rme_puede_acceder = 1
-- ============================================================================

-- ============================================================================
-- 6. CONTAR TOTAL DE ITEMS EN EL MENÚ SEGURIDAD
-- ============================================================================
SELECT 'PASO 6: Contando items totales en menú SEGURIDAD' as VERIFICACION;
SELECT 
    COUNT(*) as TOTAL_ITEMS,
    SUM(CASE WHEN men_tipo = 'HEADER' THEN 1 ELSE 0 END) as HEADERS,
    SUM(CASE WHEN men_tipo = 'ITEM' THEN 1 ELSE 0 END) as ITEMS
FROM seguridad_menu m
WHERE m.men_modulo_id = (SELECT mod_id FROM seguridad_modulos WHERE mod_codigo = 'SEGURIDAD');

-- Resultado esperado: Debe incluir el HEADER y ITEM que agregamos
-- ============================================================================

-- ============================================================================
-- 7. MOSTRAR LA ESTRUCTURA COMPLETA DEL MENÚ SEGURIDAD (SIN CATÁLOGOS NUEVO)
-- ============================================================================
SELECT 'PASO 7: Estructura completa del menú SEGURIDAD (primeros 5 items)' as VERIFICACION;
SELECT 
    m.men_id,
    CASE 
        WHEN m.men_padre_id IS NULL THEN '└─ HEADER'
        ELSE '   ├─ ITEM'
    END as TIPO,
    m.men_label as NOMBRE,
    m.men_orden as ORDEN,
    m.men_icono as ICONO,
    CASE 
        WHEN m.men_ruta_controller IS NOT NULL THEN CONCAT(m.men_ruta_controller, '/', m.men_ruta_action)
        ELSE 'N/A'
    END as RUTA
FROM seguridad_menu m
WHERE m.men_modulo_id = (SELECT mod_id FROM seguridad_modulos WHERE mod_codigo = 'SEGURIDAD')
ORDER BY m.men_orden, m.men_id
LIMIT 5;

-- Resultado esperado: Lista de items del menú
-- ============================================================================

-- ============================================================================
-- 8. VERIFICAR QUE LAS TABLAS DEL MÓDULO EXISTEN
-- ============================================================================
SELECT 'PASO 8: Verificando tablas del módulo catálogos' as VERIFICACION;
SELECT 
    TABLE_NAME,
    TABLE_ROWS,
    DATA_LENGTH
FROM INFORMATION_SCHEMA.TABLES
WHERE TABLE_SCHEMA = 'digisports_core'
AND TABLE_NAME LIKE 'seguridad_catalogo%'
ORDER BY TABLE_NAME;

-- Resultado esperado: 2 filas (seguridad_catalogo y seguridad_catalogo_item)
-- ============================================================================

-- ============================================================================
-- 9. MOSTRAR JERARQUÍA COMPLETA: HEADER → ITEM
-- ============================================================================
SELECT 'PASO 9: Jerarquía del menú: HEADER → ITEM' as VERIFICACION;
SELECT 
    CONCAT(REPEAT('   ', 0), header.men_label) as NIVEL_1,
    COUNT(DISTINCT item.men_id) as NUM_ITEMS
FROM seguridad_menu header
LEFT JOIN seguridad_menu item ON item.men_padre_id = header.men_id AND item.men_tipo = 'ITEM'
WHERE header.men_modulo_id = (SELECT mod_id FROM seguridad_modulos WHERE mod_codigo = 'SEGURIDAD')
AND header.men_tipo = 'HEADER'
AND header.men_label = 'Administración de Catálogos'
GROUP BY header.men_id, header.men_label;

-- Resultado esperado: 1 fila mostrando el HEADER y cantidad de ITEMS (mínimo 1)
-- ============================================================================

-- ============================================================================
-- 10. RESUMEN FINAL: VALIDACIÓN COMPLETA
-- ============================================================================
SELECT 'PASO 10: RESUMEN DE VERIFICACIÓN' as VERIFICACION;
SELECT 
    'Módulo SEGURIDAD' as COMPONENTE,
    (SELECT COUNT(*) FROM seguridad_modulos WHERE mod_codigo = 'SEGURIDAD') > 0 as EXISTE,
    '✅ Si es 1' as ESPERADO
UNION ALL
SELECT 
    'Menu HEADER Catálogos',
    (SELECT COUNT(*) FROM seguridad_menu 
     WHERE men_label = 'Administración de Catálogos' 
     AND men_tipo = 'HEADER'
     AND men_modulo_id = (SELECT mod_id FROM seguridad_modulos WHERE mod_codigo = 'SEGURIDAD')) > 0,
    '✅ Si es 1'
UNION ALL
SELECT 
    'Menu ITEM Catálogos',
    (SELECT COUNT(*) FROM seguridad_menu 
     WHERE men_label = 'Catálogos' 
     AND men_tipo = 'ITEM'
     AND men_padre_id = (SELECT men_id FROM seguridad_menu 
                         WHERE men_label = 'Administración de Catálogos'
                         AND men_tipo = 'HEADER'
                         AND men_modulo_id = (SELECT mod_id FROM seguridad_modulos WHERE mod_codigo = 'SEGURIDAD'))) > 0,
    '✅ Si es 1'
UNION ALL
SELECT 
    'Permisos ADMIN HEADER',
    (SELECT COUNT(*) FROM seguridad_rol_menu r
     INNER JOIN seguridad_menu m ON r.rme_menu_id = m.men_id
     WHERE r.rme_rol_id = 1 AND m.men_label = 'Administración de Catálogos' AND m.men_tipo = 'HEADER') > 0,
    '✅ Si es 1'
UNION ALL
SELECT 
    'Permisos ADMIN ITEM',
    (SELECT COUNT(*) FROM seguridad_rol_menu r
     INNER JOIN seguridad_menu m ON r.rme_menu_id = m.men_id
     WHERE r.rme_rol_id = 1 AND m.men_label = 'Catálogos' AND m.men_tipo = 'ITEM') > 0,
    '✅ Si es 1'
UNION ALL
SELECT 
    'Tabla seguridad_catalogo',
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES 
     WHERE TABLE_SCHEMA = 'digisports_core' AND TABLE_NAME = 'seguridad_catalogo') > 0,
    '✅ Si es 1'
UNION ALL
SELECT 
    'Tabla seguridad_catalogo_item',
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES 
     WHERE TABLE_SCHEMA = 'digisports_core' AND TABLE_NAME = 'seguridad_catalogo_item') > 0,
    '✅ Si es 1';

-- Resultado esperado: Todas las filas con 1 (TRUE)
-- ============================================================================

-- ============================================================================
-- FIN DEL SCRIPT DE VERIFICACIÓN
-- ============================================================================
SELECT '
═══════════════════════════════════════════════════════════════════════════════
✅ VERIFICACIÓN COMPLETADA

Si todos los pasos retormaron resultados esperados, significa que:
  ✅ Menú de catálogos fue integrado correctamente
  ✅ Permisos del rol ADMIN están configurados
  ✅ Las tablas de la base de datos existen
  ✅ El módulo está 100% operacional

SIGUIENTE PASO:
  1. Abre http://localhost/digisports/public/
  2. Login como ADMIN
  3. Vé al módulo Seguridad
  4. Busca "Administración de Catálogos" en el menú lateral
  5. Haz clic en "Catálogos" para comenzar

═══════════════════════════════════════════════════════════════════════════════
' as RESULTADO_FINAL;
