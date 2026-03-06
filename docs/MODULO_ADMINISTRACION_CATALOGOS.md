# ✅ MÓDULO: Administración de Catálogos del Sistema

**Estado:** ✅ COMPLETADO Y LISTO PARA USAR
**Versión:** 1.0.0  
**Fecha:** 2024  
**Proyecto:** DigiSports

---

## 📌 DESCRIPCIÓN

Módulo completo para la administración centralizada de catálogos del sistema. Permite crear, editar, eliminar y gestionar tablas de catálogos (grupos) y sus ítems asociados.

### Características Principales:
- ✅ Gestión CRUD completa de catálogos
- ✅ Validaciones multi-capa (frontend + backend)
- ✅ Interfaz amigable con SweetAlert2
- ✅ Protección CSRF en todas las operaciones
- ✅ Arquitectura MVC limpia y mantenible
- ✅ JSON API para integraciones
- ✅ Búsqueda y filtrados dinámicos

---

## 📦 ARCHIVOS ENTREGADOS

### 1. Modelos (2 archivos)

**Ubicación:** `app/models/seguridad/`

#### 📁 SeguridadTablaModel.php (290 líneas)
Maneja la lógica de negocio para **catálogos (grupos)**

**Métodos Principales:**
- `listar($filtro)` - Lista todos los catálogos con búsqueda
- `obtener($id)` - Obtiene un catálogo específico
- `crear($datos)` - Crea nuevo catálogo con validaciones
- `actualizar($id, $datos)` - Actualiza catálogo existente
- `eliminar($id)` - Elimina catálogo (valida que no tenga ítems)
- `validarDatos($datos)` - Valida campos requeridos y formato
- `contarItems($id)` - Cuenta ítems asociados

**Validaciones:**
- Nombre único a nivel global
- Máximo 255 caracteres
- Descripción máximo 500 caracteres

---

#### 📁 SeguridadTablaCatalogoModel.php (320 líneas)
Maneja la lógica de negocio para **ítems de catálogos**

**Métodos Principales:**
- `listarPorGrupo($grupoId, $filtro)` - Lista ítems de un grupo
- `obtener($id)` - Obtiene un ítem específico
- `crear($datos)` - Crea nuevo ítem
- `actualizar($id, $datos)` - Actualiza ítem existente
- `eliminar($id)` - Elimina ítem
- `validarDatos($datos)` - Valida campos
- `codigoExisteEnGrupo($codigo, $grupoId)` - Valida unicidad de código
- `obtenerProximaOrden($grupoId)` - Calcula siguiente número de orden

**Validaciones:**
- Código único dentro del grupo
- Todos los campos requeridos
- Máximo 255 caracteres en valor y etiqueta
- Máximo 100 caracteres en código

---

### 2. Controladores (2 archivos)

**Ubicación:** `app/controllers/seguridad/`

#### 🎮 SeguridadTablaController.php (190 líneas)
Maneja las peticiones HTTP para **gestión de catálogos**

**Métodos/Rutas:**
```
GET  /seguridad/seguridad_tabla/index       → Listado de catálogos
GET  /seguridad/seguridad_tabla/editar      → Formulario crear/editar
POST /seguridad/seguridad_tabla/crear       → Crear nuevo catálogo
POST /seguridad/seguridad_tabla/actualizar  → Actualizar catálogo
POST /seguridad/seguridad_tabla/eliminar    → Eliminar catálogo
GET  /seguridad/seguridad_tabla/items       → Ver ítems del catálogo
```

**Características:**
- Validación CSRF en todas las operaciones POST
- Respuestas JSON estructuradas
- Manejo de errores con mensajes claros
- Acceso controlado (stub para expand con permisos)

---

#### 🎮 SeguridadTablaCatalogoController.php (210 líneas)
Maneja las peticiones HTTP para **gestión de ítems**

**Métodos/Rutas:**
```
GET  /seguridad/seguridad_tabla_catalogo/listar      → Listar ítems (AJAX)
GET  /seguridad/seguridad_tabla_catalogo/editar      → Formulario crear/editar
POST /seguridad/seguridad_tabla_catalogo/crear       → Crear ítem
POST /seguridad/seguridad_tabla_catalogo/actualizar  → Actualizar ítem
POST /seguridad/seguridad_tabla_catalogo/eliminar    → Eliminar ítem
```

**Características:**
- API JSON completa para AJAX
- Devuelve información del grupo con los ítems
- Validación de dependencias
- Paginación y búsqueda

---

### 3. Vistas (4 archivos)

**Ubicación:** `app/views/seguridad/catalogos/`

#### 🎨 index.php (170 líneas)
**Listado de Catálogos**

