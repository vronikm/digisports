-- ============================================================
-- DigiSports Fútbol — Script de Inicialización por Tenant
-- Ejecutar una vez por cada nuevo tenant que active el módulo FUTBOL
-- ============================================================
-- Uso: reemplazar @NEW_TENANT_ID con el ID real del tenant
-- Ejemplo: SET @NEW_TENANT_ID = 2;
-- ============================================================

SET @NEW_TENANT_ID = 1; -- CAMBIAR AQUÍ

-- ── 1. Asignar módulo FUTBOL al tenant ─────────────────────
INSERT IGNORE INTO seguridad_tenant_modulos (tmo_tenant_id, tmo_modulo_id, tmo_estado, tmo_activo)
SELECT @NEW_TENANT_ID, mod_id, 'ACTIVO', 'S'
FROM seguridad_modulos WHERE mod_codigo = 'FUTBOL';

-- ── 2. Categorías por defecto (basadas en edades FIFA Ecuador) ─
INSERT IGNORE INTO futbol_categorias
    (fct_tenant_id, fct_nombre, fct_descripcion, fct_edad_min, fct_edad_max, fct_color, fct_orden, fct_activo)
VALUES
    (@NEW_TENANT_ID, 'Sub-6 (Baby Fútbol)', 'Iniciación deportiva temprana',           4,  6,  '#94A3B8', 1,  1),
    (@NEW_TENANT_ID, 'Sub-8',               'Desarrollo motriz y coordinación',          7,  8,  '#22C55E', 2,  1),
    (@NEW_TENANT_ID, 'Sub-10',              'Fundamentos técnicos básicos',              9,  10, '#3B82F6', 3,  1),
    (@NEW_TENANT_ID, 'Sub-12',              'Técnica individual y juego colectivo',      11, 12, '#8B5CF6', 4,  1),
    (@NEW_TENANT_ID, 'Sub-14',              'Táctica y posicionamiento',                 13, 14, '#F59E0B', 5,  1),
    (@NEW_TENANT_ID, 'Sub-16',              'Fútbol competitivo formativo',              15, 16, '#EF4444', 6,  1),
    (@NEW_TENANT_ID, 'Sub-18',              'Alto rendimiento juvenil',                  17, 18, '#EC4899', 7,  1),
    (@NEW_TENANT_ID, 'Adultos',             'Fútbol recreativo y competitivo adulto',    18, 99, '#06B6D4', 8,  1);

-- ── 3. Configuración inicial del módulo ────────────────────
INSERT IGNORE INTO futbol_configuracion
    (config_tenant_id, config_clave, config_valor, config_descripcion, config_tipo, config_opciones)
