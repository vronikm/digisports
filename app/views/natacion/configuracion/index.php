<?php
/**
 * DigiSports Natación - Configuración del Módulo
 */
$configuraciones = $configuraciones ?? [];
$moduloColor     = $modulo_actual['color'] ?? '#0EA5E9';
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6"><h1 class="m-0"><i class="fas fa-cogs mr-2" style="color:<?= $moduloColor ?>"></i>Configuración</h1></div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <form method="POST" action="<?= url('natacion', 'configuracion', 'guardar') ?>" id="formConfig">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">

            <?php if (empty($configuraciones)): ?>
            <div class="card"><div class="card-body text-center text-muted py-5"><i class="fas fa-cogs fa-3x mb-3 opacity-50"></i><p>No hay configuraciones</p></div></div>
            <?php else: ?>
            <?php foreach ($configuraciones as $cat => $configs): ?>
            <div class="card card-outline" style="border-top-color:<?= $moduloColor ?>">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-folder mr-2"></i><?= htmlspecialchars(ucfirst(strtolower($cat))) ?></h3>
                </div>
                <div class="card-body">
                    <?php foreach ($configs as $c): ?>
                    <div class="form-group row mb-2">
                        <label class="col-md-4 col-form-label">
                            <strong><?= htmlspecialchars($c['ncg_clave']) ?></strong>
                            <?php if (!empty($c['ncg_descripcion'])): ?>
                            <br><small class="text-muted"><?= htmlspecialchars($c['ncg_descripcion']) ?></small>
                            <?php endif; ?>
                        </label>
                        <div class="col-md-8">
                            <input type="text" name="config[<?= $c['ncg_config_id'] ?>]" class="form-control form-control-sm" value="<?= htmlspecialchars($c['ncg_valor'] ?? '') ?>">
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>

            <div class="mb-4">
                <button type="submit" class="btn" style="background:<?= $moduloColor ?>;color:white;"><i class="fas fa-save mr-1"></i>Guardar Configuración</button>
            </div>
            <?php endif; ?>
        </form>
    </div>
</section>

<script>
// Variable de control para evitar envíos duplicados
var isSubmittingConfig = false;

// Función para serializar el formulario correctamente para PHP
function serializeForm(form) {
    var formData = {};
    var inputs = form.querySelectorAll('input[name]');
    
    inputs.forEach(function(input) {
        // Mapear inputs config[id] -> { config: { id: value } }
        var match = input.name.match(/^config\[(\d+)\]$/);
        if (match) {
            if (!formData['config']) formData['config'] = {};
            formData['config'][match[1]] = input.value;
        } else if (input.name) {
            formData[input.name] = input.value;
        }
    });
    
    return formData;
}

// Convertir objeto a URLSearchParams
function objectToFormData(obj, prefix = '') {
    var str = [];
    for (var key in obj) {
        if (obj.hasOwnProperty(key)) {
            var k = prefix ? prefix + '[' + key + ']' : key;
            if (typeof obj[key] === 'object' && obj[key] !== null) {
                str.push(objectToFormData(obj[key], k));
            } else {
                str.push(encodeURIComponent(k) + '=' + encodeURIComponent(obj[key]));
            }
        }
    }
    return str.join('&');
}

