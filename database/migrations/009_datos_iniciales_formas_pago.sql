-- ============================================================
-- Migration 009: Datos Iniciales - Formas de Pago por Tenant
-- Inserta las 8 formas de pago SRI (Tabla 24) para cada tenant
-- que no las tenga aun. Idempotente: seguro de ejecutar N veces.
-- Ejecutar en: digisports_core
-- ============================================================

-- Por cada tenant, insertar las formas de pago que falten
INSERT INTO facturacion_formas_pago
    (fpa_tenant_id, fpa_nombre, fpa_codigo_sri, fpa_estado)
SELECT
    t.ten_tenant_id,
    c.nombre,
    c.codigo,
    'ACTIVO'
FROM seguridad_tenants t
CROSS JOIN (
    SELECT '01' AS codigo, 'SIN UTILIZACION DEL SISTEMA FINANCIERO' AS nombre
    UNION ALL SELECT '15', 'COMPENSACION DE DEUDAS'
    UNION ALL SELECT '16', 'TARJETA DE DEBITO'
    UNION ALL SELECT '17', 'DINERO ELECTRONICO'
    UNION ALL SELECT '18', 'TARJETA PREPAGO'
    UNION ALL SELECT '19', 'TARJETA DE CREDITO'
    UNION ALL SELECT '20', 'OTROS CON UTILIZACION DEL SISTEMA FINANCIERO'
    UNION ALL SELECT '21', 'ENDOSO DE TITULOS'
) c
WHERE t.ten_estado_suscripcion != 'CANCELADA'
  AND NOT EXISTS (
      SELECT 1 FROM facturacion_formas_pago
      WHERE fpa_tenant_id = t.ten_tenant_id
        AND fpa_codigo_sri = c.codigo
  );

-- Verificacion post-insercion
SELECT
    t.ten_nombre_comercial AS tenant,
    COUNT(fp.fpa_id) AS formas_pago_total,
    SUM(CASE WHEN fp.fpa_estado = 'ACTIVO'   THEN 1 ELSE 0 END) AS activas,
    SUM(CASE WHEN fp.fpa_estado = 'INACTIVO' THEN 1 ELSE 0 END) AS inactivas
FROM seguridad_tenants t
LEFT JOIN facturacion_formas_pago fp ON fp.fpa_tenant_id = t.ten_tenant_id
WHERE t.ten_estado_suscripcion != 'CANCELADA'
GROUP BY t.ten_tenant_id, t.ten_nombre_comercial
ORDER BY t.ten_tenant_id;
