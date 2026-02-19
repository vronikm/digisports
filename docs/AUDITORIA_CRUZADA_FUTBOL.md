# 🔍 AUDITORÍA CRUZADA COMPLETA — Módulo Fútbol

> **Fecha:** Generado automáticamente  
> **Alcance:** 22 controladores en `app/controllers/futbol/` · 23 vistas en `app/views/futbol/`  
> **Objetivo:** Detectar endpoints inexistentes, vistas faltantes, variables mal nombradas, incompatibilidades HTTP y enlaces rotos.

---

## ÍNDICE

1. [Resumen Ejecutivo](#1-resumen-ejecutivo)
2. [Vistas Faltantes (Controller renderiza vista que no existe)](#2-vistas-faltantes)
3. [Métodos Inexistentes en Controladores (Vista llama a método que no existe)](#3-métodos-inexistentes)
4. [Variables/Claves Incorrectas en Vistas](#4-variables-incorrectas)
5. [Incompatibilidades de Método HTTP (GET vs POST)](#5-incompatibilidades-http)
6. [Enlaces de Dashboard a Endpoints POST-only](#6-enlaces-dashboard)
7. [Detalle por Controlador/Vista](#7-detalle-por-controlador)
8. [Estadísticas Globales](#8-estadísticas)
9. [Recomendaciones de Corrección](#9-recomendaciones)

---

## 1. Resumen Ejecutivo

| Métrica | Valor |
|---|---|
| Total controladores | 22 |
| Total vistas (archivos PHP) | 23 |
| Métodos públicos totales en controladores | ~78 |
| Vistas faltantes (render a archivo inexistente) | **2** |
| Métodos inexistentes referenciados desde vistas | **35** |
| Variables/claves con nombre incorrecto en vistas | **~45** |
| Incompatibilidades HTTP (GET↔POST) | **7** |
| Controladores 100% consistentes con su vista | **5** (Entrenador, Grupo, Horario, Periodo, Sede) |
| Controladores con problemas CRÍTICOS | **10** |

**Veredicto:** El módulo de fútbol tiene un **desacoplamiento severo** entre controladores y vistas. La mayoría de las vistas fueron escritas asumiendo un API de controlador que no coincide con la implementación real. Esto resulta en funcionalidades que aparecen en la UI pero fallarían al ejecutarse.

---

## 2. Vistas Faltantes

Controladores que llaman `renderModule()` apuntando a archivos de vista que **NO existen** en disco.

| Controlador | Método | Vista esperada | Archivo esperado |
|---|---|---|---|
| `AsistenciaController` | `reporte()` | `futbol/asistencia/reporte` | `app/views/futbol/asistencia/reporte.php` ❌ |
| `ComprobanteController` | `imprimir()` | `futbol/comprobantes/imprimir` | `app/views/futbol/comprobantes/imprimir.php` ❌ |

**Impacto:** Llamar a estos endpoints genera un error fatal (archivo no encontrado).

---

## 3. Métodos Inexistentes en Controladores

Métodos referenciados por las vistas vía `url('futbol', ...)` que **NO existen** en el controlador correspondiente.

### Tabla completa

| Controlador | Método faltante | Referenciado desde vista | Tipo esperado |
|---|---|---|---|
| `AlumnoController` | `ver()` | `alumnos/index.php` | Página o JSON |
| `AsistenciaController` | `porGrupo()` | `asistencia/index.php` | JSON |
| `BecaController` | `guardar()` | `becas/index.php` | POST JSON |
| `BecaController` | `guardarAsignacion()` | `becas/index.php` | POST JSON |
| `BecaController` | `eliminarAsignacion()` | `becas/index.php` | GET JSON |
| `BecaController` | `toggleEstado()` | `becas/index.php` | POST JSON |
| `CampoFichaController` | `reordenar()` | `campoficha/index.php` | POST JSON |
| `CanchaController` | `crear()` | `canchas/index.php` | POST JSON |
| `CanchaController` | `editar()` | `canchas/index.php` | POST JSON |
| `CanchaController` | `eliminar()` | `canchas/index.php` | GET JSON |
| `CanchaController` | `cambiarEstado()` | `canchas/index.php` | POST JSON |
| `ComprobanteController` | `guardar()` | `comprobantes/index.php` | POST JSON |
| `EgresoController` | `eliminar()` | `egresos/index.php` | GET JSON |
| `EgresoController` | `listar()` | `egresos/index.php` | GET JSON |
| `EvaluacionController` | `editar()` | `evaluaciones/index.php` | POST JSON |
| `EvaluacionController` | `buscarAlumno()` | `evaluaciones/index.php` | GET JSON |
| `EvaluacionController` | `listar()` | `evaluaciones/index.php` | GET JSON |
| `InscripcionController` | `editar()` | `inscripciones/index.php` | POST JSON |
| `InscripcionController` | `eliminar()` | `inscripciones/index.php` | GET JSON |
| `InscripcionController` | `buscarAlumno()` | `inscripciones/index.php` | GET JSON |
| `InscripcionController` | `listar()` | `inscripciones/index.php` | GET JSON |
| `MoraController` | `registrarPago()` | `mora/index.php` | POST JSON |
| `NotificacionController` | `guardar()` | `notificaciones/index.php` | POST JSON |
| `NotificacionController` | `ver()` | `notificaciones/index.php` | GET JSON |
| `PagoController` | `eliminar()` | `pagos/index.php` | GET JSON |
| `PagoController` | `comprobante()` | `pagos/index.php` | GET (página/descarga) |
| `PagoController` | `listar()` | `pagos/index.php` | GET JSON |
| `ReporteController` | `generar()` | `reportes/index.php` | GET/POST JSON |
| `TorneoController` | `guardar()` | `torneos/index.php` | POST JSON |
| `TorneoController` | `confirmarJugador()` | `torneos/convocatoria.php` | POST JSON |

**Total: 29 métodos inexistentes referenciados desde vistas.**

### Caso especial: CanchaController (READ-ONLY)

El controlador `CanchaController` solo implementa `index()` (lectura). Sin embargo, la vista `canchas/index.php` contiene un modal CRUD completo con botones para crear, editar, eliminar y cambiar estado. **Toda la funcionalidad CRUD de canchas es inoperante.**

---

## 4. Variables/Claves Incorrectas en Vistas

### 4.1 `asistencia/index.php`

| Lo que usa la vista | Lo que provee el controlador | Corrección |
|---|---|---|
| `$asistencias` | `$alumnos` | Renombrar a `$alumnos` |
| `$fecha_actual` | `$fecha` | Renombrar a `$fecha` |
| `$grupo_seleccionado` | `$grupoId` | Renombrar a `$grupoId` |
| `$sedes` (iterado) | NO se pasa a la vista | Agregar `$sedes` al controlador |
| `$sede['id']` | — | Debería ser `$sede['sed_sede_id']` |
| `$sede['nombre']` | — | Debería ser `$sede['sed_nombre']` |
| `$grupo['id']` | `$grupo['fgr_grupo_id']` | Corregir clave |
| `$grupo['nombre']` | `$grupo['fgr_nombre']` | Corregir clave |

### 4.2 `becas/index.php`

| Lo que usa la vista | Lo que provee el controlador | Corrección |
|---|---|---|
| `$beca['fbc_id']` | `$beca['fbc_beca_id']` | Corregir clave |
| `$beca['total_asignados']` | `$beca['total_asignaciones']` | Corregir clave |
| `$asig['alumno_nombre']` | Nombre construido desde `alu_nombres`+`alu_apellidos` | Ajustar consulta o vista |
| `$asig['beca_nombre']` | No proporcionado con ese alias | Agregar alias en query |
| `$asig['beca_tipo']` | No proporcionado con ese alias | Agregar alias en query |
| `$asig['beca_valor']` | No proporcionado con ese alias | Agregar alias en query |
| `$asig['fba_id']` | `$asig['fba_asignacion_id']` | Corregir clave |
| `$asig['alumno_id']` | `$asig['alu_alumno_id']` | Corregir clave |
| `$asig['fbc_id']` | `$asig['fbc_beca_id']` | Corregir clave |
| `$alumno['id']` | `$alumno['alu_alumno_id']` | Corregir clave |
| `$alumno['nombre_completo']` | Construir desde `alu_nombres`+`alu_apellidos` | Ajustar query o vista |
| `$alumno['nombre']` | `$alumno['alu_nombres']` | Corregir clave |

### 4.3 `comprobantes/index.php`

| Lo que usa la vista | Lo que provee el controlador | Corrección |
|---|---|---|
| `$comp['fcm_id']` | `$comp['fcm_comprobante_id']` | Corregir clave |
| `$comp['alumno_nombre']` | No aliasado (debe construirse) | Agregar alias en query |

### 4.4 `configuracion/index.php`

| Lo que usa la vista | Lo que provee el controlador | Corrección |
|---|---|---|
| Prefijo `fco_` en todas las claves | Prefijo `fcg_` (tabla real) | Cambiar `fco_` → `fcg_` en toda la vista |
| `$configuraciones` como array agrupado (`$grupo => $configs`) | Array plano sin agrupar | Agrupar en controlador o ajustar vista |
| `$c['fco_tipo']` | `$c['fcg_tipo']` | Corregir prefijo |
| `$c['fco_clave']` | `$c['fcg_clave']` | Corregir prefijo |
| `$c['fco_valor']` | `$c['fcg_valor']` | Corregir prefijo |
| `$c['fco_descripcion']` | `$c['fcg_descripcion']` | Corregir prefijo |
| `$c['fco_config_id']` | `$c['fcg_config_id']` | Corregir prefijo |
| `$c['fco_opciones']` | `$c['fcg_opciones']` | Corregir prefijo |

### 4.5 `inscripciones/index.php`

| Lo que usa la vista | Lo que provee el controlador | Corrección |
|---|---|---|
| `$insc['fin_id']` | `$insc['fin_inscripcion_id']` | Corregir clave |

### 4.6 `mora/index.php`

| Lo que usa la vista | Lo que provee el controlador | Corrección |
|---|---|---|
| `$moroso['alumno_id']` | `$moroso['alu_alumno_id']` | Corregir clave |

### 4.7 `torneos/index.php`

| Lo que usa la vista | Lo que provee el controlador | Corrección |
|---|---|---|
| `$torneo['fto_id']` | `$torneo['fto_torneo_id']` | Corregir clave |

### 4.8 `alumnos/index.php`

| Lo que usa la vista | Lo que provee el controlador | Corrección |
|---|---|---|
| `$grupos` (filtro de grupos) | NO se pasa a la vista | Agregar consulta y variable en controlador |

---

## 5. Incompatibilidades de Método HTTP

Casos donde la vista llama al endpoint con un método HTTP diferente al que espera el controlador.

| Vista | URL llamada | Método en vista | Método esperado por controlador |
|---|---|---|---|
| `comprobantes/index.php` | `url('futbol','comprobante','anular')` | GET (redirect `window.location`) | POST (`$_SERVER['REQUEST_METHOD'] === 'POST'`) |
| `mora/index.php` | `url('futbol','mora','enviarNotificacion')` | GET (redirect) | POST |
| `mora/index.php` | `url('futbol','mora','suspender')` | GET (redirect) | POST |
| `mora/index.php` | `url('futbol','mora','historial')` | GET (redirect página) | GET JSON (devuelve `jsonResponse`) |
| `notificaciones/index.php` | `url('futbol','notificacion','reenviar')` | GET (redirect) | POST |
| `pagos/index.php` | `url('futbol','pago','anular')` | POST (`$.post`) | GET (`$this->get('id')`) |
| `categorias/index.php` | `url('futbol','categoria','habilidades')` | GET (link `<a href>` a página) | GET JSON (`jsonResponse`) |

**Impacto:** Estas llamadas fallan silenciosamente o devuelven errores. El usuario ve un error o una página en blanco.

---

## 6. Enlaces de Dashboard a Endpoints POST-only

La vista `dashboard/index.php` contiene tarjetas/botones con enlaces directos a acciones que son POST-only en sus controladores:

| Enlace en dashboard | Controlador:método | Problema |
|---|---|---|
| `url('futbol','alumno','crear')` | `AlumnoController::crear()` | Solo acepta POST, devuelve JSON. No es una página. |

**Corrección:** El dashboard debería enlazar a `url('futbol','alumno','index')` y abrir el modal de creación desde ahí, o el controlador debe soportar GET para mostrar un formulario.

---

## 7. Detalle por Controlador/Vista

### ✅ Controladores SIN problemas detectados

| Controlador | Métodos | Estado |
|---|---|---|
| `EntrenadorController` | `index`, `crear`, `editar`, `eliminar` | ✅ Consistente |
| `GrupoController` | `index`, `crear`, `editar`, `eliminar` | ✅ Consistente |
| `HorarioController` | `index`, `crear`, `editar`, `eliminar` | ✅ Consistente |
| `PeriodoController` | `index`, `crear`, `editar`, `eliminar` | ✅ Consistente |
| `SedeController` | `index`, `crear`, `editar`, `eliminar`, `seleccionar`, `listar`, `resumenFinanciero` | ✅ Consistente |

### ⚠️ Controladores con problemas MENORES

| Controlador | Problemas |
|---|---|
| `DashboardController` | 1 enlace a endpoint POST-only |
| `AlumnoController` | 1 método faltante (`ver`), 1 variable faltante (`$grupos`) |

### ❌ Controladores con problemas GRAVES

| Controlador | Métodos faltantes | Variables mal nombradas | HTTP mismatch |
|---|---|---|---|
| `AsistenciaController` | 1 (`porGrupo`) + 1 vista faltante | 8 claves incorrectas | — |
| `BecaController` | 4 (`guardar`, `guardarAsignacion`, `eliminarAsignacion`, `toggleEstado`) | 12 claves incorrectas | — |
| `CampoFichaController` | 1 (`reordenar`) | — | — |
| `CanchaController` | 4 (`crear`, `editar`, `eliminar`, `cambiarEstado`) — CRUD completo | — | — |
| `CategoriaController` | — | — | 1 (JSON servido como página) |
| `ComprobanteController` | 1 (`guardar`) + 1 vista faltante | 2 claves incorrectas | 1 (GET→POST) |
| `ConfiguracionController` | — | 8 prefijos incorrectos + estructura | — |
| `EgresoController` | 2 (`eliminar`, `listar`) | — | — |
| `EvaluacionController` | 3 (`editar`, `buscarAlumno`, `listar`) | — | — |
| `InscripcionController` | 4 (`editar`, `eliminar`, `buscarAlumno`, `listar`) | 1 clave incorrecta | — |
| `MoraController` | 1 (`registrarPago`) | 1 clave incorrecta | 3 |
| `NotificacionController` | 2 (`guardar`, `ver`) | — | 1 |
| `PagoController` | 3 (`eliminar`, `comprobante`, `listar`) | — | 1 |
| `ReporteController` | 1 (`generar`) | — | — |
| `TorneoController` | 2 (`guardar`, `confirmarJugador`) | 1 clave incorrecta | — |

---

## 8. Estadísticas Globales

```
┌─────────────────────────────────────────────┬───────┐
│ Métrica                                     │ Total │
├─────────────────────────────────────────────┼───────┤
│ Controladores analizados                    │    22 │
│ Vistas analizadas                           │    23 │
│ Controladores 100% OK                       │     5 │
│ Controladores con problemas menores         │     2 │
│ Controladores con problemas graves          │    15 │
│                                             │       │
│ Vistas faltantes (render a archivo no exist)│     2 │
│ Métodos inexistentes referenciados          │    29 │
│ Claves/variables mal nombradas              │   ~45 │
│ Incompatibilidades HTTP                     │     7 │
│                                             │       │
│ Funcionalidades UI completamente inoperantes│     6 │
│   - CRUD completo de Canchas                │       │
│   - Reordenar campos de ficha               │       │
│   - Asignación/revocación de becas (parcial)│       │
│   - Búsqueda de alumno en inscripciones     │       │
│   - Búsqueda de alumno en evaluaciones      │       │
│   - Registro de pago desde mora             │       │
└─────────────────────────────────────────────┴───────┘
```

---

## 9. Recomendaciones de Corrección

### 🔴 Prioridad CRÍTICA (funcionalidad rota, errores visibles al usuario)

1. **Crear métodos CRUD en `CanchaController`**: `crear()`, `editar()`, `eliminar()`, `cambiarEstado()`. Sin ellos, toda la gestión de canchas es una pantalla muerta.

2. **Crear las 2 vistas faltantes:**
   - `app/views/futbol/asistencia/reporte.php`
   - `app/views/futbol/comprobantes/imprimir.php`

3. **Corregir prefijos en `configuracion/index.php`**: Cambiar todas las referencias `fco_` → `fcg_` y ajustar la estructura de datos (plana vs agrupada).

4. **Corregir claves en `asistencia/index.php`**: Alinear nombres de variables (`$asistencias`→`$alumnos`, `$fecha_actual`→`$fecha`, etc.) y claves de array (`$grupo['id']`→`$grupo['fgr_grupo_id']`).

### 🟠 Prioridad ALTA (funcionalidad parcialmente rota)

5. **Implementar métodos faltantes de alta frecuencia:**
   - `BecaController::guardar()`, `guardarAsignacion()`, `eliminarAsignacion()`, `toggleEstado()`
   - `InscripcionController::editar()`, `eliminar()`, `buscarAlumno()`
   - `EvaluacionController::editar()`, `buscarAlumno()`
   - `PagoController::eliminar()`, `comprobante()`
   - `NotificacionController::guardar()` (o renombrar vista a usar `crear`)
   - `MoraController::registrarPago()`
   - `TorneoController::guardar()` (o cambiar vista a usar `crear`/`editar`)

6. **Corregir claves de ID en vistas:**
   - `becas/index.php`: `fbc_id` → `fbc_beca_id`, `fba_id` → `fba_asignacion_id`
   - `comprobantes/index.php`: `fcm_id` → `fcm_comprobante_id`
   - `inscripciones/index.php`: `fin_id` → `fin_inscripcion_id`
   - `torneos/index.php`: `fto_id` → `fto_torneo_id`

7. **Corregir incompatibilidades HTTP:**
   - `comprobantes/index.php`: Cambiar `window.location` a `$.post` para `anular`
   - `mora/index.php`: Cambiar redirects a `$.post` para `enviarNotificacion` y `suspender`
   - `notificaciones/index.php`: Cambiar redirect a `$.post` para `reenviar`
   - `pagos/index.php`: Cambiar `anular()` del controlador a aceptar POST, o cambiar vista a GET

### 🟡 Prioridad MEDIA (funcionalidad menor o UX mejorable)

8. **Implementar métodos `listar()` donde se referencian** (Egresos, Evaluaciones, Inscripciones, Pagos) — probablemente para DataTables server-side.

9. **Corregir enlace de dashboard** de `url('futbol','alumno','crear')` a `url('futbol','alumno','index')`.

10. **Cambiar enlace de habilidades en `categorias/index.php`** de `<a href>` a llamada AJAX, ya que `habilidades()` devuelve JSON.

11. **Agregar variable `$grupos` en `AlumnoController::index()`** para que el filtro de la vista funcione.

12. **Agregar variable `$sedes` en `AsistenciaController::index()`** para el selector de sede.

### 🟢 Prioridad BAJA (mejoras opcionales)

13. **Crear método `AlumnoController::ver()`** para ficha individual del alumno.

14. **Crear método `CampoFichaController::reordenar()`** para drag-and-drop de campos.

15. **Crear método `TorneoController::confirmarJugador()`** para la convocatoria.

16. **Crear método `ReporteController::generar()`** como unificador de los 3 reportes existentes.

---

## Apéndice A: Mapeo Completo Controlador → Métodos

```
AlumnoController        : index, crear, editar, eliminar, buscar
AsistenciaController    : index, guardar, reporte
BecaController          : index, crear, editar, eliminar, asignar, revocar
CampoFichaController    : index, crear, editar, eliminar
CanchaController        : index  (SOLO LECTURA)
CategoriaController     : index, crear, editar, eliminar, habilidades, crearHabilidad
ComprobanteController   : index, crear, generar, anular, ver, imprimir
ConfiguracionController : index, guardar
DashboardController     : index
EgresoController        : index, crear, editar, anular
EntrenadorController    : index, crear, editar, eliminar
EvaluacionController    : index, crear, guardar, eliminar, detalle
GrupoController         : index, crear, editar, eliminar
HorarioController       : index, crear, editar, eliminar
InscripcionController   : index, crear, cancelar
MoraController          : index, enviarNotificacion, suspender, historial
NotificacionController  : index, crear, reenviar, eliminar
PagoController          : index, crear, editar, anular, buscarInscripciones
PeriodoController       : index, crear, editar, eliminar
ReporteController       : index, financiero, asistencia, inscripciones
SedeController          : index, crear, editar, eliminar, seleccionar, listar, resumenFinanciero
TorneoController        : index, crear, editar, eliminar, convocatoria, agregarJugador, quitarJugador
```

## Apéndice B: Archivos de Vista

```
app/views/futbol/
├── alumnos/index.php
├── asistencia/index.php
├── becas/index.php
├── campoficha/index.php
├── canchas/index.php
├── categorias/index.php
├── comprobantes/index.php
├── configuracion/index.php
├── dashboard/index.php
├── egresos/index.php
├── entrenadores/index.php
├── evaluaciones/index.php
├── grupos/index.php
├── horario/index.php
├── inscripciones/index.php
├── mora/index.php
├── notificaciones/index.php
├── pagos/index.php
├── periodos/index.php
├── reportes/index.php
├── sedes/index.php
├── torneos/index.php
└── torneos/convocatoria.php
```

---

*Fin del informe de auditoría cruzada.*
