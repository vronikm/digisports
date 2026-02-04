# Módulo de Seguridad - DigiSports

## Resumen

El módulo de **Seguridad** es el centro de administración del sistema DigiSports. Permite gestionar todos los aspectos de seguridad, usuarios, roles, módulos y tenants desde una interfaz centralizada.

## Estructura de Archivos

```
app/
├── controllers/
│   └── seguridad/
│       ├── DashboardController.php    # Panel principal con KPIs y alertas
│       ├── UsuarioController.php      # CRUD de usuarios
│       ├── RolController.php          # Gestión de roles y permisos
│       ├── ModuloController.php       # Administración de módulos e iconos
│       ├── TenantController.php       # Gestión de tenants/organizaciones
│       ├── AsignacionController.php   # Asignación de módulos a tenants
│       └── PlanController.php         # Planes de suscripción
│
└── views/
    └── seguridad/
        ├── dashboard/
        │   └── index.php              # Dashboard de seguridad
        │
        ├── usuario/
        │   ├── index.php              # Lista de usuarios
        │   └── form.php               # Formulario crear/editar
        │
        ├── rol/
        │   ├── index.php              # Lista de roles
        │   ├── form.php               # Formulario crear/editar
        │   └── permisos.php           # Matriz de permisos
        │
        ├── modulo/
        │   ├── index.php              # Lista de módulos
        │   ├── form.php               # Formulario con selector iconos/colores
        │   └── iconos.php             # Galería de iconos
        │
        ├── tenant/
        │   ├── index.php              # Lista de tenants
        │   ├── form.php               # Formulario crear/editar
        │   ├── ver.php                # Detalle del tenant
        │   └── suscripciones.php      # Gestión de suscripciones
        │
        ├── plan/
        │   ├── index.php              # Lista de planes
        │   ├── form.php               # Formulario crear/editar
        │   └── comparativa.php        # Tabla comparativa
        │
        └── asignacion/
            ├── modulos.php            # Asignar módulos a tenant
            └── masiva.php             # Asignación masiva
```

## Funcionalidades

### 1. Dashboard de Seguridad
- KPIs del sistema (tenants, usuarios, módulos, roles)
- Alertas de seguridad (suscripciones por vencer, intentos de login)
- Actividad reciente del sistema
- Accesos rápidos a todas las secciones

### 2. Gestión de Usuarios
- CRUD completo de usuarios
- Asignación de tenant y rol
- Reset de contraseña
- Bloqueo/desbloqueo de cuentas
- Configuración 2FA

### 3. Gestión de Roles
- Crear roles personalizados
- Matriz de permisos por módulo/acción
- Niveles de jerarquía (1-5)
- Plantillas predefinidas (SuperAdmin, Admin, Operador, Consulta)

### 4. Gestión de Módulos
- CRUD de módulos del sistema
- **Selector de iconos** con 50+ iconos FontAwesome organizados por categoría:
  - Deportes (fútbol, basket, natación, etc.)
  - Negocios (facturación, reportes, etc.)
  - Personas (usuarios, clientes, etc.)
  - General (configuración, dashboard, etc.)
- **Paleta de colores** con 16 colores predefinidos
- Vista previa en tiempo real
- Duplicar módulos existentes

### 5. Gestión de Tenants
- CRUD de organizaciones/empresas
- Asignación de plan de suscripción
- Control de límite de usuarios
- Fechas de vigencia
- Estados: Activo, Suspendido, Inactivo

### 6. Gestión de Suscripciones
- Panel de suscripciones por vencer
- Lista de suscripciones vencidas
- Renovación de suscripciones
- Notificaciones masivas
- Suspensión automática

### 7. Planes de Suscripción
- CRUD de planes
- Definir precios mensual/anual
- Límites de usuarios y almacenamiento
- Módulos incluidos
- Características destacadas
- Vista comparativa de planes

### 8. Asignación de Módulos
- Asignar módulos individualmente por tenant
- Personalizar nombre, icono y color por tenant
- Asignación masiva a múltiples tenants
- Acciones: Agregar, Reemplazar, Quitar

## Iconos Disponibles

### Deportes
- `fas fa-futbol` - Fútbol
- `fas fa-basketball-ball` - Basketball
- `fas fa-swimming-pool` - Natación
- `fas fa-running` - Running/Atletismo
- `fas fa-chess` - Ajedrez
- `fas fa-dumbbell` - Gimnasio
- `fas fa-table-tennis` - Tenis de mesa
- `fas fa-volleyball-ball` - Voleibol
- Y más...

### Negocios
- `fas fa-file-invoice-dollar` - Facturación
- `fas fa-chart-bar` - Reportes
- `fas fa-cash-register` - Caja
- `fas fa-shopping-cart` - Tienda
- `fas fa-warehouse` - Inventario
- Y más...

### Personas
- `fas fa-users` - Usuarios
- `fas fa-user-tie` - Clientes
- `fas fa-user-shield` - Seguridad
- `fas fa-user-graduate` - Estudiantes
- Y más...

### General
- `fas fa-tachometer-alt` - Dashboard
- `fas fa-cog` - Configuración
- `fas fa-building` - Instalaciones
- `fas fa-calendar-alt` - Reservas
- Y más...

## Colores Disponibles

| Color | Hex | Uso Sugerido |
|-------|-----|--------------|
| Verde | #22C55E | Éxito, Activo |
| Azul | #3B82F6 | Principal |
| Naranja | #F97316 | Atención |
| Púrpura | #8B5CF6 | Premium |
| Rosa | #EC4899 | Femenino |
| Teal | #14B8A6 | Salud |
| Rojo | #EF4444 | Alerta |
| Ámbar | #F59E0B | Advertencia |
| Cyan | #06B6D4 | Información |
| Índigo | #6366F1 | Creatividad |
| Lima | #84CC16 | Natural |
| Esmeralda | #10B981 | Finanzas |
| Fucsia | #D946EF | Moderno |
| Sky | #0EA5E9 | Tecnología |
| Violeta | #A855F7 | Elegante |
| Gris | #6B7280 | Neutral |

## Instalación

1. Ejecutar el script SQL:
   ```sql
   SOURCE database/seguridad_module.sql;
   ```

2. El módulo ya está configurado en `config/app.php`

3. Acceder desde el Hub: **Seguridad**

## URLs del Módulo

| Sección | URL |
|---------|-----|
| Dashboard | `/seguridad` |
| Usuarios | `/seguridad/usuario` |
| Roles | `/seguridad/rol` |
| Módulos | `/seguridad/modulo` |
| Iconos | `/seguridad/modulo/iconos` |
| Tenants | `/seguridad/tenant` |
| Suscripciones | `/seguridad/tenant/suscripciones` |
| Planes | `/seguridad/plan` |
| Comparativa | `/seguridad/plan/comparativa` |
| Asignación | `/seguridad/asignacion/modulos` |
| Asignación Masiva | `/seguridad/asignacion/masiva` |

## Permisos Requeridos

Para acceder al módulo de Seguridad se requiere rol de nivel 4 o superior (Administrador o Super Admin).

## Dependencias

- AdminLTE 3.x
- FontAwesome 5.x
- Bootstrap 4.x
- jQuery 3.x
