# 🚀 GUÍA DE PRUEBA: PASO 5 EN AMBIENTE LOCAL

## 📋 CHECKLIST PREVIO

Antes de comenzar, verifica que tengas:

```
✅ WAMP64 corriendo
✅ MySQL 8.0+ activo
✅ Apache 2.4+ corriendo
✅ PHP 8.2.13+ verificado
✅ digiSports descargado en c:\wamp64\www\digiSports
```

---

## 1️⃣ CREAR LA BASE DE DATOS

### Opción A: Usando phpMyAdmin (Fácil)

```
1. Abrir navegador
2. Ir a: http://localhost/phpmyadmin
3. Login con:
   - Usuario: root
   - Contraseña: (dejar en blanco si es default)
```

### Opción B: Usando Terminal MySQL

```bash
# Abrir Command Prompt como Administrador
cd C:\wamp64\bin\mysql\mysql8.0.13\bin

# Ejecutar comando MySQL
mysql -u root -p

# Si no pide contraseña, solo presiona Enter
```

### Crear Base de Datos

**En phpMyAdmin**:
```
1. Click en "Nueva"
2. Nombre: digisports_core
3. Codificación: utf8mb4_unicode_ci
4. Crear
```

**O en Terminal**:
```sql
CREATE DATABASE IF NOT EXISTS digisports_core 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;
USE digisports_core;
```

### Importar SQL

**En phpMyAdmin**:
```
1. Seleccionar BD: digisports_core
2. Click en "Importar"
3. Seleccionar archivo: database/digisports_core.sql
4. Ejecutar
```

**O en Terminal**:
```bash
mysql -u root digisports_core < C:\wamp64\www\digiSports\database\digisports_core.sql
mysql -u root digisports_core < C:\wamp64\www\digiSports\database\schema_instalaciones.sql
```

---

## ✅ VERIFICAR BASE DE DATOS

En phpMyAdmin o Terminal, ejecutar:

```sql
-- Ver que las tablas estén creadas
USE digisports_core;
SHOW TABLES;

-- Ver registros principales
SELECT * FROM tenants LIMIT 5;
SELECT * FROM usuarios LIMIT 5;
SELECT * FROM facturas LIMIT 5;
```

**Resultado esperado**: 
```
✅ Tablas visibles
✅ Datos cargados correctamente
✅ Estructura intacta
```

---

## 2️⃣ CONFIGURAR CONEXIÓN BD

Verificar que `config/database.php` esté correcto:

```php
// config/database.php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');              // Cambiar si tienes contraseña
define('DB_NAME', 'digisports_core');
define('DB_PORT', 3306);
```

---

## 3️⃣ CREAR DATOS DE PRUEBA (IMPORTANTE)

Si la BD está vacía, necesitas datos. Ejecuta en phpMyAdmin:

```sql
USE digisports_core;

-- Insertar tenant de prueba
INSERT INTO tenants (nombre, ruc, email, telefono) VALUES
('DigiSports Demo', '20123456789', 'demo@digisports.local', '+51987654321');

-- Insertar usuario admin
INSERT INTO usuarios (tenant_id, nombre, email, password, rol, estado) VALUES
(1, 'Admin Usuario', 'admin@digisports.local', 
 '$2y$10$...', 'admin', 'A');

-- Insertar clientes
INSERT INTO clientes (tenant_id, nombre, ruc, email) VALUES
(1, 'Cliente 1', '20111111111', 'cliente1@email.com'),
(1, 'Cliente 2', '20222222222', 'cliente2@email.com'),
(1, 'Cliente 3', '20333333333', 'cliente3@email.com');

-- Insertar formas de pago
INSERT INTO formas_pago (tenant_id, nombre, descripcion) VALUES
(1, 'Efectivo', 'Pago en efectivo'),
(1, 'Tarjeta de Crédito', 'Pago con tarjeta de crédito'),
(1, 'Transferencia Bancaria', 'Pago por transferencia');

-- Insertar facturas de prueba
INSERT INTO facturas (tenant_id, cliente_id, numero_factura, fecha_emision, total, estado) VALUES
(1, 1, 'F001-2024-01', '2024-01-15', 1000.00, 'EMITIDA'),
(1, 2, 'F002-2024-01', '2024-01-18', 2500.00, 'PAGADA'),
(1, 3, 'F003-2024-01', '2024-01-20', 750.50, 'EMITIDA');

-- Insertar pagos
INSERT INTO pagos (tenant_id, factura_id, forma_pago_id, monto, fecha_pago) VALUES
(1, 2, 2, 2500.00, '2024-01-25');
```

---

## 4️⃣ ACCEDER AL SISTEMA

### Paso 1: Abrir Navegador
```
URL: http://localhost/digiSports/public/
```

### Paso 2: Login
```
Usuario: admin@digisports.local
Contraseña: Admin123!
(O ver datos reales en tabla usuarios)
```

### Paso 3: Verificar que Carga
```
✅ Deberías ver el dashboard principal
✅ Menú lateral con opciones
✅ Sin errores en consola (F12)
```

---

## 5️⃣ ACCEDER A PASO 5 (REPORTES)

### Opción A: Desde el Menú
```
1. Login correcto
2. Buscar en menú: "Reportes" o "Dashboard"
3. Click en "Dashboard" o "Reportes"
```

### Opción B: URL Directa
```
http://localhost/digiSports/reportes/index
```

