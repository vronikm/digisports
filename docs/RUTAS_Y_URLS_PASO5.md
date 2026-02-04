# 🌐 RUTAS Y URLs: PASO 5 - GUÍA RÁPIDA

## 🎯 URLs DE ACCESO DIRECTO

### Dashboard Principal
```
http://localhost/digiSports/reportes/index
```

### KPIs - Diferentes Períodos
```
http://localhost/digiSports/reportes/kpi?periodo=semana
http://localhost/digiSports/reportes/kpi?periodo=mes
http://localhost/digiSports/reportes/kpi?periodo=trimestre
http://localhost/digiSports/reportes/kpi?periodo=año
```

### Reportes
```
http://localhost/digiSports/reportes/facturas
http://localhost/digiSports/reportes/ingresos
http://localhost/digiSports/reportes/clientes
```

### Exportar
```
http://localhost/digiSports/reportes/exportarCSV?tipo=facturas
http://localhost/digiSports/reportes/exportarCSV?tipo=ingresos
http://localhost/digiSports/reportes/exportarCSV?tipo=clientes
```

---

## 📂 ESTRUCTURA DE CARPETAS

### Controladores
```
app/controllers/reportes/
├── ReporteController.php
└── KPIController.php
```

### Vistas
```
app/views/reportes/
├── index.php              # Dashboard
├── facturas.php           # Reporte facturas
├── ingresos.php           # Reporte ingresos
├── clientes.php           # Reporte clientes
└── kpi.php                # Dashboard KPIs
```

### Base de Datos
```
database/
├── digisports_core.sql
└── schema_instalaciones.sql
```

### Documentación
```
Raíz/
├── PASO_5_REPORTES.md
├── PASO_5_INICIO_RAPIDO.md
├── PASO_5_VALIDACION_EJECUTIVA.md
├── PASO_5_INDICE.md
├── PASO_5_INSTALACION.md
├── GUIA_PRUEBA_PASO5.md
├── GUIA_VISUAL_PASO5.md
├── SQL_RAPIDO_PASO5.md
└── (+ más documentación)
```

---

## 🔧 CONFIGURACIÓN

### Base de Datos
```
config/database.php

Valores esperados:
DB_HOST = localhost
DB_USER = root
DB_PASS = (vacío)
DB_NAME = digisports_core
DB_PORT = 3306
```

### Router
```
config/Router.php

Rutas PASO 5:
Router::get('/reportes', 'reportes', 'reporte', 'index');
Router::get('/reportes/kpi', 'reportes', 'kpi', 'index');
```

---

## 🗂️ NAVEGACIÓN POR TIPO

### Para Usuario Admin
```
1. http://localhost/digiSports/public/
   └─ Login

2. http://localhost/digiSports/dashboard/
   └─ Dashboard principal

3. http://localhost/digiSports/reportes/index
   └─ Reportes
```

### Para Reportería
```
http://localhost/digiSports/reportes/facturas
http://localhost/digiSports/reportes/ingresos
http://localhost/digiSports/reportes/clientes
```

### Para Análisis
```
http://localhost/digiSports/reportes/kpi?periodo=mes
http://localhost/digiSports/reportes/kpi?periodo=trimestre
http://localhost/digiSports/reportes/kpi?periodo=año
```

---

## 📊 ENDPOINTS DISPONIBLES

### ReporteController
```
GET  /reportes/index           → Dashboard principal
GET  /reportes/facturas        → Reporte de facturas
GET  /reportes/ingresos        → Reporte de ingresos
GET  /reportes/clientes        → Reporte de clientes
GET  /reportes/exportarCSV     → Exportar a CSV (params: tipo)
```

### KPIController
```
GET  /reportes/kpi             → Dashboard KPIs (param: periodo)
POST /reportes/kpi/calcular    → Calcular KPIs (interno)
```

---

## 🎯 MAPEO DE FUNCIONALIDADES

### Dashboard (index.php)
```
URL:  http://localhost/digiSports/reportes/index
Ruta: app/views/reportes/index.php

Componentes:
✅ 4 KPI Cards
✅ 3 Gráficos Chart.js
✅ Tabla Top 5 Clientes
✅ Tabla Últimas 10 Facturas
✅ Selector de período
```

### Reporte Facturas (facturas.php)
```
URL:  http://localhost/digiSports/reportes/facturas
Ruta: app/views/reportes/facturas.php

Funciones:
✅ Tabla con 25 registros/página
✅ Filtros: Fecha, Estado
✅ Paginación
✅ Exportar CSV
```

### Reporte Ingresos (ingresos.php)
```
URL:  http://localhost/digiSports/reportes/ingresos
Ruta: app/views/reportes/ingresos.php

Funciones:
✅ Análisis por día
✅ Resumen: Total, Pagado, Promedio
✅ Gráfico Ingresos por Forma Pago
✅ Exportar CSV
```

