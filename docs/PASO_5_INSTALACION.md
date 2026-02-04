# 📦 PASO 5: Guía de Instalación y Entrega

## 🚀 Instalación

### Pre-requisitos

```
✓ PHP 8.2.13+
✓ MySQL 8.0+
✓ Apache 2.4+
✓ PASO 4 (Facturación) instalado
✓ Bootstrap 5.3
✓ Font Awesome 6
```

### Paso 1: Copiar Archivos

```bash
# Copiar controladores
cp app/controllers/reportes/* /app/controllers/reportes/

# Copiar vistas
cp app/views/reportes/* /app/views/reportes/

# Copiar documentación
cp PASO_5_*.md /
```

### Paso 2: Configurar Rutas

En `config/Router.php`, agregar:

```php
// Rutas PASO 5
Router::get('/reportes', 'reportes', 'reporte', 'index');
Router::get('/reportes/index', 'reportes', 'reporte', 'index');
Router::get('/reportes/facturas', 'reportes', 'reporte', 'facturas');
Router::get('/reportes/ingresos', 'reportes', 'reporte', 'ingresos');
Router::get('/reportes/clientes', 'reportes', 'reporte', 'clientes');
Router::get('/reportes/exportarCSV', 'reportes', 'reporte', 'exportarCSV');

Router::get('/reportes/kpi', 'reportes', 'kpi', 'index');
```

### Paso 3: Crear Índices (Opcional pero Recomendado)

```sql
-- Ejecutar en MySQL
USE digisports_core;

-- Índices para mejorar performance de reportes
CREATE INDEX idx_facturas_tenant_fecha 
  ON facturas(tenant_id, fecha_emision);

CREATE INDEX idx_pagos_tenant_fecha 
  ON pagos(tenant_id, fecha_pago);

CREATE INDEX idx_facturas_estado 
  ON facturas(estado);

CREATE INDEX idx_pagos_factura 
  ON pagos(factura_id);
```

### Paso 4: Validar Instalación

```php
// Acceder a:
http://localhost/digiSports/reportes/index

// Debería ver:
✓ Dashboard con 4 KPI cards
✓ 3 Gráficos Chart.js
✓ Tabla de clientes
✓ Tabla de facturas
```

---

## ✅ Checklist de Instalación

- [ ] Archivos PHP copiados correctamente
- [ ] Rutas configuradas en Router.php
- [ ] Índices MySQL creados
- [ ] Carpeta `/storage/logs/` existe
- [ ] Permisos de escritura OK
- [ ] Dashboard carga sin errores
- [ ] KPIs muestran datos
- [ ] Gráficos renderizan
- [ ] Filtros funcionan
- [ ] Exportación CSV funciona

---

## 🧪 Validación Post-Instalación

### Test 1: Dashboard Carga
```
GET http://localhost/digiSports/reportes/index
Resultado esperado: 200 OK con 4 KPI cards visibles
```

### Test 2: KPI Dashboard
```
GET http://localhost/digiSports/reportes/kpi?periodo=mes
Resultado esperado: 8 KPIs con tendencias
```

### Test 3: Reporte Facturas
```
GET http://localhost/digiSports/reportes/facturas
Resultado esperado: Tabla de facturas con paginación
```

### Test 4: Exportar CSV
```
GET http://localhost/digiSports/reportes/exportarCSV?tipo=facturas
Resultado esperado: Descarga archivo .csv
```

---

## 📋 Archivos Entregados

### Controladores (2)
```
✓ app/controllers/reportes/ReporteController.php (350 líneas)
✓ app/controllers/reportes/KPIController.php (400 líneas)
```

### Vistas (5)
```
✓ app/views/reportes/index.php (200 líneas)
✓ app/views/reportes/facturas.php (180 líneas)
✓ app/views/reportes/ingresos.php (200 líneas)
✓ app/views/reportes/clientes.php (220 líneas)
✓ app/views/reportes/kpi.php (300 líneas)
```

### Documentación (4)
```
✓ PASO_5_REPORTES.md (Documentación técnica completa)
✓ PASO_5_INICIO_RAPIDO.md (Guía de inicio rápido)
✓ PASO_5_VALIDACION_EJECUTIVA.md (Resumen ejecutivo)
✓ PASO_5_INDICE.md (Índice de documentación)
✓ PASO_5_INSTALACION.md (Este archivo)
```

---

## 📊 Estadísticas

| Métrica | Valor |
|---------|-------|
| Líneas de código | 1,100+ |
| Controladores | 2 |
| Vistas | 5 |
| Métodos | 6 públicos + 9 privados |
| KPIs implementados | 8 |
| Gráficos | 3 tipos |
| Reportes | 3 + 1 KPI |
| Documentación | 5 archivos |

---

## 🔧 Configuración Adicional

### Agregar Logotipo a Reportes

