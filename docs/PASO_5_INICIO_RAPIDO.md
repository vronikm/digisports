# 🚀 PASO 5: Inicio Rápido

## ✅ Verificación Pre-requisitos

Antes de comenzar, asegúrate que tengas:

```php
✓ PHP 8.2.13+
✓ MySQL 8.0+
✓ PASO 4 (Facturación) completado
✓ Bootstrap 5.3
✓ Chart.js 3.9.1
```

---

## 📦 Estructura de Archivos Creados

```
PASO_5/
├── app/
│   ├── controllers/
│   │   └── reportes/
│   │       ├── ReporteController.php          (350 líneas)
│   │       └── KPIController.php              (400 líneas)
│   └── views/
│       └── reportes/
│           ├── index.php                      (200 líneas)
│           ├── facturas.php                   (180 líneas)
│           ├── ingresos.php                   (200 líneas)
│           ├── clientes.php                   (220 líneas)
│           └── kpi.php                        (300 líneas)
```

---

## 🔗 Rutas de Acceso

### Dashboard Principal
```
GET /reportes/index
```

### KPI Dashboard
```
GET /reportes/kpi?periodo=mes
GET /reportes/kpi?periodo=semana
GET /reportes/kpi?periodo=trimestre
GET /reportes/kpi?periodo=año
```

### Reportes
```
GET /reportes/facturas
GET /reportes/ingresos
GET /reportes/clientes
```

### Exportar
```
GET /reportes/exportarCSV?tipo=facturas
GET /reportes/exportarCSV?tipo=ingresos
GET /reportes/exportarCSV?tipo=clientes
```

---

## 🎯 Primeros Pasos

### 1. Acceder al Dashboard
```
1. Abrir navegador
2. Ir a http://localhost/digiSports
3. Hacer login
4. Clic en "Reportes" > "Dashboard"
```

### 2. Ver KPIs
```
1. Ir a http://localhost/digiSports/reportes/kpi
2. Seleccionar período: Semana/Mes/Trimestre/Año
3. Revisar alertas
4. Comparar con período anterior
```

### 3. Generar Reporte
```
1. Ir a Reportes > [tipo de reporte]
2. Seleccionar filtros
3. Clic "Filtrar"
4. Descargar CSV (opcional)
```

---

## 📊 KPIs Principales

| KPI | Descripción | Valor Ejemplo |
|-----|-------------|---------------|
| **Total Ingresos** | Suma de pagos | $50,000 |
| **Facturas** | Num. facturas | 25 |
| **Tasa Cobranza** | % pagado | 75% |
| **Saldo Pendiente** | Monto adeudado | $12,500 |
| **Promedio Factura** | Monto medio | $2,000 |
| **Clientes Únicos** | Num. clientes | 15 |
| **Días Pago** | Promedio | 18 días |
| **Facturas Pagadas** | Num. pagadas | 19 |

---

## 📈 Gráficos Disponibles

### Dashboard Principal
1. **Line Chart** - Ingresos por día (últimos 7 días)
2. **Doughnut Chart** - Ingresos por forma de pago
3. **Pie Chart** - Facturas por estado

### Dashboard KPIs
1. **Line Chart** - Evolución de ingresos (período completo)

---

## ⚠️ Alertas Automáticas

El sistema genera alertas en 4 casos:

### 🔴 Alerta Crítica
```
❌ Tasa Cobranza < 70%
❌ Saldo Pendiente > 30% de ingresos
```

### 🟡 Alerta Advertencia
```
⚠️ Días Promedio Pago > 30
⚠️ Disminución ingresos > 20% vs. anterior
```

---

## 🛠️ Customización

### Cambiar Período por Defecto

En `ReporteController.php`:
```php
// Línea ~50
$fecha_inicio = date('Y-m-d', strtotime('-7 days'));
$fecha_fin = date('Y-m-d');

// Cambiar a:
$fecha_inicio = date('Y-m-d', strtotime('first day of this month'));
$fecha_fin = date('Y-m-d');
```

### Agregar Filtro Adicional

En vista (ej: facturas.php):
```php
<div class="col-md-3">
    <label class="form-label">Nuevo Filtro</label>
    <select name="nuevo_filtro" class="form-select">
        <option value="">-- Seleccionar --</option>
    </select>
</div>
```

En `ReporteController::facturas()`:
```php
$nuevoFiltro = $_GET['nuevo_filtro'] ?? '';
if ($nuevoFiltro) {
    $where .= " AND campo = ?";
    $params[] = $nuevoFiltro;
}
```

---

## 📊 Ejemplo: Flujo Completo

```
1. Usuario login
   ↓
2. Navega a Reportes
   ↓
3. Selecciona "Dashboard"
   ↓
4. Sistema carga ReporteController::index()
   ↓
5. Controller llama helper obtenerKPIs()
   ↓
6. KPIs se pasan a vista index.php
   ↓
7. Gráficos Chart.js se renderizan
   ↓
8. Usuario ve dashboard completo
   ↓
9. Clic en "Este Mes"
   ↓
10. Se recarga con período nuevo
```

---

## 🔐 Seguridad

Todas las vistas incluyen:

✅ Multi-tenant filtering (WHERE tenant_id = ?)  
✅ Prepared statements (PDO)  
✅ HTML escaping (htmlspecialchars)  
✅ CSRF protection  
✅ Audit logging

---

## 💾 Optimización

Para mejor rendimiento:

### Agregar Índices
```sql
CREATE INDEX idx_facturas_tenant_fecha 
  ON facturas(tenant_id, fecha_emision);

CREATE INDEX idx_pagos_tenant_fecha 
  ON pagos(tenant_id, fecha_pago);

CREATE INDEX idx_facturas_estado 
  ON facturas(estado);
```

### Caché de KPIs
```php
// En KPIController
$cache_key = "kpis_{$periodo}_{$tenant_id}";
$kpis = cache()->get($cache_key);

if (!$kpis) {
    $kpis = $this->calcularKPIs();
    cache()->set($cache_key, $kpis, 3600); // 1 hora
}
```

---

## 📋 Checklist de Validación

Antes de ir a producción:

- [ ] Todos los KPIs calculan correctamente
- [ ] Gráficos muestran datos reales
- [ ] Filtros funcionan en todos los reportes
- [ ] Exportación CSV funciona
- [ ] Alertas se generan correctamente
- [ ] Paginación funciona
- [ ] Responsive design OK
- [ ] Multi-tenant filtering OK
- [ ] Logs se escriben correctamente
- [ ] Performance acceptable

---

## 📞 Soporte Rápido

| Problema | Solución |
|----------|----------|
| Gráficos en blanco | Cargar Chart.js CDN |
| Sin datos | Crear facturas en PASO 4 |
| Error 500 | Revisar logs en `/storage/logs/` |
| Alertas no muestran | Revisar condiciones de alerta |
| Export error | Verificar headers en controlador |

---

**🎉 ¡PASO 5 está listo para usar!**

Para más detalles, ver: `PASO_5_REPORTES.md`
