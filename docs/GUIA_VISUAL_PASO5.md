# 📱 GUÍA VISUAL: PASO 5 DESDE CERO

## 🎯 META
Tener PASO 5 funcionando en tu navegador en 15 minutos

---

## ⏱️ PASO 1: PREPARACIÓN (2 minutos)

### Verificar WAMP está corriendo

```
1. Abrir Windows
2. Buscar: WAMP64
3. Ejecutar WampServer
4. Esperar a que aparezca icono en bandeja (debe ser verde)
```

**Icono en bandeja**:
- 🟢 Verde = WAMP corriendo perfectamente
- 🟡 Naranja = Algún servicio parado
- 🔴 Rojo = Error en servicios

### Si está rojo o naranja

```
1. Click derecho en icono WAMP
2. Ir a: www directory
3. Verificar que digiSports está en C:\wamp64\www\digiSports
4. Si falta, copiar ahí
```

---

## 📊 PASO 2: CREAR BASE DE DATOS (3 minutos)

### Opción MÁS FÁCIL: phpMyAdmin

```
1. Abrir navegador (Chrome, Firefox, Edge)
2. Ir a: http://localhost/phpmyadmin
3. Login:
   - Usuario: root
   - Contraseña: (dejar vacío, Enter)
```

**Pantalla que deberías ver**:
```
┌─────────────────────────────────────┐
│ phpMyAdmin                          │
├─────────────────────────────────────┤
│ Bases de Datos | Herramientas       │
│                                     │
│ [Nueva] [Importar] [Exportar]       │
│                                     │
│ Bases de datos:                     │
│ • information_schema                │
│ • mysql                             │
│ • performance_schema                │
│ • sys                               │
│ • test                              │
│ • wordpress (si lo tienes)          │
└─────────────────────────────────────┘
```

### Crear base de datos

```
1. Click en botón [Nueva]
2. Nombre de la BD: digisports_core
3. Codificación: utf8mb4_unicode_ci
4. Crear

Resultado esperado:
✅ Nuevo elemento "digisports_core" en lista
```

### Importar datos

```
1. Seleccionar: digisports_core (click izquierdo)
2. Ver en arriba: Importar | Exportar | ...
3. Click en [Importar]
4. Click en "Seleccionar archivo"
5. Navegar a: C:\wamp64\www\digiSports\database\
6. Seleccionar: digisports_core.sql
7. Click [Abrir]
8. Botón [Ejecutar] (abajo)

Esperar... (toma 10-20 segundos)

Resultado esperado:
✅ "Importación exitosa" mensaje verde
✅ Múltiples tablas aparecen en panel izquierdo
```

**Tablas que deberías ver** (lado izquierdo):
```
digisports_core ▼
├── clientes
├── facturas
├── formas_pago
├── modulos_sistema
├── pagos
├── tenants
├── usuarios
├── (+ 20 más)
```

---

## 👤 PASO 3: CREAR USUARIO DE PRUEBA (3 minutos)

### Opción A: phpMyAdmin (Recomendado)

```
1. En phpMyAdmin, en tabla "usuarios"
2. Click en [Insertar] (arriba)
3. Llenar campos:
```

**Formulario a llenar**:
```
usuario_id:        (AUTO - dejar vacío)
tenant_id:         1
nombre:            Admin Prueba
email:             admin@digisports.local
password:          $2y$10$N9qo8uLO... (ver abajo)
rol:               admin
estado:            A
```

### Generar contraseña encriptada

En tu navegador, consola (F12), escribe:
```javascript
// Simular contraseña encriptada (para prueba)
"Admin123!"
```

O simplemente usa en la BD directamente (no es seguro pero es prueba):
```sql
INSERT INTO usuarios (tenant_id, nombre, email, password, rol, estado) 
VALUES (1, 'Admin Prueba', 'admin@digisports.local', 'Admin123!', 'admin', 'A');
```

**Resultado esperado**:
```
✅ Fila insertada correctamente
✅ usuario_id asignado automáticamente
```

---

## 💰 PASO 4: CREAR DATOS DE PRUEBA (3 minutos)

### En phpMyAdmin, ejecutar estos comandos:

**Click en [SQL]** (pestaña arriba)

```sql
-- Insertar clientes
INSERT INTO clientes (tenant_id, nombre, ruc, email) VALUES
(1, 'Acme Corp', '20123456789', 'acme@email.com'),
(1, 'Tech Solutions', '20987654321', 'tech@email.com'),
(1, 'Digital Agency', '20111222333', 'digital@email.com');

-- Insertar facturas
INSERT INTO facturas (tenant_id, cliente_id, numero_factura, fecha_emision, total, estado) VALUES
(1, 1, 'F-001-2024', '2024-01-15', 1000.00, 'EMITIDA'),
(1, 2, 'F-002-2024', '2024-01-18', 2500.00, 'PAGADA'),
(1, 3, 'F-003-2024', '2024-01-20', 750.50, 'EMITIDA'),
(1, 1, 'F-004-2024', '2024-01-22', 3200.00, 'PAGADA');

-- Insertar pagos
INSERT INTO pagos (tenant_id, factura_id, forma_pago_id, monto, fecha_pago) VALUES
(1, 2, 1, 2500.00, '2024-01-25'),
(1, 4, 2, 3200.00, '2024-01-26');
```

**Click [Ejecutar]**

```
Resultado esperado:
✅ "Consulta ejecutada con éxito" (3 veces)
```

---