### Reporte Clientes (clientes.php)
```
URL:  http://localhost/digiSports/reportes/clientes
Ruta: app/views/reportes/clientes.php

Funciones:
✅ Búsqueda (nombre/RUC)
✅ Ordenamiento: Facturado, Pagado, Saldo, Facturas
✅ Tabla de análisis
✅ Estadísticas generales
✅ Exportar CSV
```

### Dashboard KPIs (kpi.php)
```
URL:  http://localhost/digiSports/reportes/kpi?periodo=mes
Ruta: app/views/reportes/kpi.php

Funciones:
✅ 8 KPIs principales
✅ Selector de período (botones)
✅ Alertas automáticas
✅ Gráfico de evolución
✅ Comparación vs período anterior
```

---

## 🔐 PARÁMETROS DISPONIBLES

### Período
```
?periodo=semana     → Últimos 7 días
?periodo=mes        → Mes actual
?periodo=trimestre  → Últimos 3 meses
?periodo=año        → Último año
```

### Filtros de Reporte
```
?fecha_inicio=2024-01-01
?fecha_fin=2024-01-31
?estado=EMITIDA|PAGADA|ANULADA|BORRADOR
?forma_pago_id=1|2|3
?pagina=1
?busqueda=cliente_name
?ordenar=total_facturado|total_pagado|saldo|num_facturas
```

### Exportación
```
?tipo=facturas|ingresos|clientes
```

---

## 📋 CHECKLIST DE RUTAS

Prueba estas URLs para verificar que todo funciona:

```
[ ] http://localhost/digiSports/public/
    └─ Login page

[ ] http://localhost/digiSports/dashboard/
    └─ Dashboard principal

[ ] http://localhost/digiSports/reportes/index
    └─ Dashboard reportes (4 KPIs)

[ ] http://localhost/digiSports/reportes/facturas
    └─ Tabla de facturas

[ ] http://localhost/digiSports/reportes/ingresos
    └─ Análisis ingresos

[ ] http://localhost/digiSports/reportes/clientes
    └─ Reporte clientes

[ ] http://localhost/digiSports/reportes/kpi?periodo=mes
    └─ Dashboard KPIs - Mes

[ ] http://localhost/digiSports/reportes/kpi?periodo=trimestre
    └─ Dashboard KPIs - Trimestre

[ ] http://localhost/digiSports/reportes/exportarCSV?tipo=facturas
    └─ Descarga CSV facturas

[ ] http://localhost/digiSports/phpmyadmin
    └─ phpMyAdmin (ver BD)
```

---

## 🚨 ERRORES COMUNES Y SOLUCIÓN

### Error: "Página no encontrada" (404)

**URL incorrecto**:
```
❌ http://localhost/digiSports/reports/index
✅ http://localhost/digiSports/reportes/index  ← reportes, no reports
```

### Error: "No tiene permiso" (403)

**No está logueado**:
```
❌ Intenta acceder sin login
✅ Primero: http://localhost/digiSports/public/
   Luego: Login
   Entonces: /reportes/
```

### Error: "Database connection"

**BD no conecta**:
```
❌ MySQL no está corriendo
✅ Verificar WAMP está verde
✅ Verificar config/database.php
```

### Error: "Tabla no encontrada"

**BD no tiene datos**:
```
❌ SQL no fue importado
✅ Importar digisports_core.sql en phpMyAdmin
✅ Ejecutar SQL de datos de prueba
```

---

## 🎯 FLUJO TÍPICO DE USO

```
1. http://localhost/digiSports/public/
   ↓ Login
   
2. http://localhost/digiSports/dashboard/
   ↓ Click "Reportes"
   
3. http://localhost/digiSports/reportes/index
   ↓ Ver KPIs y gráficos
   ↓ Click "Este Mes"
   
4. Datos se actualizan
   ↓ Click "Ver Reportes Detallados"
   
5. http://localhost/digiSports/reportes/facturas
   ↓ Filtrar, paginar
   ↓ Click "Exportar CSV"
   
6. reporte.csv se descarga
   ↓ Abrir en Excel
   
✅ FIN
```

---

## 📞 REFERENCIAS RÁPIDAS

### Documentación
- `PASO_5_REPORTES.md` - Técnica
- `PASO_5_INICIO_RAPIDO.md` - Usuario
- `GUIA_VISUAL_PASO5.md` - Paso a paso
- `SQL_RAPIDO_PASO5.md` - Scripts SQL

### Herramientas
- `http://localhost/phpmyadmin` - Administrar BD
- `F12` - Consola del navegador (debugging)
- `http://localhost/digiSports/storage/logs/` - Ver logs

### Contacto
- Email: admin@digisports.local
- Documentación: `/docs/`

---

**Todas las URLs están listas para usar** ✅
