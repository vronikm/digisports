<?php
/**
 * DigiSports Fútbol - Configuración del Módulo
 * @vars $configuraciones, $csrf_token, $modulo_actual
 */
$configuraciones = $configuraciones ?? [];
$moduloColor     = $modulo_actual['color'] ?? '#22C55E';

$grupoIconos = [
    'GENERAL'       => 'fas fa-cog',
    'INSCRIPCIONES' => 'fas fa-clipboard-list',
    'PAGOS'         => 'fas fa-dollar-sign',
    'ASISTENCIA'    => 'fas fa-calendar-check',
    'EVALUACIONES'  => 'fas fa-star',
    'TORNEOS'       => 'fas fa-trophy',
];
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6"><h1 class="m-0"><i class="fas fa-cogs mr-2" style="color:<?= $moduloColor ?>"></i>Configuración</h1></div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= url('futbol', 'dashboard', 'index') ?>">Fútbol</a></li>
                    <li class="breadcrumb-item active">Configuración</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <?php if (empty($configuraciones)): ?>
        <div class="card">
            <div class="card-body text-center text-muted py-5">
                <i class="fas fa-cogs fa-3x mb-3 opacity-50"></i>
                <p>No hay configuraciones definidas</p>
            </div>
        </div>
        <?php else: ?>

        <!-- Botón guardar global -->
        <div class="mb-3 text-right">
            <button type="button" id="btnGuardarTodo" class="btn" style="background:<?= $moduloColor ?>;color:white;">
                <i class="fas fa-save mr-1"></i>Guardar Todo
            </button>
        </div>

        <?php foreach ($configuraciones as $grupo => $configs): ?>
        <?php $icono = $grupoIconos[$grupo] ?? 'fas fa-folder'; ?>
        <div class="card card-outline" style="border-top-color:<?= $moduloColor ?>">
            <div class="card-header">
                <h3 class="card-title"><i class="<?= $icono ?> mr-2"></i><?= htmlspecialchars(ucfirst(strtolower(str_replace('_', ' ', $grupo)))) ?></h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-sm btn-outline-primary js-guardar-grupo" data-grupo="<?= htmlspecialchars($grupo) ?>">
                        <i class="fas fa-save mr-1"></i>Guardar grupo
                    </button>
                    <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                </div>
            </div>
            <div class="card-body">
                <form id="formGrupo_<?= htmlspecialchars($grupo) ?>" class="form-config-grupo" data-grupo="<?= htmlspecialchars($grupo) ?>">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                    <input type="hidden" name="grupo" value="<?= htmlspecialchars($grupo) ?>">

                    <?php foreach ($configs as $c): ?>
                    <?php
                        $tipo  = $c['fcg_tipo'] ?? 'TEXT';
                        $clave = $c['fcg_clave'] ?? '';
                        $valor = $c['fcg_valor'] ?? '';
                        $desc  = $c['fcg_descripcion'] ?? '';
                        $configId = $c['fcg_config_id'] ?? 0;
                    ?>
                    <div class="form-group row mb-2">
                        <label class="col-md-4 col-form-label">
                            <strong><?= htmlspecialchars($clave) ?></strong>
                            <?php if ($desc): ?>
                            <br><small class="text-muted"><?= htmlspecialchars($desc) ?></small>
                            <?php endif; ?>
                        </label>
                        <div class="col-md-8">
                            <?php if ($tipo === 'BOOLEAN'): ?>
                            <div class="custom-control custom-switch mt-2">
                                <input type="checkbox" class="custom-control-input" id="cfg_<?= $configId ?>" name="config[<?= $configId ?>]" value="1" <?= ($valor === '1' || $valor === 'true') ? 'checked' : '' ?>>
                                <label class="custom-control-label" for="cfg_<?= $configId ?>"><?= $valor === '1' || $valor === 'true' ? 'Activado' : 'Desactivado' ?></label>
                            </div>
                            <?php elseif ($tipo === 'NUMBER'): ?>
                            <input type="number" name="config[<?= $configId ?>]" class="form-control form-control-sm" value="<?= htmlspecialchars($valor) ?>">
                            <?php elseif ($tipo === 'SELECT'): ?>
                            <?php
                                $opciones = [];
                                if (!empty($c['fcg_descripcion'])) {
                                    $decoded = json_decode($c['fcg_descripcion'], true);
                                    $opciones = is_array($decoded) ? $decoded : explode(',', $c['fcg_descripcion']);
                                }
                            ?>
                            <select name="config[<?= $configId ?>]" class="form-control form-control-sm">
                                <?php foreach ($opciones as $op): ?>
                                <?php $opVal = is_array($op) ? ($op['value'] ?? $op) : trim($op); ?>
                                <?php $opLabel = is_array($op) ? ($op['label'] ?? $opVal) : $opVal; ?>
                                <option value="<?= htmlspecialchars($opVal) ?>" <?= $valor === $opVal ? 'selected' : '' ?>><?= htmlspecialchars($opLabel) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <?php elseif ($tipo === 'JSON'): ?>
                            <textarea name="config[<?= $configId ?>]" class="form-control form-control-sm" rows="3" style="font-family:monospace;"><?= htmlspecialchars($valor) ?></textarea>
                            <?php else: /* TEXT */ ?>
                            <input type="text" name="config[<?= $configId ?>]" class="form-control form-control-sm" value="<?= htmlspecialchars($valor) ?>">
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </form>
            </div>
        </div>
        <?php endforeach; ?>

        <?php endif; ?>
    </div>
