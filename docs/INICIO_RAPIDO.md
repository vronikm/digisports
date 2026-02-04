# 🚀 INICIO RÁPIDO - PASO 2

**¿Dónde empezar?** Lee esto primero (5 minutos)

---

## ⚡ TL;DR (Muy Resumido)

**Lo que se hizo:** 2 controladores + 5 vistas + 5 tablas SQL para gestionar canchas/instalaciones.

**Cómo activarlo:**
1. Ejecutar SQL: `database/paso_2_instalaciones.sql`
2. Visitar: `http://localhost/digisports/public/instalaciones/cancha/index`

---

## 📋 ORDEN DE LECTURA

### Paso 1: Visión General (1 min)
Lee: `PASO_2_RESUMEN.md` (ejecutive summary)

### Paso 2: Importar Base de Datos (10 min)
Lee: `INSTRUCCIONES_IMPORTACION.md`
Luego ejecutar:
```bash
mysql -u root digisports_core < paso_2_instalaciones.sql
```

### Paso 3: Explorar Interfaz (5 min)
1. Login en: `http://localhost/digisports/public/`
2. Ir a: `http://localhost/digisports/public/instalaciones/cancha/index`
3. Probar: Crear → Editar → Ver Tarifas

### Paso 4: Entender Arquitectura (10 min)
Lee: `PASO_2_REFERENCIA.md` (documentación técnica)

### Paso 5: Configuración de Rutas (5 min)
Lee: `PASO_2_CONFIGURACION.php` (si necesitas entender las rutas)

---

## 🗂️ ESTRUCTURA DE ARCHIVOS

```
DigiSports/
├── app/controllers/instalaciones/
│   ├── CanchaController.php          ← Manejo de canchas
│   └── MantenimientoController.php   ← Manejo de mantenimiento
├── app/views/instalaciones/
│   ├── canchas/
│   │   ├── index.php                 ← Listado de canchas
│   │   ├── formulario.php            ← Crear/editar cancha
│   │   └── tarifas.php               ← Gestionar tarifas
│   └── mantenimientos/
│       ├── index.php                 ← Listado de mantenimientos
│       └── formulario.php            ← Crear/editar mantenimiento
├── database/
│   └── paso_2_instalaciones.sql      ← Tablas SQL
└── Documentación/
    ├── PASO_2_RESUMEN.md             ← Este resumen
    ├── PASO_2_REFERENCIA.md          ← Técnico detallado
    ├── INSTRUCCIONES_IMPORTACION.md  ← Guía BD
    ├── PASO_2_CONFIGURACION.php      ← Configuración
    └── INICIO_RAPIDO.md              ← Está leyendo esto
```

---

## 🎯 FUNCIONALIDADES

### Canchas (Espacios deportivos)
- ✅ Crear, leer, actualizar, eliminar (CRUD)
- ✅ Buscar por nombre/tipo
- ✅ Filtrar por estado (activo/inactivo)
- ✅ Ver reservas hoy
- ✅ Soft delete (no se borran, se marcan como inactivas)

### Tarifas (Precios por hora)
- ✅ Definir precio por día/hora (flexible)
- ✅ Editar tarifas inline
- ✅ Plantilla de horarios sugerida (mañana/tarde/noche)
- ✅ UNIQUE constraint (no duplicados)

### Mantenimiento
- ✅ Programar mantenimiento preventivo/correctivo
- ✅ Asignar responsable (técnico)
- ✅ Soporte para recurrencia
- ✅ Workflow de estados (Programado → En Progreso → Completado)
- ✅ Cambiar estado con dropdown

---

## 🔐 SEGURIDAD

- ✅ Multi-tenant (cada tenant solo ve sus datos)
- ✅ CSRF tokens en formularios
- ✅ Validación cliente + servidor
- ✅ Auditoría completa (quién, qué, cuándo, antes/después)
- ✅ Soft deletes (no se pierden datos)

---

## 💾 BASE DE DATOS

### Tablas creadas
```sql
canchas               -- Espacios deportivos
tarifas              -- Precios por hora/día
mantenimientos       -- Programación de mantenimiento
disponibilidad_canchas -- Cache de disponibilidad
eventos_canchas      -- Auditoría de eventos
```

### Vistas SQL
```sql
vw_tarifas_por_dia
vw_mantenimientos_pendientes
vw_estadisticas_canchas
```

---

## 🧪 PRIMERA PRUEBA (2 min)

1. **Abrir navegador:**
   ```
   http://localhost/digisports/public/instalaciones/cancha/index
   ```

2. **Deberías ver:**
   - Tabla vacía (sin canchas todavía)
   - Botón "Nueva Cancha"
   - Filtros (buscar, tipo, estado)