### Opción C: KPIs
```
http://localhost/digiSports/reportes/kpi?periodo=mes
```

---

## 📊 PRUEBAS A REALIZAR

### Test 1: Dashboard Carga ✅
```
1. Ir a: http://localhost/digiSports/reportes/index
2. Verificar:
   ✅ Página carga sin errores
   ✅ 4 KPI cards visibles
   ✅ 3 gráficos renderizan
   ✅ Tablas muestran datos
```

### Test 2: Filtros Funcionan ✅
```
1. En Dashboard, click botón "Este Mes"
2. Verificar:
   ✅ Datos se actualizan
   ✅ Gráficos cambian
   ✅ Sin errores en consola
```

### Test 3: Reporte de Facturas ✅
```
1. Ir a: http://localhost/digiSports/reportes/facturas
2. Verificar:
   ✅ Tabla de facturas visible
   ✅ Filtros funcionan (fecha, estado)
   ✅ Paginación OK
   ✅ Botón "Ver" link funciona
```

### Test 4: Exportar CSV ✅
```
1. En cualquier reporte, click "Exportar CSV"
2. Verificar:
   ✅ Se descarga archivo .csv
   ✅ Abre en Excel correctamente
   ✅ Datos completos
```

### Test 5: KPI Dashboard ✅
```
1. Ir a: http://localhost/digiSports/reportes/kpi?periodo=mes
2. Verificar:
   ✅ 8 KPIs visibles
   ✅ Tendencias calculadas
   ✅ Alertas se muestran
   ✅ Gráfico de evolución renderiza
```

---

## 🐛 TROUBLESHOOTING

### Problema: "Página en blanco" o Error 404

**Solución**:
```
1. Verificar que las rutas están en config/Router.php
2. Agregar si falta:
   Router::get('/reportes', 'reportes', 'reporte', 'index');
   Router::get('/reportes/kpi', 'reportes', 'kpi', 'index');
```

### Problema: "Database connection error"

**Solución**:
```
1. Verificar MySQL está corriendo en WAMP
2. Verificar credenciales en config/database.php
3. Probar: mysql -u root -p digisports_core
```

### Problema: "No hay datos en reportes"

**Solución**:
```
1. Crear datos de prueba (ver sección 3)
2. Verificar tenant_id en datos
3. Verificar que tienes permisos en sesión
```

### Problema: "Gráficos en blanco"

**Solución**:
```
1. Abrir consola (F12)
2. Verificar que Chart.js CDN cargó
3. Ver si hay errores de JavaScript
4. Verificar datos en tabla HTML
```

### Problema: "403 Forbidden o sin permisos"

**Solución**:
```
1. Verificar que estás logueado
2. Verificar que user_id está en $_SESSION
3. Revisar permisos en BD
```

---

## 🔍 VERIFICAR EN CONSOLA (F12)

### Red (Network)
```
✅ Verificar que /reportes/index carga (200 OK)
✅ Chart.js CDN carga (200 OK)
✅ Sin 404 o 500 errors
```

### Consola (Console)
```
✅ Sin errores JavaScript rojos
✅ Sin advertencias críticas
✅ Mensajes informativos OK
```

### Elements (Inspector)
```
✅ KPI cards HTML visibles
✅ Canvas elementos para gráficos
✅ Tablas con datos
```

---

## 📊 EJEMPLO DE FLOW COMPLETO

```
1. Abrir http://localhost/digiSports/public/
   └─ Dashboard principal carga

2. Login con admin@digisports.local / Admin123!
   └─ Sesión se inicia

3. Navegar a Reportes → Dashboard
   └─ http://localhost/digiSports/reportes/index
   └─ Ves 4 KPIs + 3 gráficos

4. Click "Este Mes"
   └─ Datos se actualizan
   └─ Gráficos cambian

5. Ir a Reportes → Facturas
   └─ Tabla de facturas visible
   └─ Filtros funcionan

6. Click "Exportar CSV"
   └─ Descarga archivo.csv
   └─ Abre en Excel

7. Ir a Reportes → KPIs
   └─ 8 KPIs con tendencias visibles
   └─ Alertas aparecen si aplica

✅ TODO FUNCIONA CORRECTAMENTE
```

---

## ✅ CHECKLIST FINAL

- [ ] Base de datos creada
- [ ] Usuarios y clientes de prueba insertados
- [ ] Config/database.php configurado
- [ ] Apache y MySQL corriendo
- [ ] Dashboard carga sin errores
- [ ] KPIs muestran datos
- [ ] Gráficos renderizan
- [ ] Filtros funcionan
- [ ] Exportación CSV OK
- [ ] Alertas aparecen
- [ ] Sin errores en consola F12
- [ ] Performance < 3s

---

## 🎯 ¿AHORA QUÉ?

Si todo funciona:
1. ✅ PASO 5 está operativo
2. ✅ Puedes crear más datos de prueba
3. ✅ Puedes personalizar filtros
4. ✅ Puedes agregar más usuarios

Si algo no funciona:
1. Revisar logs: `/storage/logs/`
2. Revisar consola F12
3. Consultar documentación: `PASO_5_REPORTES.md`

---

**¡A probar! 🚀**

Si encuentras problemas, consulta el archivo de [troubleshooting en la documentación](PASO_5_REPORTES.md#-troubleshooting).
