# Cambio de Branding Dinámico en Módulos (30-01-2026)

## Requerimiento
Permitir que el nombre, color e icono de cada módulo (Store, Fútbol, Natación, Multideporte, Basket, Artes Marciales, Ajedrez, etc.) se actualicen dinámicamente en todas las vistas de dashboard, reflejando los valores configurados en la base de datos (`modulos_sistema`). El objetivo es que cualquier cambio realizado desde el módulo de Seguridad (o directamente en la BD) se propague automáticamente a la interfaz de usuario de todos los módulos.

## Detalle del ajuste
- Se corrigió el valor de `$this->moduloCodigo` en todos los `DashboardController` para que coincida exactamente con el campo `codigo` de la tabla `modulos_sistema` (por ejemplo, `STORE`, `FUTBOL`, etc.).
- Se eliminó la asignación fija de nombre, color e icono en los controladores. Ahora solo se asigna el código y el resto se carga dinámicamente.
- Se ajustó el método `loadModuleBranding()` en `ModuleController.php` para que también actualice `$this->moduloNombre` con el valor de la base de datos, además de color e icono.
- Todas las vistas de dashboard usan ahora `$modulo_actual['nombre']`, `$modulo_actual['color']` y `$modulo_actual['icono']` para mostrar el branding dinámico.
- Así, cualquier cambio en la tabla `modulos_sistema` se refleja automáticamente en la interfaz de todos los módulos.

## Archivos modificados
- `app/controllers/store/DashboardController.php`
- `app/controllers/futbol/DashboardController.php`
- `app/controllers/natacion/DashboardController.php`
- `app/controllers/multideporte/DashboardController.php`
- `app/controllers/basket/DashboardController.php`
- `app/controllers/artes_marciales/DashboardController.php`
- `app/controllers/ajedrez/DashboardController.php`
- `app/controllers/ModuleController.php`
- Todas las vistas de dashboard de los módulos

## Notas
Si el branding deja de actualizarse dinámicamente:
1. Verifica que el valor de `$this->moduloCodigo` en el controlador coincida exactamente con el campo `codigo` en la tabla `modulos_sistema`.
2. Asegúrate de que el método `loadModuleBranding()` no esté sobrescrito ni modificado.
3. Revisa que las vistas usen `$modulo_actual['nombre']`, `$modulo_actual['color']` y `$modulo_actual['icono']`.

---
_Registro automático generado por GitHub Copilot el 30/01/2026._