## 🔓 PASO 5: LOGIN AL SISTEMA (2 minutos)

### Abrir navegador

```
URL: http://localhost/digiSports/public/
```

**Deberías ver**:
```
╔═══════════════════════════════════════╗
│                                       │
│        🔐 digiSports - Login          │
│                                       │
│  Email: [________________]            │
│  Contraseña: [________________]       │
│                                       │
│         [ENTRAR]  [REGISTRAR]        │
│                                       │
╚═══════════════════════════════════════╝
```

### Ingresar datos

```
Email:      admin@digisports.local
Contraseña: Admin123!
Click [ENTRAR]
```

**Pantalla siguiente esperada**:
```
Debería redirigir a: http://localhost/digiSports/dashboard/
Y ver el DASHBOARD PRINCIPAL
```

---

## 📊 PASO 6: ACCEDER A PASO 5 (2 minutos)

### Opción A: Desde el navegador (URL directa)

```
Copiar en navegador:
http://localhost/digiSports/reportes/index

Enter...
```

**Deberías ver**:
```
╔════════════════════════════════════════╗
║        📊 Dashboard de Reportes        ║
├────────────────────────────────────────┤
║                                        ║
║  [Esta Semana] [Este Mes] ...         ║
║                                        ║
║  ┌────────┐ ┌────────┐ ┌────────┐    ║
║  │ $5,700 │ │   4    │ │  75%   │    ║
║  │Ingresos│ │Facturas│ │Cobranza│    ║
║  └────────┘ └────────┘ └────────┘    ║
║                                        ║
║  ┌──────────────────────────────────┐ ║
║  │ Gráfico 1 (Ingresos por día)    │ ║
║  └──────────────────────────────────┘ ║
║                                        ║
║  ┌──────────────────────────────────┐ ║
║  │ Tabla: Top 5 Clientes           │ ║
║  │ Acme Corp      | $2,500         │ ║
║  │ Tech Solutions | $2,000         │ ║
║  └──────────────────────────────────┘ ║
╚════════════════════════════════════════╝
```

### ✅ Si VES ESO = PASO 5 FUNCIONA 🎉

---

## 🔍 PASO 7: PRUEBAS RÁPIDAS (3 minutos)

### Test 1: Cambiar período

```
1. Click botón "Este Mes"
2. Observar:
   ✅ Los números cambian
   ✅ Gráficos se actualizan
   ✅ Sin errores
```

### Test 2: Ver otro reporte

```
URL: http://localhost/digiSports/reportes/facturas

Deberías ver:
✅ Tabla con tus facturas
✅ Columnas: Factura, Cliente, Fecha, Total, Pagado, Estado
✅ Botones de filtro
```

### Test 3: Exportar CSV

```
1. En cualquier reporte, scroll abajo
2. Buscar botón "Exportar CSV"
3. Click
4. Se descarga archivo "reporte.csv"
5. Abrir en Excel
6. ✅ Datos correctos
```

### Test 4: Ver KPIs

```
URL: http://localhost/digiSports/reportes/kpi?periodo=mes

Deberías ver:
✅ 8 tarjetas con números
✅ Flechas de tendencia (↑ ↓)
✅ Gráfico de evolución
```

---

## 🐛 SI ALGO FALLA

### Error: "No se encontró la página"

```
❌ Error 404

Solución:
1. Verificar URL es exacta
2. Verificar que directorio /reportes existe
3. Revisar config/Router.php
```

### Error: "Error de conexión a BD"

```
❌ Database Connection Error

Solución:
1. Verificar MySQL corre (icono WAMP verde)
2. Verificar config/database.php tiene credenciales correctas
3. En phpMyAdmin, verificar BD "digisports_core" existe
```

### Error: "Tablas no encontradas"

```
❌ SQLSTATE[42S02]: Table not found

Solución:
1. Ir a phpMyAdmin
2. Seleccionar digisports_core
3. Verificar que tabla "facturas" existe
4. Si no, reimportar digisports_core.sql
```

### Página en blanco sin errores

```
❌ Blank page

Solución:
1. Abrir consola F12
2. Ver qué error muestra
3. Si dice Chart.js error → problema con gráficos
4. Si no dice nada → revisar logs en /storage/logs/
```

---

## ✅ CHECKLIST FINAL

Marca cuando cada cosa esté lista:

```
[ ] WAMP corriendo (icono verde)
[ ] phpMyAdmin accesible (http://localhost/phpmyadmin)
[ ] BD "digisports_core" creada
[ ] SQL importado sin errores
[ ] Usuario admin creado
[ ] Datos de prueba insertados
[ ] Login funciona
[ ] Dashboard carga
[ ] PASO 5 Dashboard visible
[ ] Gráficos muestran datos
[ ] Filtros funcionan
[ ] Exportación CSV OK
[ ] Sin errores en consola F12
```

---

## 🎉 ¡LO LOGRASTE!

Si marcaste todos los checks = **PASO 5 está funcionando correctamente en tu ambiente** ✅

---

## 📞 ¿PRÓXIMOS PASOS?

Ahora puedes:

1. **Crear más datos de prueba**
   - Más clientes, facturas, pagos

2. **Personalizar reportes**
   - Cambiar filtros
   - Agregar nuevas columnas

3. **Customizar vistas**
   - Cambiar colores
   - Agregar logos
   - Traducir textos

4. **Ir a PASO 6**
   - Business Intelligence
   - Predicciones
   - Reportes avanzados

---

**¡Que disfrutes PASO 5! 🚀**