</section>

<?php ob_start(); ?>
<script nonce="<?= cspNonce() ?>">
// Función para convertir objeto a query string
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

// Serializar datos de un formulario
function serializeFormData(form) {
    var data = {};
    var inputs = form.querySelectorAll('[name^="config"]');
    
    inputs.forEach(function(input) {
        var match = input.name.match(/^config\[(\d+)\]$/);
        if (match) {
            if (!data['config']) data['config'] = {};
            data['config'][match[1]] = input.value;
        }
    });
    
    // Agregar CSRF y grupo
    data['csrf_token'] = form.querySelector('input[name="csrf_token"]').value;
    data['grupo'] = form.getAttribute('data-grupo');
    
    return data;
}

function guardarConfiguracion(grupo) {
    var form = document.getElementById('formGrupo_' + grupo);
    if (!form) {
        console.error('[ERROR] No se encontró formulario para grupo: ' + grupo);
        return;
    }

    console.log('[DEBUG] Guardando grupo: ' + grupo);
    
    var serialized = serializeFormData(form);
    var bodyData = objectToFormData(serialized);
    var url = '<?= url('futbol', 'configuracion', 'guardar') ?>';
    
    console.log('[DEBUG] URL:', url);
    console.log('[DEBUG] Datos:', bodyData.substring(0, 150) + '...');

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
            var res = JSON.parse(cleanedText);
            console.log('[DEBUG] === JSON PARSEADO EXITOSAMENTE ===');
            console.log('[DEBUG] Objeto:', res);
            console.log('[DEBUG] success =', res.success);
            console.log('[DEBUG] message =', res.message);
            
            if (res && typeof res === 'object' && res.success === true) {
                console.log('[SUCCESS] ¡Grupo guardado!');
                
                Swal.fire({
                    position: 'top-end',
                    icon: 'success',
                    title: 'Guardado',
                    text: res.message || 'Grupo guardado correctamente',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                    toast: true,
                    didOpen: function(toast) {
                        toast.addEventListener('mouseenter', Swal.stopTimer);
                        toast.addEventListener('mouseleave', Swal.resumeTimer);
                    }
                });
            } else if (res && res.success === false) {
                console.error('[ERROR]', res.message);
                
                Swal.fire({
                    position: 'top-end',
                    icon: 'error',
                    title: 'Error',
                    text: res.message || 'No se pudo guardar',
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
    })
    .catch(error => {
        console.error('[ERROR] Fetch error:', error);
        Swal.fire({
            position: 'top-end',
            icon: 'error',
            title: 'Error de conexión',
            text: error.message,
            showConfirmButton: false,
            timer: 4000,
            timerProgressBar: true,
            toast: true
        });
    });
}

function guardarTodo() {
    var forms = document.querySelectorAll('.form-config-grupo');
    var allData = {};
    var csrfToken = '<?= htmlspecialchars($csrf_token ?? '') ?>';

    forms.forEach(function(form) {
        var inputs = form.querySelectorAll('[name^="config"]');
        inputs.forEach(function(input) {
            var match = input.name.match(/^config\[(\d+)\]$/);
            if (match) {
                if (!allData['config']) allData['config'] = {};
                allData['config'][match[1]] = input.value;
            }
        });
    });

    // Recoger checkboxes no marcados
    forms.forEach(function(form) {
        form.querySelectorAll('input[type="checkbox"].custom-control-input').forEach(function(input) {
            var name = input.name;
            if (name && name.match(/^config\[\d+\]$/)) {
                var match = name.match(/^config\[(\d+)\]$/);
                if (match && !input.checked) {
                    if (!allData['config']) allData['config'] = {};
                    allData['config'][match[1]] = '0';
                }
            }
        });
    });

    allData['csrf_token'] = csrfToken;
    allData['grupo'] = 'TODOS';

    console.log('[DEBUG] Guardando todo');
    console.log('[DEBUG] Datos:', allData);

    // Mostrar confirmación
    Swal.fire({
        title: '¿Guardar todo?',
        text: '¿Deseas guardar toda la configuración?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#22C55E',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, guardar todo',
        cancelButtonText: 'Cancelar',
        allowOutsideClick: false,
        allowEscapeKey: false
    }).then(function(result) {
        if (result.isConfirmed) {
            var bodyData = objectToFormData(allData);
            var url = '<?= url('futbol', 'configuracion', 'guardar') ?>';
            
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
                    var res = JSON.parse(cleanedText);
                    console.log('[DEBUG] === JSON PARSEADO EXITOSAMENTE ===');
                    console.log('[DEBUG] Objeto:', res);
                    console.log('[DEBUG] success =', res.success);
                    console.log('[DEBUG] message =', res.message);
                    
                    if (res && typeof res === 'object' && res.success === true) {
                        console.log('[SUCCESS] ¡Todo guardado!');
                        
                        Swal.fire({
                            position: 'top-end',
                            icon: 'success',
                            title: 'Guardado',
                            text: res.message || 'Toda la configuración guardada',
                            showConfirmButton: false,
                            timer: 3000,
                            timerProgressBar: true,
                            toast: true,
                            didOpen: function(toast) {
                                toast.addEventListener('mouseenter', Swal.stopTimer);
                                toast.addEventListener('mouseleave', Swal.resumeTimer);
                            }
                        });
                    } else if (res && res.success === false) {
                        console.error('[ERROR]', res.message);
                        
                        Swal.fire({
                            position: 'top-end',
                            icon: 'error',
                            title: 'Error',
                            text: res.message || 'No se pudo guardar',
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
            })
            .catch(error => {
                console.error('[ERROR] Fetch error:', error);
                Swal.fire({
                    position: 'top-end',
                    icon: 'error',
                    title: 'Error de conexión',
                    text: error.message,
                    showConfirmButton: false,
                    timer: 4000,
                    timerProgressBar: true,
                    toast: true
                });
            });
        }
    });
}

// Toggle label for switches
$(document).on('change', '.custom-control-input[type="checkbox"]', function() {
    var label = $(this).next('label');
    label.text(this.checked ? 'Activado' : 'Desactivado');
});

// Botón guardar grupo
$(document).on('click', '.js-guardar-grupo', function() {
    guardarConfiguracion($(this).data('grupo'));
});

// Botón guardar todo
$('#btnGuardarTodo').on('click', function() {
    guardarTodo();
});
</script>
<?php $scripts = ob_get_clean(); ?>