**Contenido:**
- Tabla con todos los catálogos
- Búsqueda por nombre
- Contador de ítems por catálogo
- Botones: Crear, Editar, Ver Ítems, Eliminar
- SweetAlert2 para confirmación de eliminación
- Toast para feedback

**Funciones JavaScript:**
- `eliminarCatalogo(id, nombre)` - Elimina con confirmación
- `cargarItem($id)` - Abre vista de ítems

---

#### 🎨 editar.php (120 líneas)
**Crear/Editar Catálogo**

**Campos del Formulario:**
- `st_nombre` (required, max 255) - Nombre del catálogo
- `st_descripcion` (optional, max 500) - Descripción
- `st_activo` (toggle) - Estado del catálogo

**Funciones:**
- Detecta si es crear o editar basado en `st_id`
- Enruta a `crear()` o `actualizar()` según corresponda
- SweetAlert2 para confirmación antes de guardar
- Toast de éxito y redirección

---

#### 🎨 items.php (200 líneas)
**Listado de Ítems del Catálogo**

**Contenido:**
- Tabla dinámica cargada vía AJAX
- Carga desde endpoint `listar()`
- Muestra: ID, Código, Valor, Etiqueta, Orden, Estado
- Botones: Crear, Editar, Eliminar

**Funciones JavaScript:**
- `cargarItems()` - Carga ítems vía AJAX
- `eliminarItem(id, etiqueta)` - Elimina con confirmación
- Refresh automático tras operaciones

---

#### 🎨 item_editar.php (130 líneas)
**Crear/Editar Ítem de Catálogo**

**Campos del Formulario:**
- `stc_codigo` (required, max 100) - Código único
- `stc_valor` (required, max 255) - Valor para forms
- `stc_etiqueta` (required, max 255) - Etiqueta visible
- `stc_orden` (numeric) - Posición en listado
- `stc_activo` (toggle) - Estado activo/inactivo

**Funciones:**
- Detecta crear vs editar
- Enruta a `crear()` o `actualizar()` 
- SweetAlert2 confirmación
- Toast de éxito
- Redirección a items del grupo

---

### 4. Base de Datos

**Ubicación:** `database/modulo_administracion_catalogos.sql`

**Tablas (si necesitas crearlas):**
- `seguridad_tabla` - Grupos de catálogos
- `seguridad_tabla_catalogo` - Ítems dentro de catálogos

**Datos Iniciales Incluidos:**
- 5 catálogos de ejemplo
- 20 ítems de ejemplo (tipos doc, estados, etc)

---

## 🚀 INSTALACIÓN

### Paso 1: Verificar Archivos
```bash
# Asegurate que todos los archivos existan:
ls -la app/models/seguridad/
ls -la app/controllers/seguridad/
ls -la app/views/seguridad/catalogos/
ls -la database/modulo_administracion_catalogos.sql
```

### Paso 2: Ejecutar Validador
```bash
# Abre en el navegador:
http://localhost/digisports/public/validacion_modulo_catalogos.php

# Debes ver: ✅ Módulo Instalado Correctamente (100%)
```

### Paso 3: Cargar Datos (Opcional)
Si deseas los datos de ejemplo:
```bash
# En MySQL:
mysql -u root digisports_core < database/modulo_administracion_catalogos.sql

# O manualmente en phpMyAdmin:
# Abrir el archivo y ejecutar el SQL
```

### Paso 4: Verificar en la Aplicación
```
1. Abre: http://localhost/digisports/public/
2. Login con usuario admin
3. Ve a: Seguridad > Administración de Catálogos
4. Deberías ver el listado de catálogos
```

---

## 💻 MODO DE USO

### Gestionar Catálogos

**Ver Listado:**
- Ve a Seguridad > Administración de Catálogos
- Busca por nombre en el filtro

**Crear Nuevo:**
1. Click en botón "Crear Catálogo"
2. Completa nombre y descripción
3. Click "Guardar"

**Editar Existente:**
1. Click en botón "Editar" en la fila
2. Modifica los campos
3. Click "Actualizar"

**Eliminar:**
1. Click en botón "Eliminar"
2. Confirma en el popup SweetAlert2
3. Se elimina automáticamente

**Ver Ítems:**
1. Click en botón "Ver Ítems"
2. Se muestra tabla de ítems asociados

---

### Gestionar Ítems

**Dentro de un Catálogo:**

**Crear Ítem:**
1. Click en botón "Crear Ítem"
2. Completa campos:
   - Código: identificador único
   - Valor: qué se guarda en BD
   - Etiqueta: qué ve el usuario
   - Orden: posición (10, 20, 30...)
3. Click "Guardar"

**Editar Ítem:**
1. Click en botón "Editar" de la fila
2. Modifica campos
3. Click "Actualizar"

