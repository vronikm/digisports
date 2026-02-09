<?php
/**
 * Detalle de Mantenimiento — DigiSports Arena
 * @var array $mantenimiento
 * @var array $historial
 */
$estadosColores = [
    'PROGRAMADO'  => 'primary',
    'EN_PROGRESO' => 'warning',
    'COMPLETADO'  => 'success',
    'CANCELADO'   => 'danger'
];
$estadoActual = $mantenimiento['estado'] ?? 'PROGRAMADO';
?>

<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">
                    <i class="fas fa-wrench text-primary"></i>
                    Mantenimiento #<?php echo $mantenimiento['mantenimiento_id']; ?>
                    <span class="badge badge-<?php echo $estadosColores[$estadoActual] ?? 'secondary'; ?> ml-2">
                        <?php echo str_replace('_', ' ', $estadoActual); ?>
                    </span>
                </h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?php echo url('instalaciones', 'dashboard', 'index'); ?>">Arena</a></li>
                    <li class="breadcrumb-item"><a href="<?php echo url('instalaciones', 'mantenimiento', 'index'); ?>">Mantenimientos</a></li>
                    <li class="breadcrumb-item active">Detalle</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">

        <!-- Progreso visual -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <?php
                            $pasos = ['PROGRAMADO', 'EN_PROGRESO', 'COMPLETADO'];
                            $pasoActual = array_search($estadoActual, $pasos);
                            if ($pasoActual === false) $pasoActual = -1;
                            $labels = ['Programado', 'En Progreso', 'Completado'];
                            $iconos = ['fas fa-calendar', 'fas fa-cog fa-spin', 'fas fa-check-circle'];
                            foreach ($pasos as $idx => $paso):
                                $activo = $idx <= $pasoActual;
                            ?>
                            <div class="text-center" style="flex: 1;">
                                <i class="<?php echo $iconos[$idx]; ?> fa-2x <?php echo $activo ? 'text-' . $estadosColores[$paso] : 'text-muted'; ?>"></i>
                                <p class="small mt-1 mb-0 <?php echo $activo ? 'font-weight-bold' : 'text-muted'; ?>">
                                    <?php echo $labels[$idx]; ?>
                                </p>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="progress" style="height: 6px;">
                            <?php
                            $porcentaje = $estadoActual === 'CANCELADO' ? 0 : (($pasoActual + 1) / count($pasos)) * 100;
                            ?>
                            <div class="progress-bar bg-<?php echo $estadosColores[$estadoActual] ?? 'secondary'; ?>" 
                                 style="width: <?php echo $porcentaje; ?>%"></div>
                        </div>
                        <?php if ($estadoActual === 'CANCELADO'): ?>
                        <p class="text-danger text-center mt-2 mb-0">
                            <i class="fas fa-times-circle"></i> Este mantenimiento fue cancelado
                        </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Información principal -->
            <div class="col-md-6">
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-info-circle"></i> Información del Mantenimiento</h3>
                    </div>
                    <div class="card-body">
                        <dl class="row mb-0">
                            <dt class="col-sm-5">Tipo</dt>
                            <dd class="col-sm-7">
                                <span class="badge badge-secondary"><?php echo ucfirst(str_replace('_', ' ', $mantenimiento['tipo'])); ?></span>
                            </dd>

                            <dt class="col-sm-5">Descripción</dt>
                            <dd class="col-sm-7"><?php echo nl2br(htmlspecialchars($mantenimiento['descripcion'])); ?></dd>

                            <dt class="col-sm-5">Fecha Inicio</dt>
                            <dd class="col-sm-7"><?php echo date('d/m/Y H:i', strtotime($mantenimiento['fecha_inicio'])); ?></dd>

                            <dt class="col-sm-5">Fecha Fin</dt>
                            <dd class="col-sm-7"><?php echo date('d/m/Y H:i', strtotime($mantenimiento['fecha_fin'])); ?></dd>

                            <dt class="col-sm-5">Duración</dt>
                            <dd class="col-sm-7">
                                <?php
                                $diff = strtotime($mantenimiento['fecha_fin']) - strtotime($mantenimiento['fecha_inicio']);
                                $dias = floor($diff / 86400);
                                $horas = floor(($diff % 86400) / 3600);
                                echo $dias > 0 ? "{$dias}d {$horas}h" : "{$horas} horas";
                                ?>
                            </dd>

                            <?php if (!empty($mantenimiento['recurrir']) && $mantenimiento['recurrir'] === 'SI'): ?>
                            <dt class="col-sm-5">Recurrencia</dt>
                            <dd class="col-sm-7">
                                <span class="badge badge-info"><?php echo htmlspecialchars($mantenimiento['cadencia_recurrencia'] ?? 'Recurrente'); ?></span>
                            </dd>
                            <?php endif; ?>

                            <?php if (!empty($mantenimiento['notas'])): ?>
                            <dt class="col-sm-5">Notas</dt>
                            <dd class="col-sm-7"><em><?php echo nl2br(htmlspecialchars($mantenimiento['notas'])); ?></em></dd>
                            <?php endif; ?>
                        </dl>
                    </div>
                </div>
            </div>

            <!-- Cancha y responsable -->
            <div class="col-md-6">
                <div class="card card-success card-outline">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-th-large"></i> Cancha Asignada</h3>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between">
                                <strong>Cancha</strong>
                                <a href="<?php echo url('instalaciones', 'cancha', 'ver', ['id' => $mantenimiento['cancha_id']]); ?>">
                                    <?php echo htmlspecialchars($mantenimiento['cancha_nombre']); ?>
                                </a>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <strong>Tipo</strong>
                                <span><?php echo ucfirst($mantenimiento['cancha_tipo'] ?? ''); ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <strong>Instalación</strong>
                                <span><?php echo htmlspecialchars($mantenimiento['instalacion_nombre']); ?></span>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="card card-warning card-outline">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-user-cog"></i> Responsable</h3>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($mantenimiento['responsable_nombre'])): ?>
                        <div class="d-flex align-items-center">
                            <div class="profile-user-img img-fluid img-circle bg-warning text-white d-flex align-items-center justify-content-center mr-3" 
                                 style="width: 50px; height: 50px; font-size: 1.2rem; min-width: 50px;">
                                <?php echo strtoupper(substr($mantenimiento['responsable_nombre'], 0, 1)); ?>
                            </div>
                            <div>
                                <strong><?php echo htmlspecialchars($mantenimiento['responsable_nombre']); ?></strong><br>
                                <small class="text-muted"><?php echo htmlspecialchars($mantenimiento['responsable_email'] ?? ''); ?></small>
                            </div>
                        </div>
                        <?php else: ?>
                        <p class="text-muted mb-0"><i class="fas fa-user-slash"></i> Sin responsable asignado</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Historial de la cancha -->
        <?php if (!empty($historial)): ?>
        <div class="row">
            <div class="col-12">
                <div class="card card-secondary card-outline">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-history"></i> Otros Mantenimientos de esta Cancha</h3>
                    </div>
                    <div class="card-body table-responsive p-0">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>#</th>
                                    <th>Tipo</th>
                                    <th>Fecha Inicio</th>
                                    <th>Fecha Fin</th>
                                    <th>Estado</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($historial as $h): ?>
                                <tr>
                                    <td><?php echo $h['mantenimiento_id']; ?></td>
                                    <td><?php echo ucfirst(str_replace('_', ' ', $h['tipo'])); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($h['fecha_inicio'])); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($h['fecha_fin'])); ?></td>
                                    <td>
                                        <span class="badge badge-<?php echo $estadosColores[$h['estado']] ?? 'secondary'; ?>">
                                            <?php echo str_replace('_', ' ', $h['estado']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="<?php echo url('instalaciones', 'mantenimiento', 'ver', ['id' => $h['mantenimiento_id']]); ?>" 
                                           class="btn btn-xs btn-outline-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Acciones -->
        <div class="row mb-4">
            <div class="col-12">
                <a href="<?php echo url('instalaciones', 'mantenimiento', 'index'); ?>" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Volver al Listado
                </a>
                <a href="<?php echo url('instalaciones', 'mantenimiento', 'editar', ['id' => $mantenimiento['mantenimiento_id']]); ?>" class="btn btn-primary ml-2">
                    <i class="fas fa-edit"></i> Editar
                </a>
                <?php if ($estadoActual !== 'COMPLETADO' && $estadoActual !== 'CANCELADO'): ?>
                <button type="button" class="btn btn-success ml-2 btn-completar" 
                        data-id="<?php echo $mantenimiento['mantenimiento_id']; ?>">
                    <i class="fas fa-check"></i> Marcar Completado
                </button>
                <?php endif; ?>
            </div>
        </div>

    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var btnCompletar = document.querySelector('.btn-completar');
    if (btnCompletar) {
        btnCompletar.addEventListener('click', function() {
            var id = this.dataset.id;
            Swal.fire({
                title: '¿Completar mantenimiento?',
                text: 'Se marcará como completado.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: '<i class="fas fa-check"></i> Sí, completar',
                cancelButtonText: 'Cancelar'
            }).then(function(result) {
                if (result.isConfirmed) {
                    window.location.href = '<?php echo url("instalaciones", "mantenimiento", "cambiarEstado"); ?>&id=' + id + '&estado=COMPLETADO';
                }
            });
        });
    }
});
</script>
