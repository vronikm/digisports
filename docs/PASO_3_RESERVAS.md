# PASO 3: Sistema de Reservas - Documentación Técnica

## 📋 Índice
1. [Descripción General](#descripción-general)
2. [Arquitectura](#arquitectura)
3. [Estructura de Tablas](#estructura-de-tablas)
4. [Controlador: ReservaController](#controlador-reservacontroller)
5. [Vistas](#vistas)
6. [Flujo de Reserva](#flujo-de-reserva)
7. [APIs y Endpoints](#apis-y-endpoints)
8. [Validaciones](#validaciones)
9. [Auditoría y Seguridad](#auditoría-y-seguridad)
10. [Importación de Base de Datos](#importación-de-base-de-datos)

---

## Descripción General

El **Sistema de Reservas** permite:
- ✅ Búsqueda de disponibilidad en tiempo real
- ✅ Creación de reservas con cálculo automático de precios
- ✅ Integración con tarifas por hora/día
- ✅ Detección de conflictos (mantenimientos, otras reservas)
- ✅ Workflow de confirmación
- ✅ Cancelación con auditoría
- ✅ Multi-tenant con aislamiento completo

**Estado**: Disponibilidad integrada + CRUD + Confirmación

---

## Arquitectura

### Componentes Principales

```
ReservaController (5 métodos públicos)
├── buscar()                      # Búsqueda de disponibilidad (GET)
├── crear()                       # Crear nueva reserva (POST)
├── confirmacion()                # Ver confirmación después de crear
├── index()                       # Listar reservas del usuario
├── ver()                         # Detalles de una reserva
├── confirmar()                   # Cambiar estado a CONFIRMADA
├── cancelar()                    # Cambiar estado a CANCELADA
└── obtenerDisponibilidad()      # AJAX JSON para frontend
```

### Flujo de Datos

```
Cliente llega a buscar.php
    ↓
[ReservaController::buscar()]
    ├─ Obtiene instalaciones
    ├─ Obtiene tipos de cancha
    └─ Si fecha + instalación: Calcula disponibilidad
         ├─ Obtiene tarifas del día
         ├─ Obtiene reservas confirmadas
         ├─ Obtiene mantenimientos
         └─ Calcula franjas disponibles
    ↓
Cliente selecciona franja → Modal formulario_reserva.php
    ↓
[ReservaController::crear()] (POST)
    ├─ Valida datos cliente
    ├─ Verifica conflictos de horario
    ├─ INSERT reservas
    ├─ INSERT reservas_lineas
    ├─ Audita cambios
    └─ Redirige a confirmacion.php
    ↓
[ReservaController::confirmacion()] (GET)
    └─ Muestra resumen + botones de acción
```

---

## Estructura de Tablas

### Tabla: `reservas`

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `reserva_id` | INT UNSIGNED PK | ID único |
| `tenant_id` | INT UNSIGNED FK | Multi-tenant |
| `cancha_id` | INT UNSIGNED FK | Referencia a cancha |
| `usuario_id` | INT UNSIGNED FK | Usuario que realizó reserva |
| `referencia` | VARCHAR(20) UNIQUE | Código RES-XXXXX |
| `nombre_cliente` | VARCHAR(100) | Nombre persona |
| `email_cliente` | VARCHAR(100) | Email de contacto |
| `telefono_cliente` | VARCHAR(20) | Teléfono opcional |
| `cantidad_personas` | INT UNSIGNED | Número de personas |
| `fecha_reserva` | DATETIME | Inicio de reserva |
| `fecha_fin_reserva` | DATETIME | Fin de reserva |
| `precio_total` | DECIMAL(10,2) | Precio calculado |
| `motivo_cancelacion` | TEXT | Si estado=CANCELADA |
| `notas` | TEXT | Observaciones |
| `estado` | ENUM | PENDIENTE_CONFIRMACION\|CONFIRMADA\|COMPLETADA\|CANCELADA |
| `fecha_confirmacion` | DATETIME | Cuándo se confirmó |
| `fecha_cancelacion` | DATETIME | Cuándo se canceló |
| `fecha_creacion` | DATETIME | Timestamp |
| `fecha_actualizacion` | DATETIME | ON UPDATE CURRENT_TIMESTAMP |

**Índices**:
- `idx_tenant_id` - Filtro multi-tenant
- `idx_estado` - Búsqueda por estado
- `idx_fecha_reserva` - Ordenamiento y búsqueda de rango
- `ft_nombre_cliente` - Búsqueda full-text
- `UNIQUE referencia` - Garantiza código único

### Tabla: `reservas_lineas`

Detalle de cada franja horaria en una reserva.

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `linea_id` | INT UNSIGNED PK | ID |
| `reserva_id` | INT UNSIGNED FK | Referencia reserva |
| `tarifa_id` | INT UNSIGNED FK | Referencia tarifa |
| `cantidad` | INT | Cantidad de franjas |
| `precio_unitario` | DECIMAL(10,2) | Precio de tarifa |
| `precio_total` | DECIMAL(10,2) | Subtotal línea |

**Restricción**: `ON DELETE CASCADE` - Si se elimina reserva, se elimina línea

### Tabla: `confirmaciones`

Historial de cambios de estado de reservas.

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `confirmacion_id` | INT UNSIGNED PK | ID |
| `reserva_id` | INT UNSIGNED FK | Referencia reserva |
| `usuario_confirma_id` | INT UNSIGNED FK | Quién cambió estado |
| `estado_anterior` | ENUM | Estado previo |
| `estado_nuevo` | ENUM | Estado nuevo |
| `observaciones` | TEXT | Razón del cambio |
| `ip_address` | VARCHAR(45) | IP del cambio |
| `user_agent` | TEXT | Navegador/cliente |
| `fecha_creacion` | DATETIME | Cuándo cambió |

### Tabla: `bloqueos_disponibilidad`

Bloqueos manuales de disponibilidad (además de mantenimientos).

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `bloqueo_id` | INT UNSIGNED PK | ID |
| `tenant_id` | INT UNSIGNED FK | Multi-tenant |
| `cancha_id` | INT UNSIGNED FK | Cancha bloqueada |
| `fecha_inicio` | DATETIME | Inicio bloqueo |
| `fecha_fin` | DATETIME | Fin bloqueo |
| `razon` | VARCHAR(100) | Razón del bloqueo |
| `creado_por` | INT UNSIGNED FK | Usuario que bloqueó |
| `fecha_creacion` | DATETIME | Timestamp |

### Tabla: `historial_precios`

Auditoría de cambios en tarifas.

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `historial_id` | INT UNSIGNED PK | ID |
| `tarifa_id` | INT UNSIGNED FK | Tarifa modificada |
| `precio_anterior` | DECIMAL(10,2) | Precio viejo |
| `precio_nuevo` | DECIMAL(10,2) | Precio nuevo |
| `usuario_id` | INT UNSIGNED FK | Quién cambió |
| `fecha_cambio` | DATETIME | Cuándo cambió |

---

## Controlador: ReservaController

**Ubicación**: `app/controllers/reservas/ReservaController.php`

### Métodos Públicos

#### 1. `buscar()`
**Tipo**: GET  
**Propósito**: Mostrar formulario de búsqueda de disponibilidad

```php
// Parámetros GET (opcional):
- fecha         (YYYY-MM-DD) - Defecto: hoy
- tipo          (string)     - Filtro tipo cancha
- instalacion_id (int)        - Filtro instalación

// Response:
- Renderiza: reservas/buscar.php
- Variables:
  * fecha
  * tipo_cancha
  * instalacion_id
  * instalaciones[]
  * tipos[]
  * disponibilidades[] (si fecha + instalacion_id)
  * csrf_token
```

#### 2. `crear()`
**Tipo**: POST  
**Propósito**: Crear nueva reserva

```php
// POST Parameters (required):
- cancha_id            (int)      ✓
- tarifa_id            (int)      ✓
- fecha_reserva        (YYYY-MM-DD) ✓
- nombre_cliente       (string)   ✓ min:3, max:100
- email_cliente        (email)    ✓
- telefono_cliente     (string)   opcional
- cantidad_personas    (int)      ✓ min:1
- notas                (string)   opcional
- csrf_token           (string)   ✓

// Validaciones:
✓ Validar CSRF
✓ Validar cancha pertenece al tenant
✓ Validar capacidad >= cantidad_personas
✓ Verificar no existe otra reserva en ese horario
✓ Verificar no hay mantenimiento que bloquee

// Response:
- Success: JSON { redirect: ".../confirmacion?id=X" }
- Error: JSON { error: "mensaje" }
```

#### 3. `confirmacion()`
**Tipo**: GET  
**Propósito**: Mostrar resumen después de crear

```php
// Parámetros GET:
- id (int) - reserva_id

// Response:
- Renderiza: reservas/confirmacion.php
- Variables:
  * reserva (con detalles extendidos)
  * lineas[] (reservas_lineas)
  * csrf_token
```

#### 4. `index()`
**Tipo**: GET  
**Propósito**: Listar reservas del usuario/tenant

```php
// Parámetros GET:
- estado  (string) - Filtro estado
- pagina  (int)    - Número de página (defecto: 1)

// Paginación:
- 15 registros por página
- Índices optimizados para queries rápidas

// Response:
- Renderiza: reservas/index.php
- Variables:
  * reservas[] (paginadas)
  * totalRegistros
  * totalPaginas
  * estado
```

#### 5. `ver()`
**Tipo**: GET  
**Propósito**: Ver detalles completos de una reserva

```php
// Parámetros GET:
- id (int) - reserva_id

// Response:
- Renderiza: reservas/ver.php
- Variables:
  * reserva (detalles completos)
  * lineas[] (detalles de cobro)
```

#### 6. `confirmar()`
**Tipo**: GET  
**Propósito**: Cambiar estado a CONFIRMADA

```php
// Parámetros GET:
- id (int) - reserva_id

// Cambios:
- estado: PENDIENTE_CONFIRMACION → CONFIRMADA
- fecha_confirmacion = NOW()
- INSERT confirmaciones (historial)

// Response:
- JSON: { redirect: ".../index", message: "..." }
```

#### 7. `cancelar()`
**Tipo**: GET  
**Propósito**: Cambiar estado a CANCELADA

```php
// Parámetros GET:
- id      (int)    - reserva_id
- motivo  (string) - Razón cancelación

// Cambios:
- estado: * → CANCELADA
- motivo_cancelacion = motivo
- fecha_cancelacion = NOW()
- INSERT confirmaciones (historial)

// Response:
- JSON: { redirect: ".../index", message: "..." }
```

#### 8. `obtenerDisponibilidad()` (AJAX)
**Tipo**: GET  
**Propósito**: Obtener disponibilidad en JSON para frontend

```php
// Parámetros GET:
- cancha_id  (int)        - ✓
- fecha      (YYYY-MM-DD) - ✓

// Response: JSON array
[
  {
    "tarifa_id": 1,
    "hora_inicio": "08:00:00",
    "hora_fin": "09:00:00",
    "precio": 25.00,
    "disponible": true
  },
  ...
]
```

---

## Vistas

### 1. `reservas/buscar.php`

**Propósito**: Búsqueda y selección de disponibilidad

**Secciones**:
- **Filtros (izquierda)**:
  - Selector instalación (requerido)
  - Picker fecha (defecto: hoy, mín: hoy)
  - Selector tipo cancha (opcional)
  - Botón "Buscar"

- **Resultados (derecha)**:
  - Para cada cancha: Tarjeta con franjas horarias
  - Cada franja muestra:
    - Hora inicio/fin
    - Precio
    - Badge: "Disponible" (verde) o razón bloqueo (rojo)
    - Botón "Reservar" (solo si disponible)
  - Si no hay resultados: Alert informativo

- **Modal**: Abre al hacer clic en "Reservar"
  - Pre-rellena datos cancha, fecha, hora, precio
  - Formulario cliente: nombre, email, teléfono, cantidad, notas
  - POST a `ReservaController::crear()`

### 2. `reservas/confirmacion.php`

**Propósito**: Mostrar resumen después de crear reserva

**Secciones**:
- Alert de éxito
- Info-boxes: Estado, Cancha, Total
- Datos reserva: Fecha, hora, duración
- Datos cliente: Nombre, email, teléfono, cantidad
- Tabla de cobro: Franjas, precios, total
- Botones: "Mis reservas", "Nueva reserva"

### 3. `reservas/index.php`

**Propósito**: Listar todas las reservas del usuario

**Secciones**:
- Filtros: Estado, botones Filtrar y "Nueva"
- Tabla con columnas:
  - Referencia (bold)
  - Cliente (+ email)
  - Cancha (+ tipo)
  - Fecha
  - Hora (inicio-fin)
  - Personas
  - Precio
  - Estado (badge coloreado)
  - Acciones: Ver, Confirmar (si pendiente), Cancelar (si no completada)
- Paginación con navegación

### 4. `reservas/ver.php`

**Propósito**: Detalles completos de una reserva

**Secciones**:
- Info-boxes: Estado, Cancha, Total
- Datos reserva: Referencia, tipo, fecha, hora, duración
- Datos cliente: Nombre, email, teléfono, cantidad
- Tabla de cobro detallada
- Notas (si existen)
- Fechas auditoría: Creación, actualización
- Botones: Volver, Confirmar (si pendiente), Cancelar

---

## Flujo de Reserva

### Estado 1: PENDIENTE_CONFIRMACION (Inicial)

```
Cliente crea reserva
        ↓
INSERT reservas (estado=PENDIENTE_CONFIRMACION)
        ↓
Muestra confirmacion.php
        ↓
Cliente ve resumen y referencia
```

### Estado 2: CONFIRMADA

```
Admin o cliente hace clic "Confirmar"
        ↓
UPDATE reservas SET estado=CONFIRMADA, fecha_confirmacion=NOW()
        ↓
INSERT confirmaciones (historial)
        ↓
Reserva lista (puede haber pago después)
```

### Estado 3: COMPLETADA

```
Después de fecha_fin_reserva (manual o automático)
        ↓
UPDATE reservas SET estado=COMPLETADA
        ↓
Finaliza ciclo de reserva
```

### Estado 4: CANCELADA

```
Cliente cancela antes de completada
        ↓
UPDATE reservas SET estado=CANCELADA, fecha_cancelacion=NOW()
        ↓
INSERT confirmaciones (con motivo_cancelacion)
        ↓
Libera la franja horaria
```

---

## APIs y Endpoints

### Búsqueda
```
GET /digisports/public/index.php?m=reservas&c=reserva&a=buscar
GET /digisports/public/index.php?m=reservas&c=reserva&a=buscar&fecha=2024-03-15&instalacion_id=1
```

### Crear Reserva
```
POST /digisports/public/index.php?m=reservas&c=reserva&a=crear
Content-Type: application/x-www-form-urlencoded

cancha_id=1&tarifa_id=5&fecha_reserva=2024-03-15&nombre_cliente=Juan&email_cliente=juan@example.com&...
```

### Confirmación
```
GET /digisports/public/index.php?m=reservas&c=reserva&a=confirmacion?id=123
```

### Listar
```
GET /digisports/public/index.php?m=reservas&c=reserva&a=index
GET /digisports/public/index.php?m=reservas&c=reserva&a=index?estado=CONFIRMADA&pagina=1
```

### Ver Detalles
```
GET /digisports/public/index.php?m=reservas&c=reserva&a=ver?id=123
```

### Confirmar Reserva
```
GET /digisports/public/index.php?m=reservas&c=reserva&a=confirmar?id=123
```

### Cancelar Reserva
```
GET /digisports/public/index.php?m=reservas&c=reserva&a=cancelar?id=123&motivo=Cambio+de+planes
```

### AJAX: Disponibilidad
```
GET /digisports/public/index.php?m=reservas&c=reserva&a=obtenerDisponibilidad?cancha_id=1&fecha=2024-03-15
Response: JSON array de franjas
```

---

## Validaciones

### ReservaController::crear()

| Validación | Condición | Mensaje |
|------------|-----------|---------|
| cancha_id válida | cancha_id >= 1 | "Cancha no válida" |
| tarifa_id válida | tarifa_id >= 1 | "Tarifa no válida" |
| fecha_reserva | !empty(fecha_reserva) | "Fecha de reserva requerida" |
| nombre_cliente | len >= 3 && len <= 100 | "Nombre debe tener 3-100 caracteres" |
| email_cliente | filter_var(email) | "Email válido requerido" |
| cantidad_personas | cantidad > 0 | "Cantidad debe ser > 0" |
| cantidad_personas | cantidad <= capacidad_cancha | "Excede capacidad máxima" |
| conflicto_horario | NO hay otra reserva confirmada | "Franja horaria ya reservada" |
| tenant_id | Cancha pertenece al tenant | "Cancha no autorizada" |
| CSRF token | Valid token | "Token de seguridad inválido" |

### ReservaController::confirmar()

| Validación | Condición | Mensaje |
|------------|-----------|---------|
| reserva_id válida | reserva_id >= 1 | "Reserva no válida" |
| reserva_existe | Existe en BD | "Reserva no encontrada" |
| tenant_id | Reserva pertenece al tenant | "No autorizado" |

---

## Auditoría y Seguridad

### Seguridad Implementada

✅ **Multi-tenant**: Todos los queries filtran por `tenant_id`
✅ **CSRF**: Validación en POST mediante token
✅ **SQL Injection**: Prepared statements en todas las queries
✅ **Validación de entrada**: Sanitización de datos
✅ **Soft delete**: Lógico mediante estado
✅ **Auditoría completa**: Tablas `confirmaciones` y `historial_precios`

### Logs de Auditoría

#### Tabla: `confirmaciones`
Se registra cada cambio de estado:
- `reserva_id`
- `estado_anterior` → `estado_nuevo`
- `usuario_confirma_id`
- `ip_address`
- `user_agent`
- `fecha_creacion`

#### Security Log
Se registran eventos en `Security::logSecurityEvent()`:
- `RESERVA_CREATED` - Cuando se crea reserva
- `RESERVA_CONFIRMED` - Cuando se confirma
- `RESERVA_CANCELLED` - Cuando se cancela

---

## Importación de Base de Datos

### Paso 1: Preparar archivos SQL

```bash
# Archivos a importar en orden:
1. database/paso_2_instalaciones.sql   (si no existe)
2. database/paso_3_reservas.sql        (este)
```

### Paso 2: Importar con MySQL

```bash
# Opción A: Línea de comandos
mysql -h localhost -u root -p digisports_core < database/paso_3_reservas.sql

# Opción B: PHPMyAdmin
- Abrir PHPMyAdmin
- Seleccionar BD: digisports_core
- Ir a "Importar"
- Seleccionar archivo paso_3_reservas.sql
- Hacer clic en "Continuar"

# Opción C: CLI de MySQL
mysql> USE digisports_core;
mysql> SOURCE /ruta/a/database/paso_3_reservas.sql;
```

### Paso 3: Verificar tablas

```sql
-- Verificar tablas creadas
SHOW TABLES LIKE 'reservas%';
SHOW TABLES LIKE 'confirmaciones';
SHOW TABLES LIKE 'bloqueos%';
SHOW TABLES LIKE 'historial%';

-- Verificar vistas
SHOW VIEWS LIKE 'vw_%';

-- Verificar datos de prueba
SELECT COUNT(*) FROM reservas;
SELECT COUNT(*) FROM confirmaciones;
```

### Paso 4: Ejecutar migraciones (si aplica)

```php
// En BaseController o en controller de inicialización
$this->db->exec(file_get_contents(BASE_PATH . '/database/paso_3_reservas.sql'));
```

---

## 🚀 Próximos Pasos (PASO 4)

1. **Sistema de Facturación** (`FacturaController`)
   - Integración con tarifas
   - Cálculo de impuestos
   - SRI Ecuador

2. **Pasarelas de Pago**
   - PayPhone
   - Datafast
   - PlacetoPay
   - PayPal/Stripe

3. **Notificaciones Email**
   - Confirmación de reserva
   - Recordatorios
   - Cancelación

---

## 📞 Soporte y Debugging

### Errores Comunes

#### Error: "Reserva no encontrada"
- Verificar que `tenant_id` en sesión es correcto
- Verificar que `reserva_id` existe en BD
- Comprobar `WHERE tenant_id = ?` en query

#### Error: "Franja horaria ya reservada"
- Verificar tarifas del día existen
- Comprobar reservas confirmadas en BD
- Revisar mantenimientos que bloquean

#### Error: "Excede capacidad máxima"
- Verificar `capacidad_maxima` de cancha
- Confirmar que `cantidad_personas` <= capacidad

### Debugging

```php
// Activa logs detallados
error_log("DEBUG: Reserva creada: " . $reserva_id);

// Verifica disponibilidad
SELECT * FROM tarifas WHERE cancha_id = 1 AND dia_semana = 3;
SELECT * FROM reservas WHERE cancha_id = 1 AND estado = 'CONFIRMADA';
SELECT * FROM mantenimientos WHERE cancha_id = 1 AND estado IN ('PROGRAMADO', 'EN_PROGRESO');
```

---

**Versión**: 1.0.0  
**Última actualización**: 2024  
**Autor**: DigiSports Team  
**Licencia**: Propietaria
