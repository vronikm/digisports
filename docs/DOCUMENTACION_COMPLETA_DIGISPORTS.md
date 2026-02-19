# 📖 DigiSports Arena — Documentación Completa del Sistema

**Versión**: 1.0.0  
**Última actualización**: 8 de febrero de 2026  
**Plataforma**: PHP 7.4.33 · MySQL 8.2.0 · Apache 2.4 (WAMP64)  
**Ruta del proyecto**: `C:\wamp64\www\digisports\`

---

## 📑 Índice

1. [Visión General](#1-visión-general)
2. [Arquitectura del Sistema](#2-arquitectura-del-sistema)
3. [Estructura de Directorios](#3-estructura-de-directorios)
4. [Patrón MVC y Flujo de Petición](#4-patrón-mvc-y-flujo-de-petición)
5. [Sistema de Rutas (Router)](#5-sistema-de-rutas-router)
6. [Controladores](#6-controladores)
7. [Vistas](#7-vistas)
8. [Protección de Datos (DataProtection)](#8-protección-de-datos-dataprotection)
9. [Helpers y Funciones Utilitarias](#9-helpers-y-funciones-utilitarias)
10. [Menú Dinámico](#10-menú-dinámico)
11. [Patrones Arquitectónicos](#11-patrones-arquitectónicos)
12. [Stack Tecnológico](#12-stack-tecnológico)
13. [Historial de Fases de Implementación](#13-historial-de-fases-de-implementación)
14. [Correcciones Post-Implementación](#14-correcciones-post-implementación)

---

## 1. Visión General

**DigiSports Arena** es un sistema de gestión integral para centros deportivos, diseñado como una aplicación web multi-tenant con arquitectura MVC estricta. Permite a múltiples empresas (tenants) gestionar de forma aislada sus:

- **Instalaciones y canchas** — CRUD completo, estados, capacidad
- **Reservas** — búsqueda de disponibilidad, franjas horarias, creación, edición, confirmación
- **Pagos** — efectivo, tarjeta, transferencia, monedero electrónico, pagos mixtos
- **Monedero/Abonos** — saldo prepagado, recargas, consumos, paquetes de horas
- **Entradas** — venta, tickets, control de acceso con escáner
- **Mantenimientos** — programación, estados, responsables
- **Clientes** — gestión con datos cifrados (LOPDP Ecuador)
- **Reportes y KPIs** — dashboard en tiempo real, exportación CSV
- **Seguridad** — usuarios, roles, permisos, auditoría, 2FA

---

## 2. Arquitectura del Sistema

### 2.1 Diagrama de Capas

```
┌─────────────────────────────────────────────────┐
│                   NAVEGADOR                     │
│         (Bootstrap 5 · jQuery · Chart.js)       │
└─────────────────┬───────────────────────────────┘
                  │ HTTP (URLs cifradas AES-256-GCM)
┌─────────────────▼───────────────────────────────┐
│              public/index.php                    │
│           (Entrypoint único)                     │
└─────────────────┬───────────────────────────────┘
                  │
┌─────────────────▼───────────────────────────────┐
│            config/Router.php                     │
│   parseEncryptedUrl() → dispatch()               │
└─────────────────┬───────────────────────────────┘
                  │
┌─────────────────▼───────────────────────────────┐
│            CONTROLADORES                         │
│  BaseController → ModuleController → Concretos   │
└───────┬─────────┬───────────────────────────────┘
        │         │
┌───────▼──┐  ┌───▼────────────────────────────────┐
│ SERVICIOS │  │           VISTAS                   │
│DataProtect│  │  app/views/ (layout module.php)    │
│ Security  │  └───────────────────────────────────┘
└───────┬──┘
        │
┌───────▼──────────────────────────────────────────┐
│          BASE DE DATOS MySQL 8.2                  │
│     digisports_core (multi-tenant)                │
└──────────────────────────────────────────────────┘
```

### 2.2 Herencia de Controladores

```
BaseController (app/controllers/BaseController.php)
    ├── authorize(), render(), renderJson()
    ├── get(), post(), isPost(), isAjax()
    ├── validateCsrf(), requirePermission()
    ├── audit(), beginTransaction(), commit(), rollback()
    │
    └── ModuleController (app/controllers/ModuleController.php)
            ├── loadModuleBranding(), setupModule()
            ├── loadDynamicMenu(), buildMenuArray()
            ├── renderModule(), getBaseStats(), getChartData()
            │
            ├── ReservaController
            ├── CanchaController
            ├── MantenimientoController
            ├── EntradaController
            ├── DashboardController
            ├── ClienteController
            ├── AbonController
            ├── PagoController
            ├── UsuarioController
            └── ReporteArenaController
