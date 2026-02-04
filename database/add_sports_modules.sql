-- Script para agregar módulos deportivos al Hub DigiSports
-- Fecha: 2026-01-26

-- Verificar si ya existen los módulos antes de insertar
INSERT INTO modulos_sistema (codigo, nombre, descripcion, icono, color, url_base, es_externo, orden_visualizacion, requiere_suscripcion, estado) 
SELECT * FROM (SELECT 
    'FUTBOL' as codigo, 
    'DigiSports Fútbol' as nombre, 
    'Gestión de canchas de fútbol, ligas y torneos' as descripcion, 
    'fa-futbol' as icono, 
    '#28a745' as color, 
    '/digifutbol/' as url_base, 
    'S' as es_externo, 
    10 as orden_visualizacion, 
    'S' as requiere_suscripcion, 
    'A' as estado
) AS tmp WHERE NOT EXISTS (SELECT 1 FROM modulos_sistema WHERE codigo = 'FUTBOL');

INSERT INTO modulos_sistema (codigo, nombre, descripcion, icono, color, url_base, es_externo, orden_visualizacion, requiere_suscripcion, estado) 
SELECT * FROM (SELECT 
    'BASKET' as codigo, 
    'DigiSports Basket' as nombre, 
    'Gestión de canchas de basketball y torneos' as descripcion, 
    'fa-basketball-ball' as icono, 
    '#fd7e14' as color, 
    '/digibasket/' as url_base, 
    'S' as es_externo, 
    11 as orden_visualizacion, 
    'S' as requiere_suscripcion, 
    'A' as estado
) AS tmp WHERE NOT EXISTS (SELECT 1 FROM modulos_sistema WHERE codigo = 'BASKET');

INSERT INTO modulos_sistema (codigo, nombre, descripcion, icono, color, url_base, es_externo, orden_visualizacion, requiere_suscripcion, estado) 
SELECT * FROM (SELECT 
    'NATACION' as codigo, 
    'DigiSports Natación' as nombre, 
    'Gestión de piscinas, clases y competencias' as descripcion, 
    'fa-swimmer' as icono, 
    '#17a2b8' as color, 
    '/diginatacion/' as url_base, 
    'S' as es_externo, 
    12 as orden_visualizacion, 
    'S' as requiere_suscripcion, 
    'A' as estado
) AS tmp WHERE NOT EXISTS (SELECT 1 FROM modulos_sistema WHERE codigo = 'NATACION');

INSERT INTO modulos_sistema (codigo, nombre, descripcion, icono, color, url_base, es_externo, orden_visualizacion, requiere_suscripcion, estado) 
SELECT * FROM (SELECT 
    'ARTES_MARCIALES' as codigo, 
    'DigiSports Artes Marciales' as nombre, 
    'Academias de karate, taekwondo, judo y más' as descripcion, 
    'fa-fist-raised' as icono, 
    '#dc3545' as color, 
    '/digimarciales/' as url_base, 
    'S' as es_externo, 
    13 as orden_visualizacion, 
    'S' as requiere_suscripcion, 
    'A' as estado
) AS tmp WHERE NOT EXISTS (SELECT 1 FROM modulos_sistema WHERE codigo = 'ARTES_MARCIALES');

INSERT INTO modulos_sistema (codigo, nombre, descripcion, icono, color, url_base, es_externo, orden_visualizacion, requiere_suscripcion, estado) 
SELECT * FROM (SELECT 
    'AJEDREZ' as codigo, 
    'DigiSports Ajedrez' as nombre, 
    'Clubes de ajedrez, torneos y rankings' as descripcion, 
    'fa-chess' as icono, 
    '#343a40' as color, 
    '/digiajedrez/' as url_base, 
    'S' as es_externo, 
    14 as orden_visualizacion, 
    'S' as requiere_suscripcion, 
    'A' as estado
) AS tmp WHERE NOT EXISTS (SELECT 1 FROM modulos_sistema WHERE codigo = 'AJEDREZ');

INSERT INTO modulos_sistema (codigo, nombre, descripcion, icono, color, url_base, es_externo, orden_visualizacion, requiere_suscripcion, estado) 
SELECT * FROM (SELECT 
    'MULTIDEPORTE' as codigo, 
    'DigiSports Multideporte' as nombre, 
    'Academias mixtas con múltiples disciplinas' as descripcion, 
    'fa-running' as icono, 
    '#6f42c1' as color, 
    '/digimulti/' as url_base, 
    'S' as es_externo, 
    15 as orden_visualizacion, 
    'S' as requiere_suscripcion, 
    'A' as estado
) AS tmp WHERE NOT EXISTS (SELECT 1 FROM modulos_sistema WHERE codigo = 'MULTIDEPORTE');

INSERT INTO modulos_sistema (codigo, nombre, descripcion, icono, color, url_base, es_externo, orden_visualizacion, requiere_suscripcion, estado) 
SELECT * FROM (SELECT 
    'STORE' as codigo, 
    'DigiSports Store' as nombre, 
    'Tienda de artículos deportivos' as descripcion, 
    'fa-store' as icono, 
    '#e83e8c' as color, 
    '/digistore/' as url_base, 
    'S' as es_externo, 
    16 as orden_visualizacion, 
    'S' as requiere_suscripcion, 
    'A' as estado
) AS tmp WHERE NOT EXISTS (SELECT 1 FROM modulos_sistema WHERE codigo = 'STORE');

-- Verificar los módulos insertados
SELECT modulo_id, codigo, nombre, icono, color FROM modulos_sistema ORDER BY orden_visualizacion;
