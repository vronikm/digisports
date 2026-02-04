<?php
/**
 * PASO 2 - RUTAS Y CONFIGURACIÓN
 * Instrucciones para activar los controladores de Instalaciones
 * 
 * Este archivo documenta cómo el Router debe estar configurado
 * para que los nuevos controladores funcionen correctamente.
 */

// ===================================================================
// 📝 CONFIGURACIÓN DE RUTAS EN Router.php
// ===================================================================

/*
El archivo config/Router.php debe reconocer estas rutas:

1. CANCHAS
   ├── GET  /instalaciones/cancha/index       → CanchaController::index()
   ├── GET  /instalaciones/cancha/crear       → CanchaController::crear()
   ├── POST /instalaciones/cancha/guardar     → CanchaController::guardar()
   ├── GET  /instalaciones/cancha/editar      → CanchaController::editar()
   ├── POST /instalaciones/cancha/actualizar  → CanchaController::actualizar()
   ├── GET  /instalaciones/cancha/eliminar    → CanchaController::eliminar()
   ├── GET  /instalaciones/cancha/tarifas     → CanchaController::tarifas()
   ├── POST /instalaciones/cancha/guardarTarifa → CanchaController::guardarTarifa()
   └── GET  /instalaciones/cancha/eliminarTarifa → CanchaController::eliminarTarifa()

2. MANTENIMIENTOS
   ├── GET  /instalaciones/mantenimiento/index → MantenimientoController::index()
   ├── GET  /instalaciones/mantenimiento/crear → MantenimientoController::crear()
   ├── POST /instalaciones/mantenimiento/guardar → MantenimientoController::guardar()
   ├── GET  /instalaciones/mantenimiento/editar → MantenimientoController::editar()
   ├── POST /instalaciones/mantenimiento/actualizar → MantenimientoController::actualizar()
   ├── GET  /instalaciones/mantenimiento/eliminar → MantenimientoController::eliminar()
   └── GET  /instalaciones/mantenimiento/cambiarEstado → MantenimientoController::cambiarEstado()
*/

// ===================================================================
// 🔧 ESTRUCTURA DEL ROUTER (referencia)
// ===================================================================

/*
En config/Router.php, el patrón es:

$controller = $path[1];  // 'cancha' o 'mantenimiento'
$action = $path[2];      // 'index', 'crear', 'guardar', etc.

Luego:
$controllerClass = 'App\\Controllers\\' . ucfirst($module) . '\\' 
                   . ucfirst($controller) . 'Controller';

Es decir:
- 'instalaciones/cancha/index'
  → App\Controllers\Instalaciones\CanchaController::index()

- 'instalaciones/mantenimiento/guardar'
  → App\Controllers\Instalaciones\MantenimientoController::guardar()
*/

// ===================================================================
// 🔐 PERMISOS Y MÓDULOS
// ===================================================================

/*
Ambos controladores requieren:

1. Autenticación
   - Usuario debe estar logueado (session check)
   - $_SESSION['user_id'] debe existir
   - $_SESSION['tenant_id'] debe existir

2. Módulo habilitado
   - Tenant debe tener módulo 'INSTALACIONES' activo
   - Verificar en tabla: tenant_modulos

3. Rol
   - ADMIN: Acceso completo
   - GERENTE_INSTALACIONES: Acceso a lectura/escritura
   - TECNICO: Solo lectura y cambio de estado de mantenimientos
   - USUARIO: Solo lectura (si se implementa)
*/

// ===================================================================
// 📊 ESTRUCTURA DE DIRECTORIOS REQUERIDA
// ===================================================================

/*
app/controllers/instalaciones/
├── CanchaController.php ✓ CREADO
├── MantenimientoController.php ✓ CREADO
└── (futuro) ReservaController.php

app/views/instalaciones/
├── canchas/
│   ├── index.php ✓ CREADO (listado)
│   ├── formulario.php ✓ CREADO (crear/editar)
│   └── tarifas.php ✓ CREADO (gestión de tarifas)
├── mantenimientos/
│   ├── index.php ✓ CREADO (listado)
│   └── formulario.php ✓ CREADO (crear/editar)
└── (futuro) reservas/
    ├── index.php
    ├── formulario.php
    └── calendario.php
*/