```

---

## 3. Estructura de Directorios

```
digisports/
├── app/
│   ├── controllers/                 # Controladores MVC
│   │   ├── BaseController.php       # Clase base (~550 líneas)
│   │   ├── ModuleController.php     # Controlador de módulos (~380 líneas)
│   │   ├── clientes/
│   │   │   └── ClienteController.php
│   │   ├── instalaciones/
│   │   │   ├── CanchaController.php
│   │   │   ├── DashboardController.php
│   │   │   ├── EntradaController.php
│   │   │   ├── MantenimientoController.php
│   │   │   └── CalendarioController.php
│   │   ├── reportes/
│   │   │   └── ReporteArenaController.php
│   │   ├── reservas/
│   │   │   ├── AbonController.php
│   │   │   ├── PagoController.php
│   │   │   └── ReservaController.php
│   │   └── seguridad/
│   │       ├── UsuarioController.php
│   │       ├── RolController.php
│   │       └── ...
│   ├── helpers/
│   │   └── functions.php            # Funciones utilitarias (~700 líneas)
│   ├── models/                      # Modelos (no usados directamente - queries en controllers)
│   ├── services/
│   │   └── DataProtection.php       # Cifrado PII (~351 líneas)
│   └── views/
│       ├── layouts/
│       │   ├── main.php             # Layout principal
│       │   └── module.php           # Layout módulos con menú lateral
│       ├── clientes/                # 3 vistas
│       ├── instalaciones/           # 16+ vistas en subdirectorios
│       ├── reservas/                # 14+ vistas en subdirectorios
│       └── seguridad/               # 20+ vistas en subdirectorios
├── config/
│   ├── app.php                      # Configuración general
│   ├── database.php                 # Conexión PDO (clase Database singleton)
│   ├── Router.php                   # Enrutador (~426 líneas)
│   ├── security.php                 # Clase Security (cifrado, CSRF, auth)
│   └── smtp.php                     # Configuración de correo
├── database/                        # Scripts SQL de instalación
├── docs/                            # Documentación
├── public/
│   ├── index.php                    # Entrypoint único
│   └── assets/                      # CSS, JS, imágenes
├── storage/                         # Logs, uploads, certificados
└── vendor/                          # Dependencias Composer
```

---

## 4. Patrón MVC y Flujo de Petición

### 4.1 Flujo Completo

```
1. Usuario hace clic en enlace
   ↓
2. GET /index.php?r=<TOKEN_AES_CIFRADO>
   ↓
3. Router::dispatch()
   ├── parseEncryptedUrl($token)
   │   └── Security::decodeSecureUrl($token) → {m, c, a, p, t}
   │       m = módulo (ej: "reservas")
   │       c = controlador (ej: "reserva")
   │       a = acción (ej: "ver")
   │       p = parámetros (ej: {id: 5})
   │       t = timestamp
   ├── Valida módulo activo en BD
   ├── Busca archivo: app/controllers/{módulo}/{Controlador}Controller.php
   ├── Instancia controlador → __construct() → setupModule()
   └── Ejecuta $controller->{acción}()
       ↓
4. Controlador
   ├── Valida permisos (requirePermission / authorize)
   ├── Lee input (get/post con sanitización)
   ├── Ejecuta query SQL (prepared statements)
   ├── Descifra datos sensibles (DataProtection::decrypt)
   ├── Prepara $viewData
   └── renderModule('vista', $viewData)
       ↓
5. Vista
   ├── Recibe variables via extract($viewData)
   ├── Genera HTML con Bootstrap 5
   └── Se inserta en layout module.php (menú lateral + header)
       ↓
6. Respuesta HTML al navegador
```

### 4.2 Generación de URLs

Todas las URLs internas se generan con la función `url()`:

```php
// Genera: index.php?r=<AES-256-GCM-BASE64>
$urlReserva = url('reservas', 'reserva', 'ver', ['id' => 5]);

