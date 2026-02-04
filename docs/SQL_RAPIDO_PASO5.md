# 📋 SQL RÁPIDO: PASO 5 - COPIA Y PEGA

## 🎯 Instrucciones Rápidas

```
1. Abrir: http://localhost/phpmyadmin
2. Click en BD: digisports_core
3. Click en pestaña: SQL
4. Copiar cada bloque de abajo
5. Pegar en el editor SQL
6. Click [Ejecutar]
```

---

## 1️⃣ CREAR TENANT (Empresa)

```sql
INSERT INTO tenants (nombre, ruc, email, telefono, pais, ciudad, direccion, estado, fecha_registro) 
VALUES 
('DigiSports Demo', '20123456789', 'info@digisports.local', '+51987654321', 'PE', 'Lima', 'Av. Principal 123', 'A', NOW());
```

**Resultado**: Tenant creado con ID = 1

---

## 2️⃣ CREAR USUARIO ADMIN

```sql
-- IMPORTANTE: Este password es simple para prueba, en producción encriptar
INSERT INTO usuarios (tenant_id, nombre, email, password, rol, estado, fecha_registro) 
VALUES 
(1, 'Admin Usuario', 'admin@digisports.local', 'Admin123!', 'admin', 'A', NOW());
```

**Usuario**: admin@digisports.local  
**Contraseña**: Admin123!

---

## 3️⃣ CREAR CLIENTES

```sql
INSERT INTO clientes (tenant_id, nombre, ruc, email, telefono, direccion, estado, fecha_registro) 
VALUES 
(1, 'Acme Corporation', '20123456789', 'contacto@acme.com', '+51987654321', 'Av. Principal 100', 'A', NOW()),
(1, 'Tech Solutions SA', '20987654321', 'ventas@techsol.com', '+51987654322', 'Calle Comercial 200', 'A', NOW()),
(1, 'Digital Agency Perú', '20111222333', 'info@digitalagency.com', '+51987654323', 'Av. Tecnológica 300', 'A', NOW()),
(1, 'Innovatech Industries', '20444555666', 'contacto@innovatech.com', '+51987654324', 'Jr. Industrial 400', 'A', NOW()),
(1, 'Premium Solutions', '20777888999', 'ventas@premiumbiz.com', '+51987654325', 'Av. Premium 500', 'A', NOW());
```

**Resultado**: 5 clientes creados

---

## 4️⃣ CREAR FORMAS DE PAGO

```sql
INSERT INTO formas_pago (tenant_id, nombre, descripcion, estado, fecha_registro) 
VALUES 
(1, 'Efectivo', 'Pago en efectivo', 'A', NOW()),
(1, 'Tarjeta de Crédito', 'Pago con tarjeta de crédito', 'A', NOW()),
(1, 'Transferencia Bancaria', 'Pago por transferencia bancaria', 'A', NOW()),
(1, 'Cheque', 'Pago con cheque', 'A', NOW()),
(1, 'Depósito en Cuenta', 'Depósito directo en cuenta bancaria', 'A', NOW());
```

**Resultado**: 5 formas de pago creadas

---

## 5️⃣ CREAR FACTURAS (PASO IMPORTANTE)

```sql
INSERT INTO facturas (tenant_id, cliente_id, numero_factura, fecha_emision, subtotal, impuesto, total, moneda, estado, fecha_registro) 
VALUES 
-- Enero 2024
(1, 1, 'F-001-2024-01', '2024-01-05', 900.00, 162.00, 1062.00, 'PEN', 'EMITIDA', NOW()),
(1, 2, 'F-002-2024-01', '2024-01-08', 2200.00, 396.00, 2596.00, 'PEN', 'PAGADA', NOW()),
(1, 3, 'F-003-2024-01', '2024-01-10', 700.00, 126.00, 826.00, 'PEN', 'EMITIDA', NOW()),
(1, 4, 'F-004-2024-01', '2024-01-12', 1500.00, 270.00, 1770.00, 'PEN', 'PAGADA', NOW()),
(1, 5, 'F-005-2024-01', '2024-01-15', 3000.00, 540.00, 3540.00, 'PEN', 'EMITIDA', NOW()),
(1, 1, 'F-006-2024-01', '2024-01-18', 1200.00, 216.00, 1416.00, 'PEN', 'PAGADA', NOW()),
(1, 2, 'F-007-2024-01', '2024-01-20', 2800.00, 504.00, 3304.00, 'PEN', 'EMITIDA', NOW()),
(1, 3, 'F-008-2024-01', '2024-01-22', 950.00, 171.00, 1121.00, 'PEN', 'PAGADA', NOW()),
(1, 4, 'F-009-2024-01', '2024-01-25', 4000.00, 720.00, 4720.00, 'PEN', 'EMITIDA', NOW()),
(1, 5, 'F-010-2024-01', '2024-01-28', 1800.00, 324.00, 2124.00, 'PEN', 'PAGADA', NOW()),
-- Febrero 2024
(1, 1, 'F-011-2024-02', '2024-02-01', 1100.00, 198.00, 1298.00, 'PEN', 'EMITIDA', NOW()),
(1, 2, 'F-012-2024-02', '2024-02-03', 2500.00, 450.00, 2950.00, 'PEN', 'PAGADA', NOW()),
(1, 3, 'F-013-2024-02', '2024-02-05', 800.00, 144.00, 944.00, 'PEN', 'EMITIDA', NOW()),
(1, 4, 'F-014-2024-02', '2024-02-08', 1600.00, 288.00, 1888.00, 'PEN', 'PAGADA', NOW()),
(1, 5, 'F-015-2024-02', '2024-02-10', 3200.00, 576.00, 3776.00, 'PEN', 'EMITIDA', NOW());
```