// ===================================================================
// 🗄️ TABLAS DE BASE DE DATOS REQUERIDAS
// ===================================================================

/*
Las siguientes tablas deben existir ANTES de usar los controladores:

1. canchas
   - Creada: ✓ paso_2_instalaciones.sql
   - Relaciones: tenant_id, instalacion_id

2. tarifas
   - Creada: ✓ paso_2_instalaciones.sql
   - Relaciones: cancha_id

3. mantenimientos
   - Creada: ✓ paso_2_instalaciones.sql
   - Relaciones: tenant_id, cancha_id, responsable_id

4. disponibilidad_canchas
   - Creada: ✓ paso_2_instalaciones.sql
   - Relaciones: cancha_id

5. eventos_canchas
   - Creada: ✓ paso_2_instalaciones.sql
   - Relaciones: cancha_id, usuario_id

Importar con:
mysql -u root digisports_core < paso_2_instalaciones.sql
*/

// ===================================================================
// ⚙️ FUNCIONES DE AYUDA DISPONIBLES
// ===================================================================

/*
Los controladores utilizan estas funciones/clases:

1. url($module, $controller, $action, $params = [])
   Ejemplo: url('instalaciones', 'cancha', 'index')
   Retorna: /digisports/public/instalaciones/cancha/index

2. \Config::get($key)
   Ejemplo: \Config::get('base_url')
   Retorna: http://localhost/digisports/public/

3. \Security::generateCsrfToken()
   Genera token CSRF para formularios

4. \Security::validateCsrfToken($token)
   Valida token CSRF en POST

5. \Security::logSecurityEvent($evento, $descripcion)
   Registra evento de auditoría

6. BaseController métodos:
   - $this->render($view, $data)
   - $this->success($data, $message)
   - $this->error($message, $code)
   - $this->audit($tabla, $id, $operacion, $before, $after)
   - $this->isPost(), $this->get(), $this->post()
*/

// ===================================================================
// 🧪 PRUEBAS INICIALES
// ===================================================================

/*
Después de importar la BD y configurar las rutas:

1. Verificar que el módulo INSTALACIONES existe:
   - URL: /digisports/public/core/dashboard/index
   - Deberías ver "INSTALACIONES" en módulos

2. Probar listado de canchas:
   - URL: http://localhost/digisports/public/instalaciones/cancha/index
   - Deberías ver tabla vacía (0 canchas)

3. Crear cancha de prueba:
   - URL: http://localhost/digisports/public/instalaciones/cancha/crear
   - Rellenar form y guardar
   - Deberías ver cancha en el listado

4. Ver tarifas:
   - URL: http://localhost/digisports/public/instalaciones/cancha/tarifas?id=1
   - Deberías ver form para agregar tarifas

5. Programar mantenimiento:
   - URL: http://localhost/digisports/public/instalaciones/mantenimiento/crear
   - Deberías ver form para agendar mantenimiento
*/

// ===================================================================
// 📋 CHECKLIST PRE-PRODUCCIÓN
// ===================================================================

/*
ANTES de usar en producción, verificar:

Database:
- [ ] digisports_core importado
- [ ] paso_2_instalaciones.sql ejecutado
- [ ] Todas las tablas creadas (SHOW TABLES;)
- [ ] Índices y foreign keys en lugar

Controllers:
- [ ] CanchaController.php en app/controllers/instalaciones/
- [ ] MantenimientoController.php en app/controllers/instalaciones/
- [ ] Ambos heredan de BaseController
- [ ] Métodos públicos implementados

Views:
- [ ] index.php en app/views/instalaciones/canchas/
- [ ] formulario.php en app/views/instalaciones/canchas/
- [ ] tarifas.php en app/views/instalaciones/canchas/
- [ ] index.php en app/views/instalaciones/mantenimientos/
- [ ] formulario.php en app/views/instalaciones/mantenimientos/

Routing:
- [ ] Router.php reconoce /instalaciones/cancha/*
- [ ] Router.php reconoce /instalaciones/mantenimiento/*
- [ ] URL helper function url() disponible

Security:
- [ ] CSRF tokens en formularios
- [ ] Multi-tenant validation activa
- [ ] Auditoría funcionando
- [ ] Session checks activos

Testing:
- [ ] Crear cancha funciona
- [ ] Listar canchas funciona
- [ ] Agregar tarifa funciona
- [ ] Programar mantenimiento funciona
- [ ] Cambiar estado mantenimiento funciona
*/

