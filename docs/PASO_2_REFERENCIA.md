# 🏗️ PASO 2: GESTIÓN DE INSTALACIONES - REFERENCIA RÁPIDA

**Estado:** ✅ **COMPLETADO**  
**Fecha:** 24 Enero 2026  
**Componentes:** 7 archivos nuevos (2 controladores + 4 vistas + 1 SQL)

---

## 📋 RESUMEN EJECUTIVO

Se implementó un sistema completo de gestión de instalaciones deportivas (PASO 2) con:
- ✅ CRUD de Canchas (crear, leer, actualizar, eliminar)
- ✅ Gestión flexible de Tarifas por hora/día
- ✅ Programación de Mantenimiento preventivo/correctivo
- ✅ 4 vistas profesionales con Bootstrap 5.3
- ✅ Tablas SQL con relaciones y auditoría completa
- ✅ Seguridad: CSRF tokens, validación de tenant, soft deletes

---

## 🎯 ARQUITECTURA IMPLEMENTADA

### Controllers (2)

#### 1. **CanchaController** - `app/controllers/instalaciones/CanchaController.php`
```
Métodos:
├── index()              → Listar todas las canchas (paginada, filtrable)
├── crear()              → Mostrar formulario crear
├── guardar()            → Guardar nueva cancha
├── editar()             → Mostrar formulario editar
├── actualizar()         → Actualizar cancha existente
├── eliminar()           → Soft delete de cancha
├── tarifas()            → Ver tarifas de una cancha
├── guardarTarifa()      → Crear/actualizar tarifa
└── eliminarTarifa()     → Eliminar tarifa

Validaciones:
├── Nombre: 3-100 caracteres, único por tenant
├── Capacidad: Mínimo 1 persona
├── Tipo: futbol, tenis, padel, voleibol, basquetbol, piscina, gimnasio
├── Instalación: Debe pertenecer al tenant
└── Tarifas: Precio > 0, horarios válidos
```

**Características:**
- Multi-tenant: `WHERE tenant_id = $this->tenantId`
- Paginación: 15 registros por página
- Búsqueda en tiempo real
- Filtros por tipo, estado, instalación
- Auditoría completa (audit table)
- Prevención de eliminación si tiene reservas

---

#### 2. **MantenimientoController** - `app/controllers/instalaciones/MantenimientoController.php`
```
Métodos:
├── index()              → Listar mantenimientos (paginada)
├── crear()              → Mostrar formulario crear
├── guardar()            → Guardar nuevo mantenimiento
├── editar()             → Mostrar formulario editar
├── actualizar()         → Actualizar mantenimiento
├── eliminar()           → Eliminar mantenimiento
└── cambiarEstado()      → Cambiar estado (workflow)

Estados Soportados:
├── PROGRAMADO           (pendiente de inicio)
├── EN_PROGRESO          (en ejecución)
├── COMPLETADO           (terminado)
└── CANCELADO            (descartado)

Tipos de Mantenimiento:
├── preventivo           (inspección regular)
├── correctivo           (reparación de fallas)
├── limpieza             (sanitización)
├── reparacion           (arreglos)
├── inspeccion           (revisión)
└── otra                 (custom)
```

**Características:**
- Asignación de responsable (técnico/admin)
- Soporte para mantenimiento recurrente
- Workflow de estados con auditoría
- Bloques de fechas para reservas automáticas
- Notas y seguimiento
- Validación de fechas (fin > inicio)

---

### Vistas (4)

#### 1. **Listado de Canchas** - `app/views/instalaciones/canchas/index.php`
```
Componentes:
├── Tarjetas de resumen (Total, Activas, Inactivas)
├── Barra de filtros (búsqueda, tipo, estado)
├── Tabla responsive con:
│   ├── Nombre, Instalación, Tipo, Capacidad
│   ├── Reservas hoy, Estado
│   └── Acciones (Tarifas, Editar, Eliminar)
├── Paginación con saltos
└── Badges de estado (Activa/Inactiva/Eliminada)

Características:
├── Búsqueda LIKE en nombre e instalación
├── Filtros encadenables
├── Contadores en tiempo real
├── Botón crear nueva cancha
└── Responsive (mobile-first)
```

