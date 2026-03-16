-- ============================================================
-- Migration 004: Verificación de tablas de Facturación Electrónica
-- Ejecutar en: digisports_core
-- Nota: Las tablas facturas_electronicas* ya existen en el schema principal.
--       Este script únicamente verifica/agrega registros iniciales de secuenciales
--       y no destruye datos existentes.
-- ============================================================

-- Registrar secuencial inicial para tenant 1 (si no existe)
INSERT INTO `facturas_electronicas_secuenciales`
    (`sec_tenant_id`, `sec_tipo_comprobante`, `sec_establecimiento`, `sec_punto_emision`, `sec_secuencial_actual`)
VALUES
    (1, '01', '001', '001', 0)   -- Facturas
ON DUPLICATE KEY UPDATE `sec_secuencial_actual` = `sec_secuencial_actual`;

-- ============================================================
-- CHECKLIST POST-MIGRACIÓN
-- ============================================================
-- 1. Copiar el certificado .p12 a:
--      storage/certificados/firma.p12
--
-- 2. Configurar en .env:
--      SRI_AMBIENTE=1              (1=Pruebas, 2=Producción)
--      SRI_FIRMA_CLAVE=contraseña
--      SRI_RUC=tu_ruc_aqui
--      SRI_RAZON_SOCIAL=Razón Social S.A.
--      SRI_NOMBRE_COMERCIAL=Nombre Comercial
--      SRI_DIRECCION_MATRIZ=Dirección completa
--      SRI_DIRECCION_ESTABLECIMIENTO=Dirección establecimiento
--      SRI_CODIGO_ESTABLECIMIENTO=001
--      SRI_PUNTO_EMISION=001
--      SRI_OBLIGADO_CONTABILIDAD=SI
--
-- 3. Verificar que PHP tenga habilitadas las extensiones:
--      - php_openssl    (firma digital)
--      - php_curl       (comunicación SOAP con SRI)
--      - php_soap       (opcional, se usa CURL directo)
--      - php_dom        (generación XML)
--
-- 4. Verificar conectividad con el SRI de pruebas:
--      GET /facturacion/facturaElectronica/verificarConexion
-- ============================================================