**Eliminar Ítem:**
1. Click en botón "Eliminar"
2. Confirma
3. Se elimina automáticamente

---

## 🔧 ARQUITECTURA TÉCNICA

### Flujo de Datos

```
Usuario Interfaz HTML (vistas)
    ↓
JavaScript (SweetAlert2 + Fetch)
    ↓
Controladores (JSON responses)
    ↓
Modelos (validaciones + BD)
    ↓
MySQL Database
```

### Validaciones Implementadas

**Frontend:**
- HTML5 required, maxlength
- SweetAlert2 confirmación

**Backend (Modelos):**
- Campos requeridos
- Longitud máxima
- Unicidad de nombres/códigos
- Integración referencial (sin ítems antes de eliminar grupo)

---

## 📊 ESTRUCTURA DE DATOS

### Tabla: seguridad_tabla
```
st_id          → ID única
st_nombre      → Nombre del catálogo (UNIQUE)
st_descripcion → Información adicional
st_activo      → 1=activo, 0=inactivo
created_at     → Timestamp creación
updated_at     → Timestamp actualización
```

### Tabla: seguridad_tabla_catalogo
```
stc_id         → ID única
stc_tabla_id   → FK a seguridad_tabla (cascade delete)
stc_codigo     → Código único en el grupo
stc_valor      → Valor a guardar
stc_etiqueta   → Texto visible al usuario
stc_orden      → Orden de presentación
stc_activo     → 1=activo, 0=inactivo
created_at     → Timestamp creación
updated_at     → Timestamp actualización
```

---

## 🔐 SEGURIDAD

### Implementado:
- ✅ CSRF token validation (todos los POST)
- ✅ SQL Injection prevention (PDO prepared statements)
- ✅ XSS protection (escapeHtml en JS)
- ✅ Multi-tenant safe (puede expandirse)
- ✅ Permisos stubados para futuro acceso controlado

### No Incluido (Puede Agregarse):
- Role-based access control (expandir `usuarioTieneRol()`)
- Audit logging
- API key authentication

---

## 🐛 TROUBLESHOOTING

### Problema: "Módulo no encontrado"
**Causa:** Directorio `app/controllers/seguridad/` no existe

**Solución:**
```bash
mkdir -p app/controllers/seguridad
mkdir -p app/models/seguridad
mkdir -p app/views/seguridad/catalogos
```

---

### Problema: "Error de conexión a BD"
**Causa:** Datos de conexión incorrectos en `config/database.php`

**Verificar:**
```php
// En config/database.php
'host' => 'localhost',      // 127.0.0.1
'database' => 'digisports_core',
'username' => 'root',
'password' => ''
```

---

### Problema: "Nombre de catálogo duplicado"
**Causa:** Ya existe un catálogo con ese nombre

**Solución:** Usar nombre único o editar el existente

---

### Problema: "No puedo eliminar catálogo"
**Causa:** Todavía tiene ítems asociados

**Solución:** 
1. Ve a Ver Ítems
2. Elimina todos los ítems primero
3. Luego eliminá el catálogo

---

## 📈 PRÓXIMAS MEJORAS SUGERIDAS

1. **Permisos Role-Based:**
   - Implementar `usuarioTieneRol()` completamente
   - Agregar permisos: ver, crear, editar, eliminar

2. **Auditoría:**
   - Log de quién creó/modificó/eliminó
   - Historial de cambios

3. **Importar/Exportar:**
   - Exportar catálogos a CSV/Excel
   - Importar datos en lote

4. **Validaciones Avanzadas:**
   - Regex patterns para códigos
   - Valores predefinidos por tipo

5. **API REST Pública:**
   - Endpoint para acceder a catálogos
   - Caché para mejor performance

6. **Interfaz Mejorada:**
   - Drag-drop para reordenar ítems
   - Editor inline sin modal
   - Búsqueda con autocomplete

---

## ✅ CHECKLIST DE VALIDACIÓN

- [x] Todos los archivos creados
- [x] Modelos con CRUD completo
- [x] Controladores con JSON API
- [x] Vistas con SweetAlert2
- [x] Validaciones en ambos lados
- [x] CSRF protection
- [x] Manejo de errores
- [x] Documentación completa
- [x] Script de validación
- [x] Datos de ejemplo

---

## 📞 SOPORTE

Para problemas o preguntas:

1. **Ejecutar Validador:** `validacion_modulo_catalogos.php`
2. **Revisar Logs:** `storage/logs/`
3. **Verificar F12 Console** en navegador

---

## 📄 LICENCIA Y DERECHOS

DigiSports © 2024 - Todos los derechos reservados

---

**¡Módulo listo para usar! 🚀**