**Resultado**: 15 facturas creadas (diferentes estados)

---

## 6️⃣ CREAR PAGOS

```sql
INSERT INTO pagos (tenant_id, factura_id, forma_pago_id, monto, fecha_pago, numero_referencia, estado, fecha_registro) 
VALUES 
(1, 2, 1, 2596.00, '2024-01-10', 'EF001', 'A', NOW()),
(1, 4, 2, 1770.00, '2024-01-15', 'TC001', 'A', NOW()),
(1, 6, 3, 1416.00, '2024-01-22', 'TRF001', 'A', NOW()),
(1, 8, 1, 1121.00, '2024-01-26', 'EF002', 'A', NOW()),
(1, 10, 2, 2124.00, '2024-02-02', 'TC002', 'A', NOW()),
(1, 12, 3, 2950.00, '2024-02-08', 'TRF002', 'A', NOW()),
(1, 14, 1, 1888.00, '2024-02-12', 'EF003', 'A', NOW());
```

**Resultado**: 7 pagos registrados

---

## ✅ VERIFICAR DATOS

```sql
-- Ver resumen de datos creados
SELECT 'TENANTS' AS tipo, COUNT(*) AS cantidad FROM tenants
UNION ALL
SELECT 'USUARIOS', COUNT(*) FROM usuarios
UNION ALL
SELECT 'CLIENTES', COUNT(*) FROM clientes
UNION ALL
SELECT 'FORMAS_PAGO', COUNT(*) FROM formas_pago
UNION ALL
SELECT 'FACTURAS', COUNT(*) FROM facturas
UNION ALL
SELECT 'PAGOS', COUNT(*) FROM pagos;

-- Ver facturas con cliente
SELECT f.numero_factura, c.nombre, f.total, f.estado, f.fecha_emision
FROM facturas f
JOIN clientes c ON f.cliente_id = c.cliente_id
WHERE f.tenant_id = 1
ORDER BY f.fecha_emision DESC;

-- Ver pagos realizados
SELECT p.numero_referencia, f.numero_factura, fp.nombre, p.monto, p.fecha_pago
FROM pagos p
JOIN facturas f ON p.factura_id = f.factura_id
JOIN formas_pago fp ON p.forma_pago_id = fp.forma_pago_id
WHERE p.tenant_id = 1;
```

**Resultado esperado**: Datos tabulados correctamente

---

## 🔄 SCRIPT COMPLETO (EJECUTAR TODO)

Si quieres copiar-pegar TODO de una vez:

