Esta carpeta contiene la vista principal del dashboard de Instalaciones.

- El archivo index.php usa branding dinámico (nombre, color, icono) según la configuración en la tabla modulos_sistema.
- Si el branding no se muestra, asegúrate de que el DashboardController de instalaciones existe y hereda de ModuleController.
- El código del módulo debe ser exactamente 'INSTALACIONES' (ver columna 'codigo' en modulos_sistema).