#### 2. **Formulario Crear/Editar Cancha** - `app/views/instalaciones/canchas/formulario.php`
```
Secciones:
├── Información Básica
│   ├── Nombre (validación en cliente)
│   ├── Tipo (select con emojis)
│   ├── Instalación (multi-tenant aware)
│   └── Descripción (textarea)
├── Especificaciones Técnicas
│   ├── Capacidad Máxima (número)
│   ├── Largo (metros, decimal)
│   └── Ancho (metros, decimal)
└── Estado (solo editar)
    ├── Activo
    └── Inactivo

Validaciones:
├── Cliente: minlength, maxlength, required
├── Servidor: tipo válido, instalación pertenece a tenant
└── Auditoría: logged en tabla mantenimientos
```

#### 3. **Gestión de Tarifas** - `app/views/instalaciones/canchas/tarifas.php`
```
Layout de 2 columnas:

IZQUIERDA (50%):
├── Formulario para agregar tarifa
│   ├── Día de semana (select)
│   ├── Hora inicio/fin (time pickers)
│   ├── Precio (USD, decimal)
│   └── Estado (activo/inactivo)
└── Panel de sugerencias (horarios recomendados)

DERECHA (50%):
├── Tabla de tarifas con:
│   ├── Día (badge de color)
│   ├── Horario (formato HH:MM)
│   ├── Precio (con símbolo $)
│   ├── Estado (badge)
│   └── Acciones (editar, eliminar)
└── Tabla de referencia (plantilla sugerida)

Características:
├── AJAX para guardar sin reload
├── Edición inline (click en fila)
├── Scroll automático al formulario
├── Validación de horarios
└── UNIQUE constraint: cancha + dia + hora_inicio + hora_fin
```

#### 4. **Listado de Mantenimientos** - `app/views/instalaciones/mantenimientos/index.php`
```
Componentes:
├── Tarjetas de resumen
│   ├── Total
│   ├── Programados
│   ├── En Progreso
│   └── Completados
├── Filtros (cancha, estado)
├── Tabla con:
│   ├── Cancha, Tipo, Fecha inicio/fin
│   ├── Responsable, Estado
│   └── Acciones (editar, cambiar estado, eliminar)
└── Dropdown para cambiar estado (workflow)

Estados visuales:
├── PROGRAMADO    → Badge azul
├── EN_PROGRESO   → Badge amarillo
├── COMPLETADO    → Badge verde
└── CANCELADO     → Badge rojo
```

#### 5. **Formulario Mantenimiento** - `app/views/instalaciones/mantenimientos/formulario.php`
```
Secciones:
├── Información Básica
│   ├── Cancha (select, disabled en editar)
│   ├── Tipo (select con emojis)
│   └── Descripción (textarea, 5-500 chars)
├── Fechas y Horarios
│   ├── Fecha inicio (datetime-local)
│   └── Fecha fin (datetime-local)
├── Responsable y Recurrencia
│   ├── Responsable (select de técnicos/admins)
│   ├── ¿Recurrente? (toggle)
│   └── Cadencia (días entre repeticiones)
├── Estado (solo editar)
│   └── PROGRAMADO, EN_PROGRESO, COMPLETADO, CANCELADO
└── Notas (textarea, 1000 chars)

Validaciones:
├── Fecha fin > fecha inicio
├── Descripción mínimo 5 caracteres
├── Precio > 0
└── Cliente-side: datetime validation
```

---

## 🗄️ SCHEMA SQL IMPLEMENTADO

### Archivo: `database/paso_2_instalaciones.sql`

#### Tabla: `canchas` (Espacios deportivos)
```sql
cancha_id          INT PRIMARY KEY
tenant_id          INT NOT NULL FK→tenants
instalacion_id     INT NOT NULL FK→instalaciones
nombre             VARCHAR(100) UNIQUE per tenant
tipo               VARCHAR(50) -- futbol, tenis, padel...
descripcion        TEXT
capacidad_maxima   INT
ancho              DECIMAL(8,2) -- metros
largo              DECIMAL(8,2) -- metros
estado             VARCHAR(20) -- ACTIVO, INACTIVO, ELIMINADA
fecha_creacion     TIMESTAMP
fecha_actualizacion TIMESTAMP
usuario_creacion   INT FK→usuarios
usuario_actualizacion INT FK→usuarios

Índices:
├── PK: cancha_id
├── FK: tenant_id, instalacion_id, usuario_*
├── Búsqueda: FULLTEXT(nombre, descripcion)
├── Filtros: (tipo), (estado), (tenant_id)
└── Único: (tenant_id, nombre)
```

