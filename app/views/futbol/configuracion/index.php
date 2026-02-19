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
            <button type="button" class="btn" style="background:<?= $moduloColor ?>;color:white;" onclick="guardarTodo()">
                <i class="fas fa-save mr-1"></i>Guardar Todo
            </button>
        </div>

        <?php foreach ($configuraciones as $grupo => $configs): ?>
        <?php $icono = $grupoIconos[$grupo] ?? 'fas fa-folder'; ?>
        <div class="card card-outline" style="border-top-color:<?= $moduloColor ?>">
            <div class="card-header">
                <h3 class="card-title"><i class="<?= $icono ?> mr-2"></i><?= htmlspecialchars(ucfirst(strtolower(str_replace('_', ' ', $grupo)))) ?></h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="guardarConfiguracion('<?= htmlspecialchars($grupo) ?>')">
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
<script>
function guardarConfiguracion(grupo) {
    var form = document.getElementById('formGrupo_' + grupo);
    if (!form) return;

    var formData = $(form).serialize();

    $.post('<?= url('futbol', 'configuracion', 'guardar') ?>', formData, function(res) {
        if (res.success) {
            Swal.fire({ icon: 'success', title: '¡Guardado!', text: 'Configuración del grupo "' + grupo + '" guardada correctamente.', timer: 2000, showConfirmButton: false });
        } else {
            Swal.fire('Error', res.message || 'No se pudo guardar la configuración.', 'error');
        }
    }, 'json').fail(function() {
        Swal.fire('Error', 'Error de conexión al guardar.', 'error');
    });
}

function guardarTodo() {
    var forms = document.querySelectorAll('.form-config-grupo');
    var allData = {};
    var csrfToken = '<?= htmlspecialchars($csrf_token ?? '') ?>';

    forms.forEach(function(form) {
        var entries = $(form).serializeArray();
        entries.forEach(function(entry) {
            if (entry.name.startsWith('config[')) {
                allData[entry.name] = entry.value;
            }
        });
    });

    // Recoger checkboxes no marcados
    forms.forEach(function(form) {
        $(form).find('input[type="checkbox"].custom-control-input').each(function() {
            var name = $(this).attr('name');
            if (name && name.startsWith('config[') && !this.checked) {
                allData[name] = '0';
            }
        });
    });

    var postData = $.param(allData) + '&csrf_token=' + encodeURIComponent(csrfToken) + '&grupo=TODOS';

    Swal.fire({ title: 'Guardando...', allowOutsideClick: false, didOpen: function() { Swal.showLoading(); } });

    $.post('<?= url('futbol', 'configuracion', 'guardar') ?>', postData, function(res) {
        if (res.success) {
            Swal.fire({ icon: 'success', title: '¡Guardado!', text: 'Toda la configuración fue guardada correctamente.', timer: 2000, showConfirmButton: false });
        } else {
            Swal.fire('Error', res.message || 'No se pudo guardar.', 'error');
        }
    }, 'json').fail(function() {
        Swal.fire('Error', 'Error de conexión al guardar.', 'error');
    });
}

// Toggle label for switches
$(document).on('change', '.custom-control-input[type="checkbox"]', function() {
    var label = $(this).next('label');
    label.text(this.checked ? 'Activado' : 'Desactivado');
});
</script>
<?php $scripts = ob_get_clean(); ?>