// El token cifrado contiene:
// {"m":"reservas","c":"reserva","a":"ver","p":{"id":5},"t":1707400000}
```

**Importante**: Los parámetros (`p`) se inyectan en `$_GET` por el Router después de descifrar, permitiendo que `$this->get('id')` funcione normalmente en el controlador.

---

## 5. Sistema de Rutas (Router)

**Archivo**: `config/Router.php` (~426 líneas)

### 5.1 Métodos Principales

| Método | Descripción |
|---|---|
| `parseUrl()` | Decide entre URL cifrada (`$_GET['r']`) o estándar (dev) |
| `parseEncryptedUrl($token)` | Decodifica con `Security::decodeSecureUrl()`, extrae módulo/controller/acción/params |
| `parseStandardUrl()` | Fallback para desarrollo: `?module=X&controller=Y&action=Z` |
| `dispatch()` | Carga controlador, valida método público, ejecuta acción |
| `generateUrl($mod,$ctrl,$act,$params)` | Genera URL cifrada |
| `isPublicRoute()` | Rutas sin autenticación: auth/login, registro, error |

### 5.2 Validaciones del Router

1. Verifica que el módulo existe y está activo en `seguridad_modulos`
2. Verifica que el archivo del controlador existe en disco
3. Verifica que la acción es un método público del controlador
4. Inyecta parámetros descifrados en `$_GET` para acceso transparente

---

## 6. Controladores

### 6.1 ReservaController

**Archivo**: `app/controllers/reservas/ReservaController.php` (~1172 líneas)  
**Módulo**: ARENA

| Método | Descripción |
|---|---|
| `buscar()` | Pantalla de búsqueda de disponibilidad. Obtiene instalaciones, tipos, canchas, tarifas y bloqueos (reservas + mantenimientos). Calcula franjas horarias disponibles por cancha |
| `crear()` | POST. Valida datos, busca/crea cliente automáticamente, verifica conflictos horarios, inserta en tabla `reservas`, genera auditoría. Responde JSON |
| `confirmacion()` | Vista de confirmación post-reserva exitosa con todos los detalles |
| `index()` | Listado paginado con filtros: estado, estado_pago, búsqueda por nombre/instalación, rango de fechas. KPIs en tiempo real: reservas hoy, pendientes de pago, recaudado del mes, por cobrar |
| `ver()` | Detalle de reserva con datos del cliente descifrados y historial completo de pagos |
| `confirmar()` | Cambia estado a `CONFIRMADA` con auditoría |
| `completar()` | Cambia estado a `COMPLETADA` |
| `cancelar()` | Cambia estado a `CANCELADA` con motivo obligatorio |
| `obtenerDisponibilidad()` | API JSON que retorna franjas horarias disponibles para una cancha en una fecha específica |
| `editar()` | GET: formulario de edición con selector de franja horaria y detección de cambios. POST: actualiza fecha/hora/precio, verifica conflictos, recalcula tarifa, registra auditoría |

### 6.2 AbonController (Monedero/Abonos)

**Archivo**: `app/controllers/reservas/AbonController.php` (~702 líneas)

| Método | Descripción |
|---|---|
| `index()` | Listado de monederos con búsqueda por nombre, paginación y resumen global (total monederos, activos, saldo total) |
| `ver()` | Detalle de monedero con últimos 20 movimientos (recarga/consumo/devolución) |
| `crear()` | Formulario: lista clientes sin monedero activo + paquetes disponibles |
| `guardar()` | Crea monedero con recarga inicial en `instalaciones_abonos`, registra movimiento, actualiza `cli_saldo_abono` |
| `recargar()` | POST: suma monto al saldo, registra movimiento tipo RECARGA |
| `consumir()` | POST: resta saldo, registra movimiento tipo CONSUMO (vinculable a reserva) |
| `historial()` | Listado global de movimientos con filtros |
| `paquetes()` | Gestión de paquetes de horas prepagados |
| `guardarPaquete()` | CRUD de paquetes con cálculo de descuento |
| `saldo()` | API JSON: consulta saldo actual del monedero de un cliente |

### 6.3 PagoController

**Archivo**: `app/controllers/reservas/PagoController.php` (~649 líneas)

| Método | Descripción |
|---|---|
| `checkout()` | Pantalla de cobro: muestra reserva, pagos previos, saldo monedero disponible, monto pendiente calculado |
| `procesarPago()` | Registra pago. Soporta 5 formas: EFECTIVO, TARJETA, TRANSFERENCIA, MONEDERO, MIXTO. Descuenta monedero si aplica. Auto-confirma reserva si el pago cubre el total |
| `comprobante()` | Recibo de pago con datos del cliente descifrados (email, teléfono, identificación) |
| `index()` | Historial con filtros por rango de fechas y búsqueda. Resumen por método de pago (efectivo, tarjeta, transferencia, monedero) |
| `anular()` | Anula pago, devuelve monto al monedero si aplica, recalcula totales en la reserva |
| `saldoCliente()` | API JSON: saldo del monedero |

### 6.4 CanchaController

**Archivo**: `app/controllers/instalaciones/CanchaController.php` (~762 líneas)

| Método | Descripción |
|---|---|
| `index()` | Listado con filtros: búsqueda, tipo de cancha, estado, paginación |
| `ver()` | Detalle con tarifas, últimas 10 reservas, mantenimientos activos, KPIs (total reservas, confirmadas, hoy, ingresos) |
| `crear()` | Formulario nueva cancha (lista instalaciones activas del tenant) |
| `guardar()` | POST: inserta en tabla `canchas`, valida que la instalación pertenezca al tenant |
| `editar()` | Formulario de edición |
| `actualizar()` | POST: actualiza campos con auditoría |
| `eliminar()` | Soft delete (estado=ELIMINADA), verifica que no tenga reservas activas |
| `tarifas()` | Vista de tarifas por día de semana y horario |
| `guardarTarifa()` | Crear/actualizar tarifa (día_semana, hora_inicio, hora_fin, precio) |
| `eliminarTarifa()` | Elimina tarifa |

### 6.5 EntradaController

**Archivo**: `app/controllers/instalaciones/EntradaController.php` (~788 líneas)

| Método | Descripción |
|---|---|
| `index()` | Listado de entradas vendidas con resumen diario: vendidas, usadas, anuladas, recaudado, monedero, efectivo |
| `vender()` | Formulario de venta: instalaciones, clientes (con datos descifrados), tarifas activas |
| `guardar()` | Registra venta: genera código `ENT{yymmdd}{0001}`, soporta CORTESÍA, MONEDERO, MIXTO |
| `ticket()` | Comprobante/ticket imprimible con datos del cliente descifrados |
| `registrarIngreso()` | Marca entrada como USADA (por ID o código) |
| `anular()` | Anula entrada, devuelve monedero si aplica |
| `tarifas()` | Gestión de tarifas de entrada por instalación |
| `guardarTarifa()` | CRUD de tarifas de entrada |
| `obtenerTarifas()` | API JSON: tarifas filtradas por instalación |
| `escanear()` | Pantalla de control de acceso/escáner QR |
| `buscarCodigo()` | API JSON: busca entrada por código de ticket |

### 6.6 ClienteController

**Archivo**: `app/controllers/clientes/ClienteController.php` (~706 líneas)

| Método | Descripción |
|---|---|
| `index()` | Listado con filtros. Usa `DataProtection::decryptRows('clientes', ...)` para descifrar datos sensibles |
| `crear()` | Formulario nuevo cliente |
| `guardar()` | POST: cifra datos con `encryptRow('clientes', ...)`, verifica duplicados por blind index (`cli_identificacion_hash`) |
| `ver()` | Detalle completo: reservas, pagos, abonos y entradas del cliente |
| `editar()` | Formulario edición con datos descifrados |
| `actualizar()` | POST: cifra y actualiza con auditoría |
| `eliminar()` | Soft delete (estado='I'), verifica no tenga reservas activas |
| `buscar()` | API JSON: búsqueda AJAX por nombre + identificación vía blind index |

### 6.7 MantenimientoController

**Archivo**: `app/controllers/instalaciones/MantenimientoController.php` (~600 líneas)

| Método | Descripción |
|---|---|
| `index()` | Listado con filtros por estado y cancha, paginación |
| `ver()` | Detalle con historial de mantenimientos previos de la misma cancha |
| `crear()` | Formulario: canchas activas + usuarios técnicos/admin como responsables |
| `guardar()` | POST: inserta en `mantenimientos` (tipo, descripción, fechas, responsable, recurrencia) |
| `editar()` | Formulario edición |
| `actualizar()` | POST: actualiza campos con auditoría |
| `eliminar()` | DELETE físico de registro |
| `cambiarEstado()` | Transiciones: PROGRAMADO → EN_PROGRESO → COMPLETADO / CANCELADO |

### 6.8 UsuarioController

**Archivo**: `app/controllers/seguridad/UsuarioController.php` (~400 líneas)

| Método | Descripción |
|---|---|
| `index()` | Listado con filtros (tenant, estado, búsqueda). Usa `DataProtection::decryptRows('seguridad_usuarios')` |
| `crear()` | GET: formulario. POST: llama a `guardar()` |
| `editar()` | GET: carga usuario con descifrado. POST: llama a `guardar($id)` |
| `eliminar()` | Soft delete (`usu_estado = 'E'`) con auditoría |
| `desbloquear()` | Reset de intentos fallidos y bloqueo temporal |
| `bloqueados()` | Lista usuarios con ≥3 intentos fallidos |
| `resetPassword()` | Genera contraseña aleatoria 8 chars, hashea con `PASSWORD_ARGON2ID`, marca `usu_debe_cambiar_password` |

### 6.9 DashboardController (Arena)

**Archivo**: `app/controllers/instalaciones/DashboardController.php` (~267 líneas)

| Método | Descripción |
|---|---|
| `index()` | Dashboard con 6 KPIs en tiempo real: canchas activas, reservas hoy, ingresos del mes, tasa de ocupación, entradas hoy, saldo total monedero. Incluye gráficos Chart.js (reservas 7 días, métodos de pago) |

### 6.10 ReporteArenaController

**Archivo**: `app/controllers/reportes/ReporteArenaController.php` (~537 líneas)

| Método | Descripción |
|---|---|
| `index()` | Dashboard de reportes: KPIs financieros, gráfico polilínea de ingresos, distribución por forma de pago, top 5 clientes |
| `facturas()` | Reporte de facturas con filtros |
| `ingresos()` | Reporte de ingresos por período |
| `clientes()` | Reporte de actividad de clientes |
| `exportarCSV()` | Exportación de datos a CSV |

---

## 7. Vistas

### 7.1 Clientes (`app/views/clientes/`)

| Archivo | Propósito |
|---|---|
| `index.php` | Tabla paginada de clientes con filtros (búsqueda, tipo, estado). Formulario POST |
| `form.php` | Formulario crear/editar con campos: tipo identificación, identificación, nombres, apellidos, email, teléfono, celular, dirección |
| `ver.php` | Ficha completa del cliente: datos personales descifrados, historial de reservas, pagos, abonos, entradas |

### 7.2 Reservas (`app/views/reservas/`)

| Archivo | Propósito |
|---|---|
| `index.php` | Listado con KPIs, filtros y paginación. Formulario POST |
| `buscar.php` | Buscador de disponibilidad: selección de instalación, tipo, fecha. Grilla de franjas horarias con estados (Disponible/Ocupada/Mantenimiento) |
| `calendario.php` | Vista mensual de reservas en formato calendario |
| `confirmacion.php` | Pantalla post-reserva con resumen y botón para pago |
| `editar.php` | Formulario de edición con selector de franja horaria (radio buttons), panel de detección de cambios en tiempo real, submit AJAX |
| `ver.php` | Detalle de reserva con datos del cliente, información de la cancha, historial de pagos, botones de acción (confirmar, completar, cancelar, editar) |

### 7.3 Abonos/Monedero (`app/views/reservas/abonos/`)

| Archivo | Propósito |
|---|---|
| `index.php` | Listado de monederos activos con saldo y estado |
| `ver.php` | Detalle de monedero con movimientos recientes |
| `crear.php` | Formulario nuevo monedero: selección de cliente + paquete opcional |
| `historial.php` | Historial global de movimientos (recargas/consumos/devoluciones) |
| `paquetes.php` | CRUD de paquetes de horas prepagados |

### 7.4 Pagos (`app/views/reservas/pagos/`)

| Archivo | Propósito |
|---|---|
| `index.php` | Historial de pagos con filtros por fecha y resumen por método |
| `checkout.php` | Pantalla de cobro con selector de forma de pago, campo de referencia, soporte monedero+mixto |
| `comprobante.php` | Recibo imprimible con datos descifrados del cliente |

### 7.5 Instalaciones (`app/views/instalaciones/`)

| Subdirectorio | Archivos principales |
|---|---|
| `dashboard/` | `index.php` — Dashboard principal con 6 tarjetas KPI, reservas de hoy, gráficos |
| `canchas/` | `index.php`, `ver.php`, `formulario.php`, `tarifas.php` |
| `entradas/` | `index.php`, `vender.php`, `ticket.php`, `escanear.php`, `tarifas.php` |
| `mantenimientos/` | `index.php`, `ver.php`, `formulario.php` |
| `calendario/` | `index.php` — Vista calendario de ocupación |
| `reportes/` | `index.php`, `ingresos.php` — Reportes Arena |

### 7.6 Seguridad (`app/views/seguridad/`)

| Subdirectorio | Archivos principales |
|---|---|
| `usuario/` | `index.php`, `form.php`, `crear.php`, `editar.php`, `eliminar.php`, `bloqueados.php`, `desbloquear.php`, `resetPassword.php` |
| `rol/` | `index.php`, `form.php`, `crear.php`, `editar.php`, `permisos.php` |
| `tenant/` | `index.php`, `form.php`, `crear.php`, `ver.php`, `suspender.php`, `reactivar.php`, `renovar.php`, `suscripciones.php` |
| `modulo/` | `index.php`, `form.php`, `crear.php`, `editar.php`, `duplicar.php`, `iconos.php`, `configuracion.php` |
| `menu/` | `index.php`, `form.php` |
| `auditoria/` | `accesos.php`, `alertas.php`, `cambios.php` |
| `dashboard/` | `index.php` |

---

## 8. Protección de Datos (DataProtection)

**Archivo**: `app/services/DataProtection.php` (~351 líneas)  
**Cumplimiento**: Ley Orgánica de Protección de Datos Personales (LOPDP) Ecuador

### 8.1 Algoritmo de Cifrado

| Propiedad | Valor |
|---|---|
| **Cifrado** | AES-256-CBC |
| **Prefijo** | `ENC::` (identifica datos cifrados) |
| **IV** | 16 bytes aleatorios por cada cifrado |
| **Clave derivada** | `SHA-256(masterKey + '::PII_DATA_PROTECTION')` |
| **Blind Index** | `HMAC-SHA256(valor_normalizado, hmacKey)` truncado a 32 chars |
| **Clave HMAC** | `SHA-256(masterKey + '::PII_BLIND_INDEX')` |

### 8.2 FIELD_MAP — Campos Cifrados por Tabla

#### Tabla `clientes`

| Campo | Cifrado | Blind Index (`_hash`) |
|---|---|---|
| `cli_identificacion` | ✅ AES-256-CBC | ✅ `cli_identificacion_hash` |
| `cli_email` | ✅ AES-256-CBC | ✅ `cli_email_hash` |
| `cli_telefono` | ✅ AES-256-CBC | ❌ |
| `cli_celular` | ✅ AES-256-CBC | ❌ |

#### Tabla `seguridad_usuarios`

| Campo | Cifrado | Blind Index (`_hash`) |
|---|---|---|
| `usu_identificacion` | ✅ | ✅ `usu_identificacion_hash` |
| `usu_email` | ✅ | ✅ `usu_email_hash` |
| `usu_telefono` | ✅ | ❌ |
| `usu_celular` | ✅ | ❌ |

#### Tabla `seguridad_tenants`

| Campo | Cifrado | Blind Index |
|---|---|---|
| `ten_ruc` | ✅ | ✅ |
| `ten_email` | ✅ | ✅ |
| `ten_telefono` | ✅ | ❌ |
| `ten_celular` | ✅ | ❌ |
| `ten_representante_identificacion` | ✅ | ✅ |
| `ten_representante_email` | ✅ | ✅ |
| `ten_representante_telefono` | ✅ | ❌ |

### 8.3 Métodos Principales

| Método | Descripción |
|---|---|
| `encrypt($plaintext)` | Cifra un valor individual. Retorna `ENC::base64(iv+encrypted)` |
| `decrypt($ciphertext)` | Descifra un valor. Retorna texto plano o fallback |
| `isEncrypted($value)` | Verifica si empieza con `ENC::` |
| `blindIndex($plaintext)` | Genera hash HMAC para búsquedas exactas sin descifrar |
| `encryptRow($table, $row)` | Cifra todos los campos del FIELD_MAP + genera columnas `_hash` |
| `decryptRow($table, $row)` | Descifra campos del FIELD_MAP en una fila |
| `decryptRows($table, $rows)` | Descifra array de filas |
| `mask($value, $type)` | Enmascara para logs: `091***678`, `us***@email.com` |

### 8.4 Uso en Controladores

```php
// Al INSERTAR/ACTUALIZAR — cifrar antes de guardar
$data = DataProtection::encryptRow('clientes', $data);
$stmt->execute($data);

