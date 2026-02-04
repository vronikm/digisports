# 📊 PASO 5: Sistema de Reportes - Documentación Completa

## 📋 Tabla de Contenidos
1. [Introducción](#introducción)
2. [Arquitectura](#arquitectura)
3. [Controladores](#controladores)
4. [Vistas](#vistas)
5. [KPIs Implementados](#kpis-implementados)
6. [Guía de Uso](#guía-de-uso)
7. [Troubleshooting](#troubleshooting)

---

## 🎯 Introducción

PASO 5 es el sistema completo de reportes y análisis de digiSports. Proporciona:

✅ **Dashboard Ejecutivo** - Visión integral de la empresa  
✅ **KPIs Dinámicos** - Indicadores clave con período de comparación  
✅ **Reportes Detallados** - Facturas, ingresos, clientes  
✅ **Gráficos Interactivos** - Chart.js con múltiples visualizaciones  
✅ **Alertas Inteligentes** - Notificaciones automáticas de anomalías  
✅ **Exportación** - CSV de todos los reportes  

---

## 🏗️ Arquitectura

### Stack Tecnológico
```
Frontend:    Bootstrap 5.3 + Chart.js 3.9.1 + Font Awesome 6
Backend:     PHP 8.2.13 + MySQL 8.0+
Patrón:      MVC (Model-View-Controller)
Datos:       PASO 4 (facturas, pagos, formas_pago)
```

### Estructura de Directorios
```
app/
├── controllers/
│   └── reportes/
│       ├── ReporteController.php    # Reportes principales
│       └── KPIController.php        # Indicadores clave
└── views/
    └── reportes/
        ├── index.php                # Dashboard
        ├── facturas.php             # Reporte facturas
        ├── ingresos.php             # Reporte ingresos
        ├── clientes.php             # Reporte clientes
        └── kpi.php                  # Dashboard KPIs
```

### Flujo de Datos
```
Base de Datos (PASO 4)
    ↓
ReporteController / KPIController
    ↓
Procesamiento (Agregaciones, Cálculos)
    ↓
Vistas (HTML + JavaScript)
    ↓
Browser (Renderizado + Chart.js)
```

---

## 🔧 Controladores

### ReporteController.php

**Ubicación**: `app/controllers/reportes/ReporteController.php`

#### Métodos Públicos

```php
// Dashboard con KPIs y gráficos
public function index()

// Reporte detallado de facturas
public function facturas()

// Análisis de ingresos
public function ingresos()

// Reporte de clientes
public function clientes()

// Exportar datos a CSV
public function exportarCSV()
```

#### Métodos Privados (Helpers)

```php
// Obtener KPIs del período actual
private function obtenerKPIs()

// Gráfico de ingresos por día (Line Chart)
private function graficoIngresosPolínea()

// Gráfico de ingresos por forma de pago (Doughnut)
private function graficoFormaPago()

// Gráfico de facturas por estado (Pie)
private function graficoEstadoFactura()

// Top 5 clientes
private function obtenerTopClientes()

// Últimas 10 facturas
private function obtenerUltimasFacturas()
```

#### Ejemplo de Uso

```php
// Dashboard
GET /reportes/index

// Facturas con filtros
GET /reportes/facturas?fecha_inicio=2024-01-01&fecha_fin=2024-01-31&estado=EMITIDA

// Exportar CSV
GET /reportes/exportarCSV?tipo=facturas&fecha_inicio=2024-01-01&fecha_fin=2024-01-31
```

---

### KPIController.php

**Ubicación**: `app/controllers/reportes/KPIController.php`

#### Métodos Públicos

```php
// Dashboard de KPIs con período de comparación
public function index()
```

#### Métodos Privados

```php
// Obtener fechas del período
private function obtenerFechas()

// Calcular 8 KPIs principales
private function calcularKPIs()

// Calcular tendencia (comparación con período anterior)
private function calcularTendencia()

// Generar alertas inteligentes
private function generarAlertas()
```

#### Períodos Soportados

| Período | Rango | Anterior |
|---------|-------|----------|
| **semana** | Últimos 7 días | 7-14 días atrás |
| **mes** | Mes actual (1-28) | Mes anterior |
| **trimestre** | Últimos 3 meses | Trimestre anterior |
| **año** | Últimos 365 días | Año anterior |

#### Ejemplo de Uso

```php
// KPI dashboard - semana actual
GET /reportes/kpi?periodo=semana

// KPI dashboard - mes actual
GET /reportes/kpi?periodo=mes

// KPI dashboard - trimestre actual
GET /reportes/kpi?periodo=trimestre

// KPI dashboard - año actual
GET /reportes/kpi?periodo=año
```

---

## 📊 Vistas

### 1. Dashboard Principal (index.php)

**Ruta**: `/reportes/index`

**Componentes**:
- 4 KPI Cards: Ingresos, Facturas, Cobranza, Pendiente
- 3 Gráficos interactivos (Chart.js)
- Tabla Top 5 Clientes
- Tabla Últimas 10 Facturas
- Botones de período: Semana/Mes/Trimestre/Año

**Características**:
- Responsive Bootstrap 5.3
- Colores dinámicos según estado
- Links a facturas individuales
- Actualización por período

---

### 2. Reporte de Facturas (facturas.php)

**Ruta**: `/reportes/facturas`

**Filtros**:
- Rango de fechas (desde/hasta)
- Estado (Borrador, Emitida, Pagada, Anulada)

**Columnas**:
| Columna | Descripción |
|---------|-------------|
| Factura | Número de factura |
| Cliente | Nombre del cliente |
| Fecha | Fecha de emisión |
| Total | Monto total |
| Pagado | Monto pagado |
| Saldo | Monto pendiente |
| Estado | Estado actual |
| Acciones | Ver detalle |

**Características**:
- Paginación (25 por página)
- Badges coloreados por estado
- Botón descargar CSV
- Links a factura individual

---

### 3. Reporte de Ingresos (ingresos.php)

**Ruta**: `/reportes/ingresos`

**Filtros**:
- Rango de fechas
- Forma de pago

**Secciones**:
1. **Resumen**: Total, Pagado, Promedio diario, Transacciones
2. **Tabla Ingresos por Día**: Fecha, Día semana, Facturas, Total facturado, Pagado, % Cobranza
3. **Gráfico Ingresos por Forma de Pago** (Doughnut Chart)

**Características**:
- Análisis temporal detallado
- % Cobranza con badges
- Gráfico interactivo

---

### 4. Reporte de Clientes (clientes.php)

**Ruta**: `/reportes/clientes`

**Filtros**:
- Búsqueda por nombre/RUC
- Ordenar por: Total facturado, Total pagado, Saldo pendiente, Num. facturas

**Columnas**:
| Columna | Descripción |
|---------|-------------|
| Cliente | Nombre del cliente |
| RUC | RUC del cliente |
| Num. Facturas | Cantidad de facturas |
| Total Facturado | Suma de facturas |
| Total Pagado | Suma de pagos |
| Saldo Pendiente | Cantidad adeudada |
| % Cobranza | Porcentaje pagado |
| Última Factura | Fecha de última factura |
| Acciones | Ver detalle |

**Características**:
- Paginación
- Búsqueda en tiempo real
- Ordenamiento personalizado
- Estadísticas generales

---

### 5. Dashboard KPIs (kpi.php)

**Ruta**: `/reportes/kpi?periodo=mes`

**KPIs Principales** (con tendencia):
1. Total de Ingresos
2. Facturas Emitidas
3. Tasa de Cobranza (%)
4. Saldo Pendiente

**KPIs Secundarios**:
5. Monto Promedio
6. Clientes Únicos
7. Facturas Pagadas
8. Días Promedio Pago

**Características**:
- Indicadores con flecha de tendencia
- Alertas inteligentes (3 tipos)
- Comparación vs. período anterior
- Gráfico de evolución (Line Chart)

---

## 📈 KPIs Implementados

### 1. Total de Ingresos
```
Definición:  Suma de todos los pagos en el período
Fórmula:     SUM(pagos.monto) WHERE fecha BETWEEN inicio AND fin
Tendencia:   Comparación con período anterior
Alerta:      Si disminuye > 20% vs. anterior
```

### 2. Número de Facturas
```
Definición:  Cantidad de facturas emitidas
Fórmula:     COUNT(DISTINCT facturas.id) WHERE estado = 'EMITIDA'
Tendencia:   Comparación con período anterior
```

### 3. Facturas Pagadas
```
Definición:  Cantidad de facturas con estado PAGADA
Fórmula:     COUNT(*) WHERE estado = 'PAGADA'
Tendencia:   Comparación con período anterior
```

### 4. Tasa de Cobranza (%)
```
Definición:  Porcentaje de ingresos vs. facturación
Fórmula:     (SUM(pagado) / SUM(total)) * 100
Meta:        >= 70%
Alerta:      Si < 70%
```

### 5. Monto Promedio
```
Definición:  Promedio de valor por factura
Fórmula:     SUM(total) / COUNT(facturas)
```

### 6. Clientes Únicos
```
Definición:  Cantidad de clientes únicos
Fórmula:     COUNT(DISTINCT cliente_id)
```

### 7. Saldo Pendiente
```
Definición:  Monto total adeudado
Fórmula:     SUM(total - pagado) WHERE estado IN ('EMITIDA', 'PAGADA')
Alerta:      Si > 30% de ingresos
```

### 8. Días Promedio Pago
```
Definición:  Días promedio entre emisión y pago
Fórmula:     AVG(DATEDIFF(fecha_pago, fecha_emision))
Alerta:      Si > 30 días
```

---

## 🚀 Guía de Uso

### Acceso al Dashboard

1. **Ingresar a digiSports**
   ```
   http://localhost/digiSports/public/
   ```

2. **Navegar a Reportes**
   - Desde el menú principal: Reportes > Dashboard

3. **Seleccionar período**
   - Botones: Esta Semana, Este Mes, Este Trimestre, Este Año

### Generar Reportes

#### Reporte de Facturas
```
1. Ir a Reportes > Facturas
2. Seleccionar rango de fechas
3. Filtrar por estado (opcional)
4. Clic en "Filtrar"
5. Descargar CSV (opcional)
```

#### Reporte de Ingresos
```
1. Ir a Reportes > Ingresos
2. Seleccionar rango de fechas
3. Filtrar por forma de pago (opcional)
4. Analizar tabla y gráfico
5. Descargar CSV (opcional)
```

#### Reporte de Clientes
```
1. Ir a Reportes > Clientes
2. Buscar por nombre o RUC
3. Ordenar por columna deseada
4. Clic en factura para ver detalle
5. Descargar CSV (opcional)
```

#### Dashboard KPIs
```
1. Ir a Reportes > KPIs
2. Seleccionar período
3. Revisar alertas
4. Analizar tendencias
5. Comparar con período anterior
```

### Exportar Datos

**Formatos soportados**: CSV

**Características**:
- Encoding UTF-8 con BOM
- Headers descriptivos
- Datos separados por comas
- Importable en Excel

**Ejemplo**:
```bash
GET /reportes/exportarCSV?tipo=facturas&fecha_inicio=2024-01-01&fecha_fin=2024-01-31
```

---

## 🔍 Troubleshooting

### Problema: Gráficos no aparecen

**Causa**: Chart.js CDN no cargado

**Solución**:
```php
// Verificar en vista: Chart.js está incluido
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
```

### Problema: Datos vacíos en reportes

**Causa**: Período sin datos

**Solución**:
1. Verificar rango de fechas
2. Crear facturas de prueba en PASO 4
3. Verificar estado de facturas

### Problema: Alertas no aparecen

**Causa**: Condiciones no cumplidas

**Solución**:
- Alerta Tasa cobranza: Esperar que baje de 70%
- Alerta Saldo alto: Esperar que supere 30% de ingresos
- Alerta Días pago: Esperar que supere 30 días
- Alerta Disminución: Crear facturas en período anterior

### Problema: Paginación no funciona

**Causa**: Variable `pagina` no asignada

**Solución**:
```php
// En controlador
$pagina = $_GET['pagina'] ?? 1;
$pagina = max(1, (int)$pagina);
```

### Problema: Exportar CSV genera error

**Causa**: Headers ya enviados

**Solución**:
```php
// Colocar antes de cualquier output
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="reporte.csv"');
echo "\xEF\xBB\xBF"; // UTF-8 BOM
```

---

## 📞 Soporte

Para preguntas o problemas:

1. **Revisar logs**: `/storage/logs/`
2. **Contactar desarrollador**: admin@digisports.local
3. **Consultar documentación**: Ver `PASO_5_INDICE.md`

---

**Versión**: 1.0  
**Fecha**: 2024  
**Estado**: ✅ Producción
