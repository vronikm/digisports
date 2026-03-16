-- ============================================================
-- Migration 006: Menú — Configuración de Facturación Electrónica
-- Agrega ítem de menú para ConfiguracionController
-- Ejecutar en: digisports_core
-- ============================================================

-- 1. Insertar ítem de menú bajo el header "Facturación" (men_padre_id = 247)
INSERT INTO `seguridad_menu`
    (`men_modulo_id`, `men_padre_id`, `men_tipo`, `men_label`, `men_icono`,
     `men_ruta_modulo`, `men_ruta_controller`, `men_ruta_action`, `men_orden`, `men_activo`)
VALUES
    (3, 247, 'ITEM', 'Configuración FE', 'fas fa-sliders-h',
     'facturacion', 'configuracion', 'index', 8, 1);

-- 2. Asignar el nuevo ítem a todos los roles que ya tienen acceso al módulo de facturación
-- Se restringe a roles admin/superadmin (nivel 4+) ya que es configuración crítica
INSERT INTO `seguridad_rol_menu` (`rme_rol_id`, `rme_menu_id`, `rme_puede_ver`, `rme_puede_acceder`)
SELECT r.rol_rol_id, LAST_INSERT_ID(), 1, 1
FROM `seguridad_roles` r
WHERE r.rol_estado = 'A'
  AND (r.rol_es_super_admin = 'S' OR r.rol_es_admin_tenant = 'S' OR r.rol_nivel_acceso >= 4)
ON DUPLICATE KEY UPDATE rme_puede_ver = 1, rme_puede_acceder = 1;