// Escuchar el evento submit del formulario
document.addEventListener('DOMContentLoaded', function() {
    console.log('[DEBUG] DOMContentLoaded - Inicializar form listener');
    
    var formConfig = document.getElementById('formConfig');
    if (!formConfig) {
        console.error('[ERROR] Formulario #formConfig no encontrado');
        return;
    }
    
    console.log('[SUCCESS] Formulario #formConfig encontrado');
    
    formConfig.addEventListener('submit', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        console.log('[DEBUG] Evento submit capturado');
        
        if (isSubmittingConfig) {
            console.warn('[WARN] Ya hay un envío en curso');
            return false;
        }
        
        // Mostrar confirmación
        Swal.fire({
            title: '¿Guardar cambios?',
            text: '¿Deseas confirmar la actualización de la configuración?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '<?= $moduloColor ?>',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, guardar',
            cancelButtonText: 'Cancelar',
            allowOutsideClick: false,
            allowEscapeKey: false
        }).then(function(result) {
            console.log('[DEBUG] Resultado Swal:', result.isConfirmed);
            
            if (result.isConfirmed) {
                console.log('[DEBUG] Guardando configuración...');
                
                isSubmittingConfig = true;
                
                // Obtener datos del formulario
                var serialized = serializeForm(formConfig);
                var bodyData = objectToFormData(serialized);
                var url = formConfig.getAttribute('action');
                
                console.log('[DEBUG] === INFORMACIÓN DE ENVÍO ===');
                console.log('[DEBUG] URL:', url);
                console.log('[DEBUG] URL longitud:', url ? url.length : 'NULL');
                console.log('[DEBUG] URL tipo:', typeof url);
                
                if (!url || url === '' || url === 'null') {
                    console.error('[ERROR] URL es inválida o está vacía!');
                    console.error('[ERROR] Form action attribute:', formConfig.getAttribute('action'));
                    console.error('[ERROR] Form HTML (primeros 500 chars):', formConfig.outerHTML.substring(0, 500));
                    
                    Swal.fire({
                        position: 'top-end',
                        icon: 'error',
                        title: 'Error',
                        text: 'URL del formulario no configurada. Revisa F12 Console.',
                        showConfirmButton: false,
                        timer: 5000,
                        timerProgressBar: true,
                        toast: true
                    });
                    
                    isSubmittingConfig = false;
                    return;
                }
                
                console.log('[DEBUG] Body preview:', bodyData.substring(0, 150));
                console.log('[DEBUG] Iniciando fetch a:', url);
                
                fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: bodyData
                })
                .then(response => {
                    console.log('[DEBUG] === RESPUESTA RECIBIDA ===');
                    console.log('[DEBUG] HTTP Status:', response.status);
                    console.log('[DEBUG] HTTP StatusText:', response.statusText);
                    console.log('[DEBUG] Content-Type:', response.headers.get('Content-Type'));
                    
                    if (!response.ok) {
                        console.error('[ERROR] HTTP error:', response.status, response.statusText);
                        throw new Error('HTTP ' + response.status + ' ' + response.statusText);
                    }
                    
                    return response.text();
                })
                .then(text => {
                    console.log('[DEBUG] === ANÁLISIS DE RESPUESTA ===');
                    console.log('[DEBUG] Longitud total:', text.length, 'bytes');
                    
                    if (text.length === 0) {
                        console.error('[ERROR] Respuesta VACÍA (0 bytes)');
                        throw new Error('Servidor devolvió respuesta vacía');
                    }
                    
                    console.log('[DEBUG] Primeros 100 chars:', text.substring(0, 100));
                    console.log('[DEBUG] Últimos 50 chars:', text.substring(Math.max(0, text.length - 50)));
                    
                    // Mostrar como hex los primeros 10 bytes
                    var hexChars = [];
                    for (var i = 0; i < Math.min(10, text.length); i++) {
                        hexChars.push((text.charCodeAt(i).toString(16).padStart(2, '0')));
                    }
                    console.log('[DEBUG] Bytes iniciales (hex):', hexChars.join(' '));
                    
                    // Limpiar BOM y espacios
                    var cleanedText = text.replace(/^\uFEFF/, '').trim();
                    console.log('[DEBUG] Después de cleanup - longitud:', cleanedText.length);
                    
                    // Verificar si parece JSON
                    if (!cleanedText.startsWith('{') && !cleanedText.startsWith('[')) {
                        console.error('[ERROR] Response NO comienza con { o [');
                        console.error('[ERROR] Comienza con:', cleanedText.substring(0, 200));
                        
                        // Detectar HTML
                        if (cleanedText.indexOf('<') !== -1) {
                            console.error('[ERROR] Parece ser HTML (contiene <)');
                            console.error('[ERROR] Contenido HTML:', cleanedText.substring(0, 500));
                        }
                        
                        throw new SyntaxError('Response no es JSON - comienza con: ' + cleanedText.substring(0, 50));
                    }
                    
                    try {
                        var parsedResponse = JSON.parse(cleanedText);
                        console.log('[DEBUG] === JSON PARSEADO EXITOSAMENTE ===');
                        console.log('[DEBUG] Objeto:', parsedResponse);
                        console.log('[DEBUG] success =', parsedResponse.success);
                        console.log('[DEBUG] message =', parsedResponse.message);
                        
                        if (parsedResponse && typeof parsedResponse === 'object' && parsedResponse.success === true) {
                            console.log('[SUCCESS] ¡Configuración guardada!');
                            
                            Swal.fire({
                                position: 'top-end',
                                icon: 'success',
                                title: 'Guardado',
                                text: parsedResponse.message || 'Configuración guardada correctamente',
                                showConfirmButton: false,
                                timer: 3000,
                                timerProgressBar: true,
                                toast: true,
                                didOpen: function(toast) {
                                    toast.addEventListener('mouseenter', Swal.stopTimer);
                                    toast.addEventListener('mouseleave', Swal.resumeTimer);
                                }
                            });
                        } else if (parsedResponse && parsedResponse.success === false) {
                            console.error('[ERROR]', parsedResponse.message);
                            
                            Swal.fire({
                                position: 'top-end',
                                icon: 'error',
                                title: 'Error',
                                text: parsedResponse.message || 'No se pudo guardar',
                                showConfirmButton: false,
                                timer: 4000,
                                timerProgressBar: true,
                                toast: true,
                                didOpen: function(toast) {
                                    toast.addEventListener('mouseenter', Swal.stopTimer);
                                    toast.addEventListener('mouseleave', Swal.resumeTimer);
                                }
                            });
                        } else {
                            console.error('[ERROR] JSON válido pero no tiene estructura esperada');
                            throw new Error('JSON no tiene campo success');
                        }
                    } catch (e) {
                        console.error('[ERROR] === FALLO EN JSON.parse() ===');
                        console.error('[ERROR] Error:', e.message);
                        console.error('[ERROR] Stack:', e.stack);
                        console.error('[ERROR] Intentó parsear:', cleanedText.substring(0, 300));
                        
                        Swal.fire({
                            position: 'top-end',
                            icon: 'error',
                            title: 'Error',
                            text: 'Respuesta inválida del servidor. Abre F12 Console.',
                            showConfirmButton: false,
                            timer: 5000,
                            timerProgressBar: true,
                            toast: true
                        });
                        
                        throw e;
                    }
                    
                    isSubmittingConfig = false;
                })
                .catch(error => {
                    console.error('[ERROR] Fetch failed:', error);
                    
                    Swal.fire({
                        position: 'top-end',
                        icon: 'error',
                        title: 'Error de conexión',
                        text: error.message,
                        showConfirmButton: false,
                        timer: 4000,
                        timerProgressBar: true,
                        toast: true,
                        didOpen: function(toast) {
                            toast.addEventListener('mouseenter', Swal.stopTimer);
                            toast.addEventListener('mouseleave', Swal.resumeTimer);
                        }
                    });
                    
                    isSubmittingConfig = false;
                });
            }
        });
        
        return false;
    });
});
</script>