En `app/views/reportes/index.php`, después de `<h1>`:

```php
<img src="<?= url_asset('img/logo.png') ?>" height="50" alt="digiSports">
```

### Cambiar Período por Defecto

En `ReporteController.php`, línea ~50:

```php
// Cambiar de:
$fecha_inicio = date('Y-m-d', strtotime('-7 days'));

// A:
$fecha_inicio = date('Y-m-01'); // Primer día del mes
```

### Agregar Filtro de Tenant

Si no está habilitado multi-tenant:

```php
// En ReporteController
$tenant_id = $_SESSION['tenant_id'] ?? 1;
$where .= " AND facturas.tenant_id = ?";
$params[] = $tenant_id;
```

---

## 🔒 Configuración de Seguridad

### Asegurar Acceso

En `app/controllers/reportes/ReporteController.php`, al inicio:

```php
// Verificar sesión
if (!isset($_SESSION['user_id'])) {
    redirect('auth', 'login');
}
```

### Audit Logging

Los controladores incluyen logging automático en:
```
/storage/logs/reportes_YYYY-MM-DD.log
```

---

## 📈 Optimización de Performance

### Caché Recomendado

Para empresas con muchos datos:

```php
// En KPIController::calcularKPIs()
$cache_key = "kpis_{$periodo}_{$tenant_id}";

// Leer de caché
if (file_exists("storage/cache/{$cache_key}.json")) {
    return json_decode(file_get_contents(...), true);
}

// Calcular
$kpis = /* cálculo */;

// Guardar en caché (1 hora)
file_put_contents("storage/cache/{$cache_key}.json", json_encode($kpis));
```

### Monitoreo

Monitorear estos archivos en logs:
```
/storage/logs/reportes_*.log
/storage/cache/*
```

---

## 🆘 Troubleshooting de Instalación

### Error: "Class not found ReporteController"
```
Solución: Verificar que archivo existe en:
app/controllers/reportes/ReporteController.php
```

### Error: "View not found"
```
Solución: Verificar que vistas existen en:
app/views/reportes/*.php
```

### Error: "Database connection error"
```
Solución: Verificar credenciales en:
config/database.php
```

### Error: "Permission denied on /storage/logs/"
```
Solución: Ejecutar:
chmod -R 755 storage/logs/
chmod -R 755 storage/cache/
```

### Chart.js no carga
```
Solución: Verificar CDN en vista:
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
```

---

## 📞 Soporte Post-Instalación

### Revisar Logs
```bash
# Ver últimos errores
tail -f /storage/logs/reportes_*.log

# Ver todos los logs
ls -la /storage/logs/
```

### Contactar Soporte
- Email: admin@digisports.local
- Teléfono: [Revisar config]
- Documentación: Ver PASO_5_INDICE.md

---

## 🎯 Próximos Pasos

### Después de Instalación
1. ✅ Validar que todos los archivos estén en lugar
2. ✅ Acceder al dashboard
3. ✅ Crear datos de prueba (si es necesario)
4. ✅ Validar cada reporte
5. ✅ Entrenar a usuarios

### Antes de Producción
1. ✅ Crear índices MySQL
2. ✅ Configurar backups automáticos
3. ✅ Revisar logs diarios
4. ✅ Monitorear performance
5. ✅ Establecer alertas

---

## 📊 Matriz de Compatibilidad

| Componente | Version | Requerida | Status |
|-----------|---------|-----------|--------|
| PHP | 8.2.13+ | 8.2+ | ✅ |
| MySQL | 8.0+ | 8.0+ | ✅ |
| Bootstrap | 5.3+ | 5.3+ | ✅ |
| Chart.js | 3.9.1 | 3.9+ | ✅ |
| Font Awesome | 6+ | 6+ | ✅ |

---

## ✨ Características Incluidas

```
✅ Dashboard Ejecutivo
✅ KPI Dashboard con 8 indicadores
✅ 3 tipos de gráficos interactivos
✅ 3 reportes detallados
✅ Alertas inteligentes
✅ Exportación CSV
✅ Filtros avanzados
✅ Paginación
✅ Multi-tenant
✅ Responsive design
✅ Audit logging
✅ Error handling
```

---

## 🎉 Instalación Completada

Una vez completado todos los pasos:

1. ✅ PASO 5 está funcional
2. ✅ Usuarios pueden acceder a reportes
3. ✅ Datos se actualizan automáticamente
4. ✅ Alertas funcionan
5. ✅ Sistema está listo para producción

---

**Versión**: 1.0  
**Fecha**: 2024  
**Estado**: ✅ Listo para Producción

Para más detalles, consultar:
- [PASO_5_REPORTES.md](PASO_5_REPORTES.md)
- [PASO_5_INICIO_RAPIDO.md](PASO_5_INICIO_RAPIDO.md)
- [PASO_5_INDICE.md](PASO_5_INDICE.md)