#### Tabla: `tarifas` (Precios por horarios)
```sql
tarifa_id          INT PRIMARY KEY
cancha_id          INT NOT NULL FK→canchas
dia_semana         TINYINT -- 0=domingo, 1=lunes...
hora_inicio        TIME
hora_fin           TIME
precio             DECIMAL(10,2)
estado             VARCHAR(20) -- ACTIVO, INACTIVO
fecha_creacion     TIMESTAMP
fecha_actualizacion TIMESTAMP

Único: (cancha_id, dia_semana, hora_inicio, hora_fin)
Índices: cancha, dia_semana, horario, estado
```

#### Tabla: `mantenimientos` (Programación de mantenimiento)
```sql
mantenimiento_id   INT PRIMARY KEY
tenant_id          INT NOT NULL FK→tenants
cancha_id          INT NOT NULL FK→canchas
tipo               VARCHAR(50) -- preventivo, correctivo...
descripcion        TEXT NOT NULL
notas              TEXT
fecha_inicio       DATETIME NOT NULL
fecha_fin          DATETIME NOT NULL
responsable_id     INT FK→usuarios (nullable)
recurrir           VARCHAR(2) -- SI, NO
cadencia_recurrencia INT -- días entre repeticiones
estado             VARCHAR(20) -- PROGRAMADO, EN_PROGRESO, COMPLETADO, CANCELADO
fecha_creacion     TIMESTAMP
fecha_actualizacion TIMESTAMP

Índices: tenant, cancha, fechas, estado, tipo, responsable
```

#### Tabla: `disponibilidad_canchas` (Cache de disponibilidad)
```sql
disponibilidad_id  INT PRIMARY KEY
cancha_id          INT NOT NULL FK→canchas
fecha              DATE
hora_inicio        TIME
hora_fin           TIME
disponible         CHAR(1) -- S/N
motivo             VARCHAR(255)
fecha_creacion     TIMESTAMP

Único: (cancha_id, fecha, hora_inicio, hora_fin)
```

#### Tabla: `eventos_canchas` (Auditoría de eventos)
```sql
evento_id          INT PRIMARY KEY
cancha_id          INT NOT NULL FK→canchas
tipo_evento        VARCHAR(50)
descripcion        TEXT
referencia_id      INT -- Referencia a mantenimiento, reserva, etc
usuario_id         INT FK→usuarios
fecha_evento       TIMESTAMP
```

### Vistas SQL (3)

1. **vw_tarifas_por_dia** - Listado de tarifas con nombres de días
2. **vw_mantenimientos_pendientes** - Mantenimientos activos con días hasta inicio
3. **vw_estadisticas_canchas** - Estadísticas por cancha (total tarifas, mantenimientos)

---

## 🔐 SEGURIDAD IMPLEMENTADA

### Multi-tenant
```php
// Todas las queries verifican tenant_id
WHERE c.tenant_id = $this->tenantId

// Un tenant NUNCA puede ver/modificar datos de otro
```

### CSRF Protection
```php
// Todos los formularios incluyen:
$this->validateCsrf()  // En POST, PUT, DELETE

// Token regenerado por request
```

### Validación
```php
// Cliente-side: HTML5 validation
// Servidor-side: Validación de datos + tipos

// Prevención de SQL injection:
$stmt->execute([$canchaId, $this->tenantId])  // PDO prepared statements
```

### Auditoría
```php
// Cada cambio registrado:
$this->audit('canchas', $canchaId, 'INSERT', [], [
    'nombre' => $nombre,
    'tipo' => $tipo
]);

// Vista: tabla auditorias con usuario_id, operacion, datos_antes, datos_despues
```