3. **Crear una cancha:**
   - Click "Nueva Cancha"
   - Rellenar:
     ```
     Nombre: Cancha 1
     Tipo: Fútbol
     Instalación: (seleccionar una)
     Capacidad: 50
     ```
   - Click "Guardar"
   - Deberías ver la cancha en el listado

4. **Ver tarifas:**
   - Click en botón "$" (dólar) en la fila de la cancha
   - Agregar una tarifa:
     ```
     Día: Lunes
     Inicio: 08:00
     Fin: 09:00
     Precio: 25.00
     ```
   - Click "Guardar Tarifa"

5. **Programar mantenimiento:**
   - Ir a: `/instalaciones/mantenimiento/crear`
   - Rellenar:
     ```
     Cancha: Cancha 1
     Tipo: Preventivo
     Descripción: Revisión de seguridad
     Fecha inicio: 2026-02-15 08:00
     Fecha fin: 2026-02-15 10:00
     ```
   - Click "Programar"

---

## 🐛 TROUBLESHOOTING

### Error: "Database digisports_core not found"
```
Solución: Ejecutar paso_2_instalaciones.sql primero
mysql -u root digisports_core < paso_2_instalaciones.sql
```

### Error: "Access denied"
```
Solución: Verificar que MySQL esté corriendo en WAMP
http://localhost/phpmyadmin debe funcionar
```

### Error: "Cancha no válida"
```
Solución: Asegúrate de seleccionar una instalación que 
pertenezca al tenant actual en el formulario
```

### Las canchas no aparecen
```
Solución: 
1. Verificar SQL se ejecutó (SHOW TABLES;)
2. Login requiere módulo INSTALACIONES habilitado
3. Probar: http://localhost/digisports/public/instalaciones/cancha/index
```

---

## 🔗 URLS PRINCIPALES

| Funcionalidad | URL |
|---|---|
| Listado de canchas | `/instalaciones/cancha/index` |
| Crear cancha | `/instalaciones/cancha/crear` |
| Editar cancha | `/instalaciones/cancha/editar?id=1` |
| Ver tarifas | `/instalaciones/cancha/tarifas?id=1` |
| Listado de mantenimientos | `/instalaciones/mantenimiento/index` |
| Crear mantenimiento | `/instalaciones/mantenimiento/crear` |
| Editar mantenimiento | `/instalaciones/mantenimiento/editar?id=1` |

---

## 📚 PRÓXIMA LECTURA

1. **Entender SQL:** `database/paso_2_instalaciones.sql`
2. **Entender Controladores:** `app/controllers/instalaciones/CanchaController.php`
3. **Entender Vistas:** `app/views/instalaciones/canchas/index.php`
4. **Documentación completa:** `PASO_2_REFERENCIA.md`

---

## ❓ PREGUNTAS COMUNES

**P: ¿Se puede crear cancha sin instalación?**  
R: No, requiere seleccionar una instalación válida que pertenezca al tenant.

**P: ¿Se pueden eliminar canchas con reservas?**  
R: No, el sistema lo previene. Solo se pueden marcar como inactivas.

**P: ¿Las tarifas se aplican automáticamente?**  
R: Sí, las tarifas define el precio base para PASO 3 (Reservas).

**P: ¿Se puede cambiar la instalación de una cancha?**  
R: En la versión actual, no. Es editable pero deberías preservarla.

**P: ¿Qué es el Soft Delete?**  
R: En lugar de borrar, se marca como ELIMINADA. Protege datos y auditoría.

**P: ¿Cómo veo el historial de cambios?**  
R: Consulta la tabla `auditorias` o `eventos_canchas`.

---

## 📊 ESTADÍSTICAS

- **Líneas de código:** 1500+
- **Archivos:** 12
- **Métodos:** 16+
- **Tablas SQL:** 5
- **Vistas SQL:** 3
- **Documentación páginas:** 50+

---

## ✅ CHECKLIST RÁPIDO

- [ ] BD importada (`paso_2_instalaciones.sql`)
- [ ] Canchas funcionan (`/instalaciones/cancha/index`)
- [ ] Puedo crear cancha
- [ ] Puedo agregar tarifas
- [ ] Puedo programar mantenimiento
- [ ] Puedo editar canchas
- [ ] Puedo ver listado de mantenimientos

---

## 🎓 PRÓXIMO PASO

Después de entender PASO 2, prepárate para **PASO 3: Sistema de Reservas**

Esto incluirá:
- Búsqueda de disponibilidad
- Sistema de reserva
- Calendario
- Integración con tarifas
- Confirmación/aprobación

---

**¡Listo!** 🚀

Ahora puedes:
1. Importar la BD
2. Crear una cancha
3. Agregar tarifas
4. Programar mantenimiento

Si tienes dudas, consulta `PASO_2_REFERENCIA.md`