// Al LEER con SELECT * — descifrar automáticamente
$clientes = DataProtection::decryptRows('clientes', $filas);

// Al LEER con JOIN y alias — descifrar individualmente
$row['cliente_email'] = DataProtection::decrypt($row['cliente_email']);
$row['cliente_telefono'] = DataProtection::decrypt($row['cliente_telefono']);

// Para búsqueda exacta — usar blind index
$hash = DataProtection::blindIndex($emailBuscado);
$stmt->execute(["SELECT * FROM clientes WHERE cli_email_hash = ?", $hash]);
```

---

## 9. Helpers y Funciones Utilitarias

**Archivo**: `app/helpers/functions.php` (~700 líneas)

### 9.1 Funciones de Autenticación

| Función | Descripción |
|---|---|
| `initSSOSession($userData)` | Inicia sesión SSO |
| `isAuthenticated()` | Verifica `$_SESSION['user_id']` |
| `isAdmin()` | Verifica rol administrador |
| `isSuperAdmin()` | Verifica rol superadmin |
| `getUserId()` | ID del usuario actual |
| `getTenantId()` | ID del tenant actual |
| `getCurrentUser()` | Datos completos del usuario |

### 9.2 Funciones de URL

| Función | Descripción |
|---|---|
| `url($mod, $ctrl, $act, $params)` | **Genera URL cifrada** con `Security::encryptUrl()` |
| `urlSimple($mod, $ctrl, $act, $params)` | URL con GET params (solo desarrollo) |
| `redirect($mod, $ctrl, $act, $params)` | Redireccionamiento HTTP |
| `baseUrl($path)` | URL base del sitio |
| `asset($path)` | URL de assets estáticos |

### 9.3 Funciones de Formato

| Función | Descripción |
|---|---|
| `e($string)` | `htmlspecialchars()` para escapar HTML |
| `formatDate($date)` | Formato `d/m/Y` |
| `formatDateTime($date)` | Formato `d/m/Y H:i` |
| `formatMoney($amount)` | Formato `$1,234.56` |
| `timeAgo($datetime)` | "Hace 5 minutos", "Hace 2 horas" |

### 9.4 Funciones de Permisos

| Función | Descripción |
|---|---|
| `hasPermission($perm)` | Verifica permiso del usuario |
| `hasModuleAccess($module)` | Verifica acceso al módulo |

### 9.5 Funciones de Archivos

| Función | Descripción |
|---|---|
| `uploadFile($file, $dir, $allowed)` | Sube archivo con validación |
| `deleteFile($path)` | Elimina archivo |
| `fileUrl($path)` | URL pública del archivo |

### 9.6 Otras Utilidades

| Función | Descripción |
|---|---|
| `setFlashMessage($type, $msg)` | Flash message en sesión |
| `getFlashMessage()` | Lee y elimina flash message |
| `sendNotification(...)` | Inserta en tabla `notificaciones` |
| `logMessage($msg, $type)` | Log a archivo en `storage/logs/` |

---

## 10. Menú Dinámico

### 10.1 Estructura de la Tabla `seguridad_menu`

```sql
seguridad_menu (
    men_id              INT PK AUTO_INCREMENT,
    men_modulo_id       INT FK → seguridad_modulos,
    men_padre_id        INT FK → seguridad_menu (self-ref, NULL = raíz),
    men_tipo            ENUM('HEADER', 'ITEM', 'SUBMENU'),
    men_label           VARCHAR(100),
    men_icono           VARCHAR(50),        -- Clase FontAwesome
    men_ruta_modulo     VARCHAR(50),        -- ej: "reservas"
    men_ruta_controller VARCHAR(50),        -- ej: "reserva"
    men_ruta_action     VARCHAR(50),        -- ej: "index"
    men_url_custom      VARCHAR(255),       -- URL externa (NULL = generar con url())
    men_badge           VARCHAR(50),
    men_badge_tipo      VARCHAR(20),
    men_orden           INT,
    men_activo          TINYINT(1),
    men_visible_rol     VARCHAR(100),       -- Roles que lo ven (NULL = todos)
    men_tenant_id       INT
)
```

### 10.2 Menú del Módulo Arena (mod_id = 1)

| # | Tipo | Label | Icono | Ruta | Orden |
|---|---|---|---|---|---|
| 1 | HEADER | Principal | — | — | 0 |
| 2 | ITEM | Dashboard | `fas fa-tachometer-alt` | instalaciones/dashboard/index | 1 |
| 3 | ITEM | Canchas | `fas fa-futbol` | instalaciones/cancha/index | 2 |
| 4 | ITEM | Mantenimientos | `fas fa-wrench` | instalaciones/mantenimiento/index | 3 |
| 5 | ITEM | Reservas | `fas fa-calendar-check` | reservas/reserva/index | 4 |
| 110 | ITEM | Calendario | `fas fa-calendar-alt` | instalaciones/calendario/index | 5 |
| 111 | ITEM | Monedero / Abonos | `fas fa-wallet` | reservas/abon/index | 6 |
| 112 | ITEM | Paquetes de Horas | `fas fa-box` | reservas/abon/paquetes | 7 |
| 113 | ITEM | Pagos | `fas fa-credit-card` | reservas/pago/index | 8 |
| 114 | ITEM | Entradas | `fas fa-ticket-alt` | instalaciones/entrada/index | 9 |
| 115 | ITEM | Tarifas Entrada | `fas fa-tags` | instalaciones/entrada/tarifas | 10 |
| 116 | ITEM | Control Acceso | `fas fa-door-open` | instalaciones/entrada/escanear | 11 |
| 117 | ITEM | Reportes Arena | `fas fa-chart-bar` | reportes/reporteArena/index | 12 |
| 119 | ITEM | Clientes | `fas fa-users` | clientes/cliente/index | 13 |

### 10.3 Permisos del Menú

Los permisos se controlan en `seguridad_rol_menu`:

```sql
seguridad_rol_menu (
    rme_rol_id      INT FK → seguridad_roles,
    rme_menu_id     INT FK → seguridad_menu,
    rme_puede_ver   TINYINT(1),
    rme_puede_crear TINYINT(1),
    rme_puede_editar TINYINT(1),
    rme_puede_eliminar TINYINT(1)
)
```

`ModuleController::loadDynamicMenu()` filtra los ítems del menú según los permisos del rol del usuario actual.

---

## 11. Patrones Arquitectónicos

### 11.1 Multi-Tenant

Todo query incluye filtro por tenant:

```php
// Cada consulta filtra por tenant_id del usuario actual
$stmt = $this->db->prepare("SELECT * FROM canchas WHERE tenant_id = ?");
$stmt->execute([$this->tenantId]);
```

### 11.2 CSRF Protection

```php
// En el controlador: generar token
$this->viewData['csrf_token'] = \Security::generateCsrfToken();