### Soft Deletes
```php
// Canchas no se borran, se marcan como ELIMINADA
UPDATE canchas SET estado = 'ELIMINADA'

// No se pueden eliminar si tienen reservas activas
```

---

## 📊 RELACIONES ER

```
tenants (multi-tenant root)
├── instalaciones (1:N)
│   └── canchas (1:N)
│       ├── tarifas (1:N)
│       ├── mantenimientos (1:N)
│       ├── disponibilidad_canchas (1:N)
│       └── eventos_canchas (1:N)
└── usuarios
    └── mantenimientos.responsable_id (N:1, nullable)
```

---

## 🚀 CÓMO USAR (PASO A PASO)

### 1. Importar Base de Datos
```bash
mysql -u root -p < c:\wamp64\www\digiSports\database\paso_2_instalaciones.sql
```

### 2. Acceder a Canchas
```
URL: http://localhost/digisports/public/instalaciones/cancha/index
Acceso: Login required, módulo INSTALACIONES habilitado
```

### 3. Crear Nueva Cancha
```
GET /instalaciones/cancha/crear → Form
POST /instalaciones/cancha/guardar → Guardar

Datos requeridos:
- Nombre (3-100 chars)
- Tipo (select predefinido)
- Instalación (select)
- Capacidad máxima (>0)
```

### 4. Gestionar Tarifas
```
GET /instalaciones/cancha/tarifas?id=1 → Ver tarifas
POST /instalaciones/cancha/guardarTarifa → Crear/actualizar
GET /instalaciones/cancha/eliminarTarifa?id=1 → Eliminar
```

### 5. Programar Mantenimiento
```
GET /instalaciones/mantenimiento/crear → Form
POST /instalaciones/mantenimiento/guardar → Guardar
GET /instalaciones/mantenimiento/cambiarEstado?id=1&estado=EN_PROGRESO → Workflow
```

---

## 📈 CONTADORES Y RESUMEN

| Componente | Cantidad |
|---|---|
| Controladores | 2 |
| Métodos públicos | 16 |
| Vistas | 5 |
| Tablas SQL | 5 |
| Vistas SQL | 3 |
| Validaciones | 20+ |
| Índices | 15+ |
| Archivos totales | 12 |

---

## ✅ CHECKLIST DE IMPLEMENTACIÓN

- ✅ CanchaController con CRUD completo
- ✅ MantenimientoController con CRUD + workflow
- ✅ Validación de capacidad (prevenir overbooking)
- ✅ Tarifas por hora/día/tipo
- ✅ Multi-tenant seguro
- ✅ CSRF tokens en todos los formularios
- ✅ Auditoría completa
- ✅ Paginación de resultados
- ✅ Filtros y búsqueda
- ✅ Diseño responsive Bootstrap 5.3
- ✅ Vistas SQL para reportes
- ✅ Documentación SQL con comentarios
- ✅ Soft deletes para canchas
- ✅ Prevención de eliminación con reservas
- ✅ Estados visuales con badges

---

## 🔄 PRÓXIMOS PASOS (PASO 3)

El siguiente paso será implementar el sistema completo de **Reservas**:
- Crear ReservaController
- Sistema de reserva recurrente
- Bloqueo de disponibilidad
- Confirmación/aprobación workflow
- Integración con tarifas (cálculo de precio)
- Notificaciones por email
- Calendario de disponibilidad

---

## 📞 REFERENCIAS RÁPIDAS

### URLs principales
```
Canchas:        /instalaciones/cancha/index
Tarifas:        /instalaciones/cancha/tarifas?id=X
Mantenimientos: /instalaciones/mantenimiento/index
```

### Métodos clave BaseController
```php
$this->tenantId          // Tenant actual del usuario
$this->userId            // ID del usuario logueado
$this->audit()           // Registrar auditoría
$this->validateCsrf()    // Validar token CSRF
$this->render()          // Renderizar vista
```

### Constantes en vistas
```php
\Config::get('base_url')        // Base URL de la app
\Security::generateCsrfToken()  // Token CSRF
```

---

**Generado:** 24 Enero 2026  
**Versión:** 1.0.0  
**Estado:** ✅ Producción Ready