VALUES
    -- INSCRIPCIONES
    (@NEW_TENANT_ID, 'INSCRIPCIONES_VALOR_MATRICULA',   '50.00',    'Valor de matrícula por defecto (USD)',        'NUMBER',  NULL),
    (@NEW_TENANT_ID, 'INSCRIPCIONES_VALOR_MENSUALIDAD', '30.00',    'Valor de mensualidad por defecto (USD)',      'NUMBER',  NULL),
    (@NEW_TENANT_ID, 'INSCRIPCIONES_DIA_VENCIMIENTO',   '5',        'Día del mes en que vence la mensualidad',    'NUMBER',  NULL),
    (@NEW_TENANT_ID, 'INSCRIPCIONES_PERMITIR_LISTA',    '1',        'Permitir lista de espera cuando cupo lleno', 'BOOLEAN', NULL),
    -- PAGOS
    (@NEW_TENANT_ID, 'PAGOS_DIAS_GRACIA',               '3',        'Días de gracia antes de aplicar mora',       'NUMBER',  NULL),
    (@NEW_TENANT_ID, 'PAGOS_PORCENTAJE_MORA',           '2.00',     'Porcentaje de mora mensual (%)',              'NUMBER',  NULL),
    (@NEW_TENANT_ID, 'PAGOS_MONEDA',                    'USD',       'Moneda del sistema',                         'TEXT',    NULL),
    (@NEW_TENANT_ID, 'PAGOS_METODOS',                   'EFECTIVO,TRANSFERENCIA,TARJETA', 'Métodos de pago habilitados', 'TEXT', NULL),
    -- ASISTENCIA
    (@NEW_TENANT_ID, 'ASISTENCIA_HORA_CORTE',           '15',       'Minutos de tolerancia para marcar asistencia','NUMBER', NULL),
    (@NEW_TENANT_ID, 'ASISTENCIA_MIN_PORCENTAJE',       '80',       'Porcentaje mínimo de asistencia requerido',  'NUMBER',  NULL),
    -- GENERAL
    (@NEW_TENANT_ID, 'GENERAL_NOMBRE_ACADEMIA',         'Academia de Fútbol', 'Nombre de la academia',             'TEXT',    NULL),
    (@NEW_TENANT_ID, 'GENERAL_EDAD_MINIMA',             '4',        'Edad mínima de inscripción',                 'NUMBER',  NULL),
    (@NEW_TENANT_ID, 'GENERAL_FOTO_OBLIGATORIA',        '0',        'Requerir foto del alumno',                   'BOOLEAN', NULL),
    (@NEW_TENANT_ID, 'GENERAL_CEDULA_OBLIGATORIA',      '0',        'Requerir cédula del alumno (no para niños)', 'BOOLEAN', NULL),
    -- NOTIFICACIONES
    (@NEW_TENANT_ID, 'NOTIF_RECORDATORIO_PAGO',         '1',        'Enviar recordatorio de pago',                'BOOLEAN', NULL),
    (@NEW_TENANT_ID, 'NOTIF_DIAS_ANTES_VENCIMIENTO',    '3',        'Días antes del vencimiento para notificar',  'NUMBER',  NULL);

-- ── 4. Período inicial (mes en curso) ──────────────────────
INSERT IGNORE INTO futbol_periodos
    (fpe_tenant_id, fpe_nombre, fpe_fecha_inicio, fpe_fecha_fin, fpe_activo)
SELECT
    @NEW_TENANT_ID,
    CONCAT('Período ', YEAR(CURDATE()), '-', LPAD(MONTH(CURDATE()), 2, '0')),
    DATE_FORMAT(CURDATE(), '%Y-%m-01'),
    LAST_DAY(CURDATE()),
    1
WHERE NOT EXISTS (
    SELECT 1 FROM futbol_periodos
    WHERE fpe_tenant_id = @NEW_TENANT_ID AND fpe_activo = 1
);

-- ── 5. Campos de ficha deportiva por defecto ───────────────
INSERT IGNORE INTO futbol_campos_ficha
    (fcf_tenant_id, fcf_nombre, fcf_tipo, fcf_obligatorio, fcf_orden, fcf_activo)
VALUES
    (@NEW_TENANT_ID, 'Posición preferida',      'SELECT',  0, 1, 1),
    (@NEW_TENANT_ID, 'Pie dominante',           'SELECT',  0, 2, 1),
    (@NEW_TENANT_ID, 'Talla de camiseta',       'SELECT',  0, 3, 1),
    (@NEW_TENANT_ID, 'Número de camiseta',      'NUMBER',  0, 4, 1),
    (@NEW_TENANT_ID, 'Club anterior',           'TEXT',    0, 5, 1),
    (@NEW_TENANT_ID, 'Años de experiencia',     'NUMBER',  0, 6, 1),
    (@NEW_TENANT_ID, 'Objetivo (recreativo/formativo/competitivo)', 'SELECT', 0, 7, 1),
    (@NEW_TENANT_ID, 'Autorización médica',     'BOOLEAN', 0, 8, 1);

-- ── Fin del script ──────────────────────────────────────────
SELECT CONCAT('✓ Módulo FUTBOL inicializado para tenant #', @NEW_TENANT_ID) AS resultado;
