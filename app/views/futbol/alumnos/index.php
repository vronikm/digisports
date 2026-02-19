<?php
/**
 * DigiSports Fútbol - Gestión de Alumnos (listado)
 * @vars $alumnos, $categorias, $grupos, $sedes, $sede_activa, $csrf_token, $modulo_actual
 */
$alumnos     = $alumnos ?? [];
$categorias  = $categorias ?? [];
$grupos      = $grupos ?? [];
$sedes       = $sedes ?? [];
$sedeActiva  = $sede_activa ?? null;
$moduloColor = $modulo_actual['color'] ?? '#22C55E';
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-futbol mr-2" style="color:<?= $moduloColor ?>"></i>Alumnos - Fútbol</h1>
            </div>
            <div class="col-sm-6">
                <div class="float-sm-right">
                    <a href="<?= url('futbol', 'alumno', 'crear') ?>" class="btn btn-sm" style="background:<?= $moduloColor ?>;color:white;">
                        <i class="fas fa-user-plus mr-1"></i>Nuevo Alumno
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <!-- Filtro Sede -->
        <?php if (!empty($sedes) && count($sedes) > 1): ?>
        <div class="row mb-3">
            <div class="col-md-4">
                <div class="input-group input-group-sm">
                    <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-building"></i></span></div>
                    <select id="sedeFilter" class="form-control" onchange="filtrarPorSede(this.value)">
                        <option value="">Todas las sedes</option>
                        <?php foreach ($sedes as $s): ?>
                        <option value="<?= $s['sed_sede_id'] ?>" <?= $sedeActiva == $s['sed_sede_id'] ? 'selected' : '' ?>><?= htmlspecialchars($s['sed_nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Filtros -->
        <div class="card card-outline" style="border-top-color:<?= $moduloColor ?>">
            <div class="card-body py-2">
                <form method="GET" action="<?= url('futbol', 'alumno', 'index') ?>" class="row align-items-end">
                    <div class="col-md-3">
                        <label class="small">Buscar</label>
                        <input type="text" name="q" class="form-control form-control-sm" placeholder="Nombres, apellidos, cédula..." value="<?= htmlspecialchars($q ?? '') ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="small">Categoría</label>
                        <select name="categoria_id" class="form-control form-control-sm">
                            <option value="">— Todas —</option>
                            <?php foreach ($categorias as $cat): ?>
                            <option value="<?= $cat['fct_categoria_id'] ?>" <?= ($categoria_id ?? '') == $cat['fct_categoria_id'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['fct_nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="small">Grupo</label>
                        <select name="grupo_id" class="form-control form-control-sm">
                            <option value="">— Todos —</option>
                            <?php foreach ($grupos as $g): ?>
                            <option value="<?= $g['fgr_grupo_id'] ?>" <?= ($grupo_id ?? '') == $g['fgr_grupo_id'] ? 'selected' : '' ?>><?= htmlspecialchars($g['fgr_nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="small">Estado</label>
                        <select name="estado" class="form-control form-control-sm">
                            <option value="">— Todos —</option>
                            <option value="ACTIVO" <?= ($estado ?? '') === 'ACTIVO' ? 'selected' : '' ?>>Activo</option>
                            <option value="INACTIVO" <?= ($estado ?? '') === 'INACTIVO' ? 'selected' : '' ?>>Inactivo</option>
                            <option value="RETIRADO" <?= ($estado ?? '') === 'RETIRADO' ? 'selected' : '' ?>>Retirado</option>
                        </select>
                    </div>
                    <div class="col-md-2 text-right">
                        <button class="btn btn-sm btn-primary"><i class="fas fa-search mr-1"></i>Filtrar</button>
                        <a href="<?= url('futbol', 'alumno', 'index') ?>" class="btn btn-sm btn-outline-secondary ml-1">Limpiar</a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabla -->
        <div class="card shadow-sm">
            <div class="card-header py-2">
                <span class="badge badge-secondary"><?= count($alumnos) ?> alumno(s)</span>
            </div>
            <div class="card-body p-0">
                <?php if (empty($alumnos)): ?>
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-futbol fa-3x mb-3 opacity-50"></i>
                    <p>No hay alumnos inscritos en fútbol</p>
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="tablaAlumnos">
                        <thead class="thead-light">
                            <tr>
                                <th width="40">#</th>
                                <th width="50">Foto</th>
                                <th>Nombres</th>
                                <th>Apellidos</th>
                                <th>Cédula</th>
                                <th>F. Nacimiento</th>
                                <th class="text-center">Edad</th>
                                <th>Cat. Sugerida</th>
                                <th>Grupo Actual</th>
                                <th class="text-center">Estado</th>
                                <th width="150" class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($alumnos as $i => $a): ?>
                            <?php
                                // Calcular edad
                                $edad = '';
                                $categoriaSugerida = '—';
                                if (!empty($a['alu_fecha_nacimiento'])) {
                                    $nacimiento = new DateTime($a['alu_fecha_nacimiento']);
                                    $hoy = new DateTime();
                                    $edad = $nacimiento->diff($hoy)->y;
                                    // Buscar categoría sugerida por edad
                                    foreach ($categorias as $cat) {
                                        $emin = $cat['fct_edad_min'] ?? 0;
                                        $emax = $cat['fct_edad_max'] ?? 99;
                                        if ($edad >= $emin && $edad <= $emax) {
                                            $categoriaSugerida = '<span class="badge" style="background:' . htmlspecialchars($cat['fct_color'] ?? '#6c757d') . ';color:white;">' . htmlspecialchars($cat['fct_nombre']) . '</span>';
                                            break;
                                        }
                                    }
                                }
                            ?>
                            <tr>
                                <td class="text-muted"><?= $i + 1 ?></td>
                                <td>
                                    <?php if (!empty($a['alu_foto'])): ?>
                                    <img src="<?= htmlspecialchars($a['alu_foto']) ?>" alt="Foto" class="img-circle" width="32" height="32" style="object-fit:cover;">
                                    <?php else: ?>
                                    <span class="img-circle bg-secondary d-inline-flex align-items-center justify-content-center" style="width:32px;height:32px;"><i class="fas fa-user text-white" style="font-size:.75rem;"></i></span>
                                    <?php endif; ?>
                                </td>
                                <td><strong><?= htmlspecialchars($a['alu_nombres'] ?? '') ?></strong></td>
                                <td><?= htmlspecialchars($a['alu_apellidos'] ?? '') ?></td>
                                <td><code><?= htmlspecialchars($a['alu_identificacion'] ?? '—') ?></code></td>
                                <td>
                                    <?php if (!empty($a['alu_fecha_nacimiento'])): ?>
                                    <?= date('d/m/Y', strtotime($a['alu_fecha_nacimiento'])) ?>
                                    <?php else: ?>
                                    <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if ($edad !== ''): ?>
                                    <span class="badge badge-light"><?= $edad ?> años</span>
                                    <?php else: ?>
                                    <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= $categoriaSugerida ?></td>
                                <td>
                                    <?php if (!empty($a['grupo_nombre'])): ?>
                                    <?php if (!empty($a['grupo_color'])): ?><span class="badge mr-1" style="background:<?= htmlspecialchars($a['grupo_color']) ?>">&nbsp;</span><?php endif; ?>
                                    <?= htmlspecialchars($a['grupo_nombre']) ?>
                                    <?php else: ?>
                                    <span class="text-muted">Sin grupo</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php
                                        $estadoInsc = $a['estado_inscripcion'] ?? $a['alu_estado'] ?? 'ACTIVO';
                                        $estadoBadge = ['ACTIVO'=>'success','ACTIVA'=>'success','INACTIVO'=>'secondary','RETIRADO'=>'danger','SUSPENDIDO'=>'warning','CANCELADA'=>'danger'][$estadoInsc] ?? 'light';
                                    ?>
                                    <span class="badge badge-<?= $estadoBadge ?>"><?= $estadoInsc ?></span>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-info" onclick="verFicha(<?= $a['alu_alumno_id'] ?>)" title="Ver Ficha"><i class="fas fa-id-card"></i></button>
                                        <button class="btn btn-outline-success" onclick="inscribirAlumno(<?= $a['alu_alumno_id'] ?>)" title="Inscribir"><i class="fas fa-user-plus"></i></button>
                                        <a href="<?= url('futbol', 'alumno', 'editar') ?>&id=<?= $a['alu_alumno_id'] ?>" class="btn btn-outline-primary" title="Editar"><i class="fas fa-edit"></i></a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php ob_start(); ?>
<script>
$(function() {
    if ($('#tablaAlumnos').length && $('#tablaAlumnos tbody tr').length > 0) {
        $('#tablaAlumnos').DataTable({
            language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' },
            pageLength: 25,
            order: [[2, 'asc']],
            responsive: true
        });
    }
});

function filtrarPorSede(sedeId) {
    $.post('<?= url('futbol', 'sede', 'seleccionar') ?>', { id: sedeId, csrf_token: '<?= $csrf_token ?? '' ?>' }, function() { location.reload(); }, 'json');
}

function verFicha(alumnoId) {
    window.location.href = '<?= url('futbol', 'alumno', 'ver') ?>&id=' + alumnoId;
}

function inscribirAlumno(alumnoId) {
    window.location.href = '<?= url('futbol', 'inscripcion', 'crear') ?>&alumno_id=' + alumnoId;
}
</script>
<?php $scripts = ob_get_clean(); ?>
