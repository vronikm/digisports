<?php
/**
 * DigiSports Natación - Formulario Crear/Editar Alumno
 */
$alumno       = $alumno ?? [];
$niveles      = $niveles ?? [];
$campos       = $campos_custom ?? [];
$datosCustom  = $datos_custom ?? [];
$clientes     = $clientes ?? [];
$sedes        = $sedes ?? [];
$sedeActiva   = $sede_activa ?? null;
$editando     = !empty($alumno);
$moduloColor  = $modulo_actual['color'] ?? '#0EA5E9';
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">
                    <i class="fas fa-<?= $editando ? 'edit' : 'user-plus' ?> mr-2" style="color:<?= $moduloColor ?>"></i>
                    <?= $editando ? 'Editar Alumno' : 'Nuevo Alumno' ?>
                </h1>
            </div>
            <div class="col-sm-6">
                <div class="float-sm-right">
                    <a href="<?= url('natacion', 'alumno', 'index') ?>" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left mr-1"></i>Volver</a>
                </div>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <form method="POST" action="<?= $editando ? url('natacion', 'alumno', 'editar') : url('natacion', 'alumno', 'crear') ?>" id="formAlumno">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
            <?php if ($editando): ?>
            <input type="hidden" name="id" value="<?= $alumno['alu_alumno_id'] ?>">
            <?php endif; ?>

            <div class="row">
                <!-- Datos Personales -->
                <div class="col-lg-8">
                    <div class="card card-outline" style="border-top-color:<?= $moduloColor ?>">
                        <div class="card-header"><h3 class="card-title"><i class="fas fa-user mr-2"></i>Datos Personales</h3></div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Tipo Doc. <span class="text-danger">*</span></label>
                                        <select name="tipo_identificacion" id="tipo_id" class="form-control" required>
                                            <option value="CED" <?= ($alumno['alu_tipo_identificacion'] ?? '') === 'CED' ? 'selected' : '' ?>>Cédula</option>
                                            <option value="RUC" <?= ($alumno['alu_tipo_identificacion'] ?? '') === 'RUC' ? 'selected' : '' ?>>RUC</option>
                                            <option value="PAS" <?= ($alumno['alu_tipo_identificacion'] ?? '') === 'PAS' ? 'selected' : '' ?>>Pasaporte</option>
                                            <option value="OTR" <?= ($alumno['alu_tipo_identificacion'] ?? '') === 'OTR' ? 'selected' : '' ?>>Otro</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Identificación <span class="text-danger">*</span></label>
                                        <input type="text" name="identificacion" id="identificacion" class="form-control" required maxlength="20" value="<?= htmlspecialchars($alumno['alu_identificacion'] ?? '') ?>">
                                        <small id="id_feedback" class="form-text"></small>
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <div class="form-group">
                                        <label>Fecha Nacimiento <span class="text-danger">*</span></label>
                                        <input type="date" name="fecha_nacimiento" class="form-control" required value="<?= htmlspecialchars($alumno['alu_fecha_nacimiento'] ?? '') ?>">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-5">
                                    <div class="form-group">
                                        <label>Nombres <span class="text-danger">*</span></label>
                                        <input type="text" name="nombres" class="form-control" required maxlength="100" value="<?= htmlspecialchars($alumno['alu_nombres'] ?? '') ?>">
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <div class="form-group">
                                        <label>Apellidos <span class="text-danger">*</span></label>
                                        <input type="text" name="apellidos" class="form-control" required maxlength="100" value="<?= htmlspecialchars($alumno['alu_apellidos'] ?? '') ?>">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>Género</label>
                                        <select name="genero" class="form-control">
                                            <option value="">—</option>
                                            <option value="M" <?= ($alumno['alu_genero'] ?? '') === 'M' ? 'selected' : '' ?>>Masculino</option>
                                            <option value="F" <?= ($alumno['alu_genero'] ?? '') === 'F' ? 'selected' : '' ?>>Femenino</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-5">
                                    <div class="form-group">
                                        <label>Email</label>
                                        <input type="email" name="email" class="form-control" maxlength="200" value="<?= htmlspecialchars($alumno['alu_email'] ?? '') ?>">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Teléfono</label>
                                        <input type="text" name="telefono" class="form-control" maxlength="20" value="<?= htmlspecialchars($alumno['alu_telefono'] ?? '') ?>">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Estado</label>
                                        <select name="estado" class="form-control">
                                            <option value="ACTIVO" <?= ($alumno['alu_estado'] ?? 'ACTIVO') === 'ACTIVO' ? 'selected' : '' ?>>Activo</option>
                                            <option value="INACTIVO" <?= ($alumno['alu_estado'] ?? '') === 'INACTIVO' ? 'selected' : '' ?>>Inactivo</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Dirección</label>
                                <input type="text" name="direccion" class="form-control" maxlength="300" value="<?= htmlspecialchars($alumno['alu_direccion'] ?? '') ?>">
                            </div>
                            <div class="form-group">
                                <label>Observaciones Médicas</label>
                                <textarea name="observaciones_medicas" class="form-control" rows="2" maxlength="500"><?= htmlspecialchars($alumno['alu_observaciones_medicas'] ?? '') ?></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Campos Personalizados -->
                    <?php if (!empty($campos)): ?>
                    <div class="card card-outline card-info">
                        <div class="card-header"><h3 class="card-title"><i class="fas fa-sliders-h mr-2"></i>Datos Adicionales</h3></div>
                        <div class="card-body">
                            <div class="row">
                                <?php foreach ($campos as $campo): ?>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label><?= htmlspecialchars($campo['ncf_etiqueta']) ?> <?= $campo['ncf_requerido'] ? '<span class="text-danger">*</span>' : '' ?></label>
                                        <?php
                                        $clave = $campo['ncf_clave'];
                                        $val = $datosCustom[$clave] ?? '';
                                        switch ($campo['ncf_tipo']):
                                            case 'SELECT':
                                                $opciones = json_decode($campo['ncf_opciones'] ?? '[]', true) ?: [];
                                        ?>
                                        <select name="custom_<?= htmlspecialchars($clave) ?>" class="form-control" <?= $campo['ncf_requerido'] ? 'required' : '' ?>>
                                            <option value="">— Seleccionar —</option>
                                            <?php foreach ($opciones as $op): ?>
                                            <option value="<?= htmlspecialchars($op) ?>" <?= $val === $op ? 'selected' : '' ?>><?= htmlspecialchars($op) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <?php break; case 'TEXTAREA': ?>
                                        <textarea name="custom_<?= htmlspecialchars($clave) ?>" class="form-control" rows="2" <?= $campo['ncf_requerido'] ? 'required' : '' ?>><?= htmlspecialchars($val) ?></textarea>
                                        <?php break; case 'CHECKBOX': ?>
                                        <div class="custom-control custom-checkbox mt-2">
                                            <input type="checkbox" class="custom-control-input" id="chk_<?= $clave ?>" name="custom_<?= htmlspecialchars($clave) ?>" value="1" <?= $val ? 'checked' : '' ?>>
                                            <label class="custom-control-label" for="chk_<?= $clave ?>">Sí</label>
                                        </div>
                                        <?php break; case 'DATE': ?>
                                        <input type="date" name="custom_<?= htmlspecialchars($clave) ?>" class="form-control" value="<?= htmlspecialchars($val) ?>" <?= $campo['ncf_requerido'] ? 'required' : '' ?>>
                                        <?php break; case 'NUMBER': ?>
                                        <input type="number" name="custom_<?= htmlspecialchars($clave) ?>" class="form-control" value="<?= htmlspecialchars($val) ?>" <?= $campo['ncf_requerido'] ? 'required' : '' ?>>
                                        <?php break; default: ?>
                                        <input type="text" name="custom_<?= htmlspecialchars($clave) ?>" class="form-control" value="<?= htmlspecialchars($val) ?>" <?= $campo['ncf_requerido'] ? 'required' : '' ?>>
                                        <?php break; endswitch; ?>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Sidebar: Sede + Representante + Nivel -->
                <div class="col-lg-4">
                    <?php if (!empty($sedes)): ?>
                    <div class="card card-outline" style="border-top-color:<?= $moduloColor ?>">
                        <div class="card-header"><h3 class="card-title"><i class="fas fa-building mr-2"></i>Sede</h3></div>
                        <div class="card-body">
                            <div class="form-group mb-0">
                                <select name="sede_id" class="form-control">
                                    <option value="">— Sin sede —</option>
                                    <?php foreach ($sedes as $s): ?>
                                    <option value="<?= $s['sed_sede_id'] ?>" <?= ($alumno['alu_sede_id'] ?? $sedeActiva) == $s['sed_sede_id'] ? 'selected' : '' ?>><?= htmlspecialchars($s['sed_nombre']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="card card-outline card-warning">
                        <div class="card-header"><h3 class="card-title"><i class="fas fa-user-friends mr-2"></i>Representante</h3></div>
                        <div class="card-body">
                            <div class="form-group">
                                <label>Buscar Representante</label>
                                <input type="text" id="buscarRepresentante" class="form-control form-control-sm" placeholder="Nombre o identificación...">
                                <input type="hidden" name="representante_id" id="representante_id" value="<?= htmlspecialchars($alumno['alu_representante_id'] ?? '') ?>">
                                <div id="representanteInfo" class="mt-2">
                                    <?php if (!empty($alumno['rep_nombres'])): ?>
                                    <div class="alert alert-success py-1 px-2 mb-0">
                                        <strong><?= htmlspecialchars($alumno['rep_nombres'] . ' ' . $alumno['rep_apellidos']) ?></strong>
                                        <button type="button" class="close" onclick="limpiarRepresentante()"><small>&times;</small></button>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card card-outline card-info">
                        <div class="card-header"><h3 class="card-title"><i class="fas fa-layer-group mr-2"></i>Nivel de Natación</h3></div>
                        <div class="card-body">
                            <div class="form-group">
                                <label>Nivel Actual</label>
                                <select name="nivel_actual_id" class="form-control">
                                    <option value="">— Sin asignar —</option>
                                    <?php foreach ($niveles as $n): ?>
                                    <option value="<?= $n['nnv_nivel_id'] ?>" <?= ($alumno['nfa_nivel_actual_id'] ?? '') == $n['nnv_nivel_id'] ? 'selected' : '' ?>><?= htmlspecialchars($n['nnv_nombre']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Notas de Ficha</label>
                                <textarea name="notas_ficha" class="form-control" rows="2"><?= htmlspecialchars($alumno['nfa_notas'] ?? '') ?></textarea>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-block" style="background:<?= $moduloColor ?>;color:white;">
                        <i class="fas fa-save mr-1"></i> <?= $editando ? 'Actualizar Alumno' : 'Registrar Alumno' ?>
                    </button>
                </div>
            </div>
        </form>
    </div>
</section>

<?php ob_start(); ?>
<script>
// Validación de cédula Ecuador (cliente-side básica)
$('#identificacion').on('blur', function() {
    var val = $(this).val().trim();
    var tipo = $('#tipo_id').val();
    var fb = $('#id_feedback');
    if (tipo === 'CED' && val.length === 10) {
        var d = val.split('').map(Number);
        var prov = d[0]*10 + d[1];
        if (prov < 1 || prov > 24) { fb.text('Provincia inválida').removeClass('text-success').addClass('text-danger'); return; }
        var sum = 0;
        for (var i = 0; i < 9; i++) { var k = d[i] * (i % 2 === 0 ? 2 : 1); sum += k > 9 ? k - 9 : k; }
        var check = (10 - (sum % 10)) % 10;
        if (check === d[9]) { fb.text('✓ Cédula válida').removeClass('text-danger').addClass('text-success'); }
        else { fb.text('✗ Cédula inválida').removeClass('text-success').addClass('text-danger'); }
    } else { fb.text(''); }
});

// Buscar representante (AJAX)
var timerRep;
$('#buscarRepresentante').on('input', function() {
    clearTimeout(timerRep);
    var q = $(this).val();
    if (q.length < 2) return;
    timerRep = setTimeout(function() {
        $.getJSON('<?= url('natacion', 'alumno', 'buscarRepresentante') ?>&q=' + encodeURIComponent(q), function(res) {
            if (res.success && res.data.length) {
                var html = '<div class="list-group">';
                res.data.forEach(function(c) {
                    html += '<a href="#" class="list-group-item list-group-item-action py-1 selRep" data-id="' + c.cli_cliente_id + '" data-nombre="' + c.cli_nombres + ' ' + c.cli_apellidos + '">' + c.cli_nombres + ' ' + c.cli_apellidos + '</a>';
                });
                html += '</div>';
                $('#representanteInfo').html(html);
            }
        });
    }, 300);
});

$(document).on('click', '.selRep', function(e) {
    e.preventDefault();
    $('#representante_id').val($(this).data('id'));
    $('#representanteInfo').html('<div class="alert alert-success py-1 px-2 mb-0"><strong>' + $(this).data('nombre') + '</strong><button type="button" class="close" onclick="limpiarRepresentante()"><small>&times;</small></button></div>');
});

function limpiarRepresentante() {
    $('#representante_id').val('');
    $('#representanteInfo').html('');
}
</script>
<?php $scripts = ob_get_clean(); ?>