// En la vista: campo hidden
<input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">

// En el controlador: validar
if (!$this->validateCsrf()) {
    $this->error('Token de seguridad inválido', 403);
}
```

### 11.3 Auditoría

```php
// Registro automático de cambios
$this->audit('reservas', $reservaId, 'ACTUALIZAR', $antes, $despues);
// Registra: tabla, registro_id, operación, valores_antes, valores_después, IP, user_agent, timestamp
```

### 11.4 Soft Delete

| Tabla | Campo | Valor activo | Valor eliminado |
|---|---|---|---|
| Canchas | `estado` | `ACTIVA` | `ELIMINADA` |
| Clientes | `cli_estado` | `A` | `I` |
| Usuarios | `usu_estado` | `A` | `E` |

### 11.5 Pagos Mixtos

El sistema soporta pagos combinados (efectivo + monedero):

```
1. Cliente tiene $30 en monedero
2. Reserva cuesta $50
3. Pago MIXTO: $30 de monedero + $20 efectivo
4. Sistema descuenta monedero + registra pago
5. Si cubre el total → auto-confirma reserva
```

### 11.6 Paginación Consistente

```php
$pagina  = max(1, (int)($this->post('pagina') ?? $this->get('pagina') ?? 1));
$perPage = 15;
$offset  = ($pagina - 1) * $perPage;