```sql
-- 1. Tenant
INSERT INTO tenants (nombre, ruc, email, telefono, pais, ciudad, direccion, estado) 
VALUES ('DigiSports Demo', '20123456789', 'info@digisports.local', '+51987654321', 'PE', 'Lima', 'Av. Principal 123', 'A');

-- 2. Usuario
INSERT INTO usuarios (tenant_id, nombre, email, password, rol, estado) 
VALUES (1, 'Admin Usuario', 'admin@digisports.local', 'Admin123!', 'admin', 'A');

-- 3. Clientes
INSERT INTO clientes (tenant_id, nombre, ruc, email, telefono, direccion, estado) VALUES 
(1, 'Acme Corporation', '20123456789', 'contacto@acme.com', '+51987654321', 'Av. Principal 100', 'A'),
(1, 'Tech Solutions SA', '20987654321', 'ventas@techsol.com', '+51987654322', 'Calle Comercial 200', 'A'),
(1, 'Digital Agency Perú', '20111222333', 'info@digitalagency.com', '+51987654323', 'Av. Tecnológica 300', 'A'),
(1, 'Innovatech Industries', '20444555666', 'contacto@innovatech.com', '+51987654324', 'Jr. Industrial 400', 'A'),
(1, 'Premium Solutions', '20777888999', 'ventas@premiumbiz.com', '+51987654325', 'Av. Premium 500', 'A');

-- 4. Formas de Pago
INSERT INTO formas_pago (tenant_id, nombre, descripcion, estado) VALUES 
(1, 'Efectivo', 'Pago en efectivo', 'A'),
(1, 'Tarjeta de Crédito', 'Pago con tarjeta de crédito', 'A'),
(1, 'Transferencia Bancaria', 'Pago por transferencia bancaria', 'A'),
(1, 'Cheque', 'Pago con cheque', 'A'),
(1, 'Depósito en Cuenta', 'Depósito directo en cuenta bancaria', 'A');

-- 5. Facturas
INSERT INTO facturas (tenant_id, cliente_id, numero_factura, fecha_emision, subtotal, impuesto, total, moneda, estado) VALUES 
(1, 1, 'F-001-2024-01', '2024-01-05', 900.00, 162.00, 1062.00, 'PEN', 'EMITIDA'),
(1, 2, 'F-002-2024-01', '2024-01-08', 2200.00, 396.00, 2596.00, 'PEN', 'PAGADA'),
(1, 3, 'F-003-2024-01', '2024-01-10', 700.00, 126.00, 826.00, 'PEN', 'EMITIDA'),
(1, 4, 'F-004-2024-01', '2024-01-12', 1500.00, 270.00, 1770.00, 'PEN', 'PAGADA'),
(1, 5, 'F-005-2024-01', '2024-01-15', 3000.00, 540.00, 3540.00, 'PEN', 'EMITIDA'),
(1, 1, 'F-006-2024-01', '2024-01-18', 1200.00, 216.00, 1416.00, 'PEN', 'PAGADA'),
(1, 2, 'F-007-2024-01', '2024-01-20', 2800.00, 504.00, 3304.00, 'PEN', 'EMITIDA'),
(1, 3, 'F-008-2024-01', '2024-01-22', 950.00, 171.00, 1121.00, 'PEN', 'PAGADA'),
(1, 4, 'F-009-2024-01', '2024-01-25', 4000.00, 720.00, 4720.00, 'PEN', 'EMITIDA'),
(1, 5, 'F-010-2024-01', '2024-01-28', 1800.00, 324.00, 2124.00, 'PEN', 'PAGADA'),
(1, 1, 'F-011-2024-02', '2024-02-01', 1100.00, 198.00, 1298.00, 'PEN', 'EMITIDA'),
(1, 2, 'F-012-2024-02', '2024-02-03', 2500.00, 450.00, 2950.00, 'PEN', 'PAGADA'),
(1, 3, 'F-013-2024-02', '2024-02-05', 800.00, 144.00, 944.00, 'PEN', 'EMITIDA'),
(1, 4, 'F-014-2024-02', '2024-02-08', 1600.00, 288.00, 1888.00, 'PEN', 'PAGADA'),
(1, 5, 'F-015-2024-02', '2024-02-10', 3200.00, 576.00, 3776.00, 'PEN', 'EMITIDA');

-- 6. Pagos
INSERT INTO pagos (tenant_id, factura_id, forma_pago_id, monto, fecha_pago, numero_referencia, estado) VALUES 
(1, 2, 1, 2596.00, '2024-01-10', 'EF001', 'A'),
(1, 4, 2, 1770.00, '2024-01-15', 'TC001', 'A'),
(1, 6, 3, 1416.00, '2024-01-22', 'TRF001', 'A'),
(1, 8, 1, 1121.00, '2024-01-26', 'EF002', 'A'),
(1, 10, 2, 2124.00, '2024-02-02', 'TC002', 'A'),
(1, 12, 3, 2950.00, '2024-02-08', 'TRF002', 'A'),
(1, 14, 1, 1888.00, '2024-02-12', 'EF003', 'A');
```

---

## 🎯 PRÓXIMOS PASOS

1. ✅ Copiar cada bloque SQL
2. ✅ Pegar en phpMyAdmin → SQL
3. ✅ Click [Ejecutar]
4. ✅ Ver mensajes verdes "Consulta ejecutada"
5. ✅ Ir a http://localhost/digiSports/reportes/index
6. ✅ ¡Ver datos en PASO 5! 🎉

---

**¡Listo para probar PASO 5 con datos reales!** 🚀