// ===================================================================
// 🚨 NOTAS IMPORTANTES
// ===================================================================

/*
1. MULTI-TENANT
   - TODOS los queries incluyen: WHERE tenant_id = $this->tenantId
   - Un tenant NUNCA puede ver datos de otro tenant
   - $this->tenantId viene de $_SESSION['tenant_id']

2. AUDITORÍA
   - Cada INSERT/UPDATE/DELETE registra en tabla auditorias
   - audit() method en BaseController
   - Incluye: usuario_id, operacion, datos_antes, datos_despues

3. VALIDACIONES
   - Cliente-side: HTML5 validation (required, minlength, etc)
   - Servidor-side: Validación PHP (tipos, rangos, etc)
   - Database-level: UNIQUE constraints, FOREIGN KEYs, CHECKs

4. SOFT DELETES
   - Canchas se marcan como 'ELIMINADA', NO se borran
   - Protege la integridad referencial
   - Permite auditoría y recuperación futura

5. TARIFAS
   - UNIQUE constraint: (cancha_id, dia_semana, hora_inicio, hora_fin)
   - No se pueden duplicar horarios en la misma cancha
   - Precio debe ser > 0

6. MANTENIMIENTOS
   - fecha_fin debe ser > fecha_inicio
   - Pueden ser recurrentes (cadencia_recurrencia)
   - Estados: PROGRAMADO → EN_PROGRESO → COMPLETADO
             o PROGRAMADO → CANCELADO
   - Responsable es opcional

7. DISPONIBILIDAD
   - Tabla disponibilidad_canchas es cache
   - Se actualiza automáticamente con reservas y mantenimientos
   - Usado para búsquedas rápidas

8. EVENTOS
   - Tabla eventos_canchas registra todos los eventos
   - Importante para auditoría y debugging
   - Referencia a mantenimientos, reservas, etc
*/

// ===================================================================
// 📚 REFERENCIAS RÁPIDAS
// ===================================================================

/*
SQL ÚTILES:

-- Ver todas las canchas de un tenant
SELECT * FROM canchas WHERE tenant_id = 1;

-- Ver tarifas de una cancha
SELECT * FROM tarifas WHERE cancha_id = 1 ORDER BY dia_semana, hora_inicio;

-- Ver mantenimientos pendientes
SELECT * FROM mantenimientos 
WHERE estado IN ('PROGRAMADO', 'EN_PROGRESO')
ORDER BY fecha_inicio ASC;

-- Ver eventos de una cancha
SELECT * FROM eventos_canchas 
WHERE cancha_id = 1 
ORDER BY fecha_evento DESC;

-- Limpiar datos de prueba
DELETE FROM tarifas WHERE cancha_id = 1;
DELETE FROM mantenimientos WHERE cancha_id = 1;
DELETE FROM canchas WHERE cancha_id = 1;

-- Ver estructura de tabla
DESCRIBE canchas;
DESCRIBE tarifas;
DESCRIBE mantenimientos;
*/

// ===================================================================
// 🎓 PRÓXIMAS FASES
// ===================================================================

/*
PASO 3: SISTEMA DE RESERVAS
- ReservaController (crear, listar, confirmar, cancelar)
- Vistas: calendario, búsqueda, confirmación
- Tablas: reservas, reservas_lineas, confirmaciones
- Integración con tarifas para cálculo de precio
- Bloqueo automático de disponibilidad

PASO 4: FACTURACIÓN Y PAGOS
- FacturaController
- Integración con SRI Ecuador
- Pasarelas de pago (PayPhone, Datafast, PlacetoPay)
- Generación de comprobantes electrónicos
- Reportes de ingresos

PASO 5: REPORTES Y ANALYTICS
- ReporteController
- Dashboards por instalación
- Estadísticas de ocupación
- Ingresos por período
- Análisis de mantenimientos
*/

?>