// Query con LIMIT y OFFSET
$query .= " LIMIT " . (int)$perPage . " OFFSET " . (int)$offset;

// Calcular total de páginas
$totalPaginas = ceil($totalRegistros / $perPage);
```

---

## 12. Stack Tecnológico

### 12.1 Backend

| Componente | Versión | Uso |
|---|---|---|
| PHP | 7.4.33 | Lenguaje principal |
| MySQL | 8.2.0 | Base de datos |
| Apache | 2.4 | Servidor web |
| WAMP | 3.3.x | Entorno local Windows |
| PDO | — | Acceso a BD (prepared statements) |
| Composer | — | Gestión de dependencias |

### 12.2 Frontend

| Componente | Versión | Uso |
|---|---|---|
| Bootstrap | 5.x | Framework CSS responsive |
| AdminLTE | 3.2 | Template de administración |
| jQuery | 3.6 | Manipulación DOM y AJAX |
| Chart.js | — | Gráficos en dashboard y reportes |
| FontAwesome | 5.15.4 | Iconografía |
| SweetAlert2 | — | Alertas y confirmaciones |

### 12.3 Seguridad

| Componente | Uso |
|---|---|
| AES-256-CBC | Cifrado de datos personales (PII) |
| AES-256-GCM | Cifrado de URLs |
| HMAC-SHA256 | Blind index para búsquedas |
| Argon2ID | Hash de contraseñas |
| CSRF Tokens | Protección contra CSRF |
| Prepared Statements | Prevención SQL injection |
| `htmlspecialchars()` | Prevención XSS |

---

## 13. Historial de Fases de Implementación

### Fase 1 — Base del Sistema

- Vistas SQL para consultas complejas
- Correcciones en controladores base
- `AbonController` — gestión de monedero
- `CalendarioController` — vista calendario
- Menús #110, #111, #112 en Arena

### Fase 2 — Pagos y Entradas

- `PagoController` — checkout, procesamiento, comprobantes
- `EntradaController` — venta, tickets, control acceso
- Tablas de pagos (`instalaciones_reserva_pagos`)
- Menús #113, #114, #115, #116
- **Verificado: 40/40 tests**

### Fase 3 — Dashboard y Reportes

- Dashboard con KPIs en tiempo real
- `ReservaController` mejorado con filtros avanzados
- `ClienteController` corregido
- `ReporteArenaController` con gráficos y exportación
- Menú #117 (Reportes Arena)
- **Verificado: 46/46 tests**

### Fase 4 — Migración a ModuleController

- Migración de `CanchaController` a `ModuleController`
- Migración de `MantenimientoController` a `ModuleController`
- Migración de `ReservaController` a `ModuleController`
- Vistas de detalle creadas/corregidas
- Corrección de cifrado de URLs
- **Verificado: 66/66 tests**

### Fase 5 — Clientes y Formularios

- Vistas SQL para usuarios/roles/tenants
- `MantenimientoController` corregido
- `ClienteController` migrado a `ModuleController`
- Formulario de clientes corregido
- **Verificado: 50/50 tests**

### Fase 6 — Edición de Reservas

- `ReservaController::editar()` (~200 líneas): GET muestra formulario, POST procesa actualización
- Vista `reservas/editar.php` (~270 líneas): selector de franja horaria con radio buttons, panel de detección de cambios JavaScript, submit AJAX
- Botón "Editar Reserva" en `reservas/ver.php` para estados PENDIENTE/CONFIRMADA
- Menú #117 reubicado (mod_id 4 → mod_id 1)
- Menú #119 (Clientes) añadido al módulo Arena
- **Verificado: 165/165 tests**

---

## 14. Correcciones Post-Implementación

### 14.1 Bug: CanchaController ORDER BY

**Síntoma**: Error `{"success":false,"message":"Error al cargar las canchas","error_code":400}`  
**Causa raíz**: Línea 68 usaba `ORDER BY i.nombre` pero la columna real es `i.ins_nombre`  
**Corrección**: Cambiado a `ORDER BY i.ins_nombre, c.nombre`  
**Archivo**: `app/controllers/instalaciones/CanchaController.php`

### 14.2 Bug: Botón "Nueva Reserva" en Dashboard

**Síntoma**: Error `{"success":false,"message":"Solicitud inválida","error_code":400}` al hacer clic  
**Causa raíz**: Los botones apuntaban a `url('reservas', 'reserva', 'crear')` que solo acepta POST  
**Corrección**: Cambiado a `url('reservas', 'reserva', 'buscar')` (pantalla de disponibilidad)  
**Archivo**: `app/views/instalaciones/dashboard/index.php`

### 14.3 Bug: Datos cifrados visibles en vistas

**Síntoma**: Campos como email, teléfono e identificación mostraban texto `ENC::...`  
**3 causas raíz identificadas y corregidas**:

#### Causa A: Columnas sin prefijo `cli_` en clientes/index.php

La vista accedía a `$cliente['identificacion']` en lugar de `$cliente['cli_identificacion']`. Como `DataProtection::decryptRows()` solo procesa claves que coincidan con el FIELD_MAP (`cli_identificacion`, `cli_email`, etc.), los datos quedaban sin descifrar.

**Corrección**: Renombradas todas las referencias a columnas con prefijo `cli_*` en `app/views/clientes/index.php`.

#### Causa B: Formularios GET perdían ruta cifrada

Los formularios de filtro usaban `method="GET"`, lo cual reemplazaba el parámetro `?r=<TOKEN>` con los campos del formulario, rompiendo el enrutamiento.

**Corrección**: Convertidos 6 formularios de GET a POST + 6 controladores actualizados para leer `$this->post() ?? $this->get()`:

| Vista | Controlador |
|---|---|
| `clientes/index.php` | `ClienteController` |
| `instalaciones/canchas/index.php` | `CanchaController` |
| `instalaciones/entradas/index.php` | `EntradaController` |
| `reservas/abonos/index.php` | `AbonController` |
| `reservas/pagos/index.php` | `PagoController` |
| `seguridad/usuario/index.php` | `UsuarioController` |

#### Causa C: Controladores no descifraban datos de JOINs con alias

Cuando un controlador hace `c.cli_email AS cliente_email` en un JOIN, `DataProtection::decryptRows()` no funciona porque busca la clave `cli_email` pero el array tiene `cliente_email`. Los controladores debían llamar a `DataProtection::decrypt()` individualmente.

**8 puntos corregidos** con `DataProtection::decrypt()`:

| Controlador | Método | Campos descifrados |
|---|---|---|
| `ReservaController` | `ver()` | `cliente_email`, `cliente_telefono` |
| `ReservaController` | `editar()` | `cliente_email`, `cliente_telefono` |
| `PagoController` | `checkout()` | `cliente_email`, `cliente_telefono` |
| `PagoController` | `comprobante()` | `cliente_email`, `cliente_telefono`, `cliente_identificacion` |
| `AbonController` | `index()` | `cliente_email`, `cliente_telefono`, `cliente_identificacion` |
| `AbonController` | `ver()` | `cliente_email`, `cliente_telefono`, `cliente_identificacion` |
| `AbonController` | `crear()` | `email`, `identificacion` (dropdown clientes) |
| `EntradaController` | `index()`, `vender()`, `ticket()` | `cliente_email`, `cli_email`, `cli_identificacion` |

**Corrección adicional**: Eliminada búsqueda `LIKE` en `AbonController` sobre campos cifrados (`cli_email LIKE ?`, `cli_identificacion LIKE ?`) que nunca encontraría resultados. Ahora solo busca por `cli_nombres` y `cli_apellidos`.

---

## Apéndice A: Base de Datos — Tablas Principales

| Tabla | Propósito |
|---|---|
| `seguridad_usuarios` | Usuarios del sistema |
| `seguridad_tenants` | Empresas/tenants |
| `seguridad_roles` | Roles de usuario |
| `seguridad_modulos` | Módulos disponibles |
| `seguridad_menu` | Ítems de menú por módulo |
| `seguridad_rol_menu` | Permisos de menú por rol |
| `seguridad_plan_modulos` | Planes de suscripción |
| `clientes` | Clientes del tenant |
| `instalaciones` | Instalaciones deportivas |
| `canchas` | Canchas/espacios deportivos |
| `reservas` | Reservas de canchas |
| `reservas_tarifas` | Tarifas por cancha, día y horario |
| `mantenimientos` | Mantenimientos programados |
| `instalaciones_reserva_pagos` | Pagos de reservas |
| `instalaciones_abonos` | Monederos/abonos de clientes |
| `abono_movimientos` | Movimientos del monedero |
| `instalaciones_paquetes` | Paquetes de horas prepagados |
| `instalaciones_entradas` | Entradas vendidas |
| `instalaciones_entradas_tarifas` | Tarifas de entrada |
| `auditoria` | Log de auditoría |
| `notificaciones` | Notificaciones del sistema |

---

## Apéndice B: Estados del Sistema

### Reservas

| Estado | Descripción |
|---|---|
| `PENDIENTE` | Reserva creada, pendiente de confirmación |
| `CONFIRMADA` | Reserva confirmada (manual o por pago completo) |
| `COMPLETADA` | Servicio prestado |
| `CANCELADA` | Reserva cancelada (con motivo) |

### Pagos

| Estado | Descripción |
|---|---|
| `PENDIENTE` | Pago registrado sin confirmar |
| `COMPLETADO` | Pago exitoso |
| `ANULADO` | Pago anulado (con devolución de monedero si aplica) |

### Estado de Pago de Reserva

| Estado | Descripción |
|---|---|
| `PENDIENTE` | Sin pagos |
| `PARCIAL` | Pago parcial registrado |
| `PAGADO` | Totalmente pagado |

### Entradas

| Estado | Descripción |
|---|---|
| `VENDIDA` | Entrada vendida, no usada |
| `USADA` | Entrada utilizada (ingreso registrado) |
| `ANULADA` | Entrada anulada |

### Mantenimientos

| Estado | Descripción |
|---|---|
| `PROGRAMADO` | Mantenimiento programado |
| `EN_PROGRESO` | En ejecución |
| `COMPLETADO` | Finalizado |
| `CANCELADO` | Cancelado |

---

*Documento generado el 8 de febrero de 2026 — DigiSports Arena v1.0.0*
