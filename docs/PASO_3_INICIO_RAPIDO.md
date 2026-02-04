# PASO 3 - Sistema de Reservas: Inicio Rápido

## ✅ Qué se ha completado

### ReservaController.php (400+ líneas)
- ✅ `buscar()` - Búsqueda de disponibilidad con filtros
- ✅ `crear()` - Crear nueva reserva con validaciones
- ✅ `confirmacion()` - Mostrar resumen post-creación
- ✅ `index()` - Listar reservas paginadas
- ✅ `ver()` - Detalles completos de reserva
- ✅ `confirmar()` - Cambiar estado a CONFIRMADA
- ✅ `cancelar()` - Cambiar estado a CANCELADA
- ✅ `obtenerDisponibilidad()` - AJAX para frontend

### Vistas (4 templates)
- ✅ `reservas/buscar.php` - Búsqueda + modal de creación
- ✅ `reservas/confirmacion.php` - Resumen post-crear
- ✅ `reservas/index.php` - Listado paginado
- ✅ `reservas/ver.php` - Detalles de reserva

### Base de Datos: `paso_3_reservas.sql`
- ✅ Tabla `reservas` - 19 campos, estados workflow
- ✅ Tabla `reservas_lineas` - Detalles de franjas
- ✅ Tabla `confirmaciones` - Historial cambios de estado
- ✅ Tabla `bloqueos_disponibilidad` - Bloqueos manuales
- ✅ Tabla `historial_precios` - Auditoría tarifas
- ✅ Vista `vw_disponibilidad_por_dia` - Estadísticas
- ✅ Vista `vw_reservas_extendida` - Datos extendidos
- ✅ Vista `vw_ingresos_por_cancha` - Análisis financiero
- ✅ Índices optimizados para queries rápidas

---

## 🚀 Instalación

### 1. Importar tablas SQL

```bash
# Option 1: MySQL CLI
mysql -h localhost -u root -p digisports_core < database/paso_3_reservas.sql

# Option 2: PHPMyAdmin
- Abrir http://localhost/phpmyadmin
- Seleccionar BD: digisports_core
- Tab: Importar
- Seleccionar: database/paso_3_reservas.sql
- Clic en Continuar
```

### 2. Verificar instalación

```bash
# En browser:
http://localhost/digisports/public/index.php?m=reservas&c=reserva&a=buscar

# En BD: Verificar tablas
mysql> USE digisports_core;
mysql> SHOW TABLES LIKE 'reservas%';
```

---

## 📋 Flujo de Uso

### 1. Buscar Disponibilidad
```
GET /index.php?m=reservas&c=reserva&a=buscar
→ Seleccionar instalación + fecha
→ Ver canchas disponibles
→ Clic "Reservar" en franja deseada
```

### 2. Crear Reserva
```
Modal abre automáticamente
→ Llenar datos cliente (nombre, email, teléfono, cantidad)
→ Clic "Confirmar Reserva" (POST)
→ Redirige a confirmación
```

### 3. Ver Confirmación
```
GET /index.php?m=reservas&c=reserva&a=confirmacion?id=123
→ Muestra referencia RES-XXXXX
→ Resumen de cobro
→ Links: "Mis reservas" o "Nueva reserva"
```

### 4. Gestionar Reservas
```
GET /index.php?m=reservas&c=reserva&a=index
→ Ver todas mis reservas
→ Filtrar por estado
→ Ver detalles
→ Confirmar o cancelar
```

---

## 🔐 Características de Seguridad

✅ **Multi-tenant**: Todas las queries filtran por `tenant_id`
✅ **CSRF**: Tokens en todos los formularios POST
✅ **SQL Injection**: Prepared statements en 100% queries
✅ **Validación entrada**: Sanitización de datos cliente
✅ **Auditoría completa**: Tabla `confirmaciones` + logs
✅ **Estados workflow**: PENDIENTE → CONFIRMADA → COMPLETADA/CANCELADA
✅ **Detección conflictos**: Verifica mantenimientos + otras reservas
✅ **Soft delete**: Estados lógicos (no borra físicamente)

---

## 📊 Integración con PASO 2 (Instalaciones)

La lógica de reservas **depende completamente** de PASO 2:

1. **Canchas** - Se buscan disponibilidades de canchas
2. **Tarifas** - Se calcula precio desde tabla `tarifas`
3. **Mantenimientos** - Se detectan bloqueos automáticamente
4. **Disponibilidad** - Se consulta tabla `disponibilidad_canchas`

**Importante**: Asegurar que PASO 2 esté completamente funcionando antes de usar PASO 3.

---

## 🧪 Datos de Prueba

### Crear una reserva de prueba:

```bash
# 1. Asegurar que existan:
- Instalación (en tabla instalaciones)
- Cancha (en tabla canchas)
- Tarifas (en tabla tarifas con dia_semana correcto)

# 2. Hacer POST a:
POST /index.php?m=reservas&c=reserva&a=crear
Content-Type: application/x-www-form-urlencoded

cancha_id=1
&tarifa_id=1
&fecha_reserva=2024-03-15
&nombre_cliente=Juan+Perez
&email_cliente=juan@example.com
&telefono_cliente=0987654321
&cantidad_personas=5
&notas=Reserva+para+torneo
&csrf_token=TOKEN_AQUI

# 3. Response esperado:
{
  "success": true,
  "message": "Reserva creada. Pendiente de confirmación.",
  "redirect": "/digisports/public/index.php?m=reservas&c=reserva&a=confirmacion&id=1"
}
```

---

## 🐛 Debugging

### Errores comunes y soluciones

| Error | Causa | Solución |
|-------|-------|----------|
| "Reserva no encontrada" | `tenant_id` no coincide | Verificar sesión usuario |
| "Franja horaria ya reservada" | Conflicto de horario | Revisar `reservas` + `mantenimientos` |
| "Excede capacidad máxima" | `cantidad_personas` > capacidad | Bajar cantidad o elegir cancha más grande |
| "Cancha no válida" | `cancha_id` no existe o no pertenece al tenant | Verificar cancha en BD |
| "Token de seguridad inválido" | CSRF token expirado/inválido | Recargar página y reintentar |

### Queries útiles para debugging

```sql
-- Ver todas las reservas
SELECT * FROM reservas WHERE tenant_id = 1 ORDER BY fecha_creacion DESC;

-- Ver disponibilidad de una cancha
SELECT * FROM tarifas WHERE cancha_id = 1 AND estado = 'ACTIVO';

-- Ver conflictos de horario
SELECT * FROM reservas 
WHERE cancha_id = 1 AND DATE(fecha_reserva) = '2024-03-15' 
AND estado = 'CONFIRMADA';

-- Ver historial de cambios de estado
SELECT * FROM confirmaciones WHERE reserva_id = 1 ORDER BY fecha_creacion DESC;

-- Ver estadísticas por cancha
SELECT * FROM vw_ingresos_por_cancha WHERE cancha_id = 1;
```

---

## 📚 Documentación Completa

Ver: [docs/PASO_3_RESERVAS.md](PASO_3_RESERVAS.md) para documentación técnica detallada.

---

## 🔄 Próximos Pasos (PASO 4)

1. **FacturaController** - Genera facturas electrónicas
2. **Sistema de Pago** - Integración PayPhone, Datafast, etc.
3. **Notificaciones Email** - Confirmaciones, recordatorios
4. **Reportes** - Dashboards y analytics

---

**Versión**: 1.0.0  
**Estado**: ✅ Completado  
**Última actualización**: 2024
