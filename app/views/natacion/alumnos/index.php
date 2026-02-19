<?php
/**
 * DigiSports Natación - Gestión de Alumnos (listado)
 */
$alumnos     = $alumnos ?? [];
$niveles     = $niveles ?? [];
$moduloColor = $modulo_actual['color'] ?? '#0EA5E9';
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-user-graduate mr-2" style="color:<?= $moduloColor ?>"></i>Alumnos</h1>
            </div>
            <div class="col-sm-6">
                <div class="float-sm-right">
                    <a href="<?= url('natacion', 'alumno', 'crear') ?>" class="btn btn-sm" style="background:<?= $moduloColor ?>;color:white;">
                        <i class="fas fa-plus mr-1"></i> Nuevo Alumno
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <!-- Filtros -->
        <div class="card card-outline" style="border-top-color:<?= $moduloColor ?>">
            <div class="card-body py-2">
                <form method="GET" action="<?= url('natacion', 'alumno', 'index') ?>" class="row align-items-end">
                    <div class="col-md-4">
                        <label class="small">Buscar</label>
                        <input type="text" name="q" class="form-control form-control-sm" placeholder="Nombres, apellidos..." value="<?= htmlspecialchars($q ?? '') ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="small">Nivel</label>
                        <select name="nivel_id" class="form-control form-control-sm">
                            <option value="">— Todos —</option>
                            <?php foreach ($niveles as $n): ?>
                            <option value="<?= $n['nnv_nivel_id'] ?>" <?= ($nivel_id ?? '') == $n['nnv_nivel_id'] ? 'selected' : '' ?>><?= htmlspecialchars($n['nnv_nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="small">Estado</label>
                        <select name="estado" class="form-control form-control-sm">
                            <option value="">— Todos —</option>
                            <option value="ACTIVO" <?= ($estado ?? '') === 'ACTIVO' ? 'selected' : '' ?>>Activo</option>
                            <option value="INACTIVO" <?= ($estado ?? '') === 'INACTIVO' ? 'selected' : '' ?>>Inactivo</option>
                        </select>
                    </div>
                    <div class="col-md-3 text-right">
                        <button class="btn btn-sm btn-primary"><i class="fas fa-search mr-1"></i>Filtrar</button>
                        <a href="<?= url('natacion', 'alumno', 'index') ?>" class="btn btn-sm btn-outline-secondary ml-1">Limpiar</a>
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
                    <i class="fas fa-user-graduate fa-3x mb-3 opacity-50"></i>
                    <p>No hay alumnos registrados</p>
                    <a href="<?= url('natacion', 'alumno', 'crear') ?>" class="btn btn-sm" style="background:<?= $moduloColor ?>;color:white;">
                        <i class="fas fa-plus mr-1"></i> Registrar primer alumno
                    </a>
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th width="40">#</th>
                                <th>Alumno</th>
                                <th>Identificación</th>
                                <th>Nivel Actual</th>
                                <th>Representante</th>
                                <th class="text-center">Estado</th>
                                <th width="140" class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($alumnos as $i => $a): ?>
                            <tr>
                                <td class="text-muted"><?= $i + 1 ?></td>
                                <td>
                                    <strong><?= htmlspecialchars($a['alu_apellidos'] . ', ' . $a['alu_nombres']) ?></strong>
                                    <?php if (!empty($a['alu_fecha_nacimiento'])): ?>
                                    <br><small class="text-muted"><i class="fas fa-birthday-cake mr-1"></i><?= date('d/m/Y', strtotime($a['alu_fecha_nacimiento'])) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><code><?= htmlspecialchars($a['alu_identificacion'] ?? '—') ?></code></td>
                                <td>
                                    <?php if (!empty($a['nivel_nombre'])): ?>
                                    <span class="badge" style="background:<?= htmlspecialchars($a['nivel_color'] ?? '#6c757d') ?>;color:white;"><?= htmlspecialchars($a['nivel_nombre']) ?></span>
                                    <?php else: ?>
                                    <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars(($a['rep_nombres'] ?? '') . ' ' . ($a['rep_apellidos'] ?? '')) ?: '<span class="text-muted">—</span>' ?></td>
                                <td class="text-center">
                                    <?php if ($a['alu_estado'] === 'ACTIVO'): ?>
                                    <span class="badge badge-success">Activo</span>
                                    <?php else: ?>
                                    <span class="badge badge-secondary">Inactivo</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= url('natacion', 'alumno', 'ver') ?>&id=<?= $a['alu_alumno_id'] ?>" class="btn btn-outline-info" title="Ver"><i class="fas fa-eye"></i></a>
                                        <a href="<?= url('natacion', 'alumno', 'editar') ?>&id=<?= $a['alu_alumno_id'] ?>" class="btn btn-outline-primary" title="Editar"><i class="fas fa-edit"></i></a>
                                        <button class="btn btn-outline-danger" onclick="eliminarAlumno(<?= $a['alu_alumno_id'] ?>, '<?= htmlspecialchars($a['alu_nombres'] . ' ' . $a['alu_apellidos']) ?>')" title="Eliminar"><i class="fas fa-trash"></i></button>
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
function eliminarAlumno(id, nombre) {
    Swal.fire({
        title: '¿Desactivar alumno?',
        html: 'Se desactivará a <strong>' + nombre + '</strong>',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'Sí, desactivar',
        cancelButtonText: 'Cancelar'
    }).then(function(r) {
        if (r.isConfirmed) { window.location.href = '<?= url('natacion', 'alumno', 'eliminar') ?>&id=' + id; }
    });
}
</script>
<?php $scripts = ob_get_clean(); ?>
