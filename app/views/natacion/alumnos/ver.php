<?php
/**
 * DigiSports Natación - Ver detalle de alumno
 */
$a           = $alumno ?? [];
$ficha       = $ficha ?? [];
$inscripciones = $inscripciones ?? [];
$moduloColor = $modulo_actual['color'] ?? '#0EA5E9';
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-user-graduate mr-2" style="color:<?= $moduloColor ?>"></i>Ficha del Alumno</h1>
            </div>
            <div class="col-sm-6">
                <div class="float-sm-right">
                    <a href="<?= url('natacion', 'alumno', 'editar') ?>&id=<?= $a['alu_alumno_id'] ?? '' ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit mr-1"></i>Editar</a>
                    <a href="<?= url('natacion', 'alumno', 'index') ?>" class="btn btn-sm btn-outline-secondary"><i class="fas fa-arrow-left mr-1"></i>Volver</a>
                </div>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <!-- Tarjeta Principal -->
            <div class="col-lg-4">
                <div class="card card-outline" style="border-top-color:<?= $moduloColor ?>">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <i class="fas fa-user-circle fa-5x" style="color:<?= $moduloColor ?>"></i>
                        </div>
                        <h4><?= htmlspecialchars(($a['alu_nombres'] ?? '') . ' ' . ($a['alu_apellidos'] ?? '')) ?></h4>
                        <p class="text-muted"><?= htmlspecialchars($a['alu_identificacion'] ?? '—') ?></p>
                        <?php if ($a['alu_estado'] === 'ACTIVO'): ?>
                        <span class="badge badge-success badge-lg">Activo</span>
                        <?php else: ?>
                        <span class="badge badge-secondary badge-lg">Inactivo</span>
                        <?php endif; ?>
                    </div>
                    <div class="card-footer bg-white">
                        <ul class="list-unstyled mb-0">
                            <li class="mb-2"><i class="fas fa-birthday-cake mr-2 text-info"></i><?= !empty($a['alu_fecha_nacimiento']) ? date('d/m/Y', strtotime($a['alu_fecha_nacimiento'])) : '—' ?></li>
                            <li class="mb-2"><i class="fas fa-venus-mars mr-2 text-info"></i><?= $a['alu_genero'] === 'M' ? 'Masculino' : ($a['alu_genero'] === 'F' ? 'Femenino' : '—') ?></li>
                            <li class="mb-2"><i class="fas fa-envelope mr-2 text-info"></i><?= htmlspecialchars($a['alu_email'] ?? '—') ?></li>
                            <li class="mb-2"><i class="fas fa-phone mr-2 text-info"></i><?= htmlspecialchars($a['alu_telefono'] ?? '—') ?></li>
                            <li><i class="fas fa-map-marker-alt mr-2 text-info"></i><?= htmlspecialchars($a['alu_direccion'] ?? '—') ?></li>
                        </ul>
                    </div>
                </div>

                <!-- Representante -->
                <?php if (!empty($a['rep_nombres'])): ?>
                <div class="card">
                    <div class="card-header"><h3 class="card-title"><i class="fas fa-user-friends mr-2"></i>Representante</h3></div>
                    <div class="card-body">
                        <strong><?= htmlspecialchars($a['rep_nombres'] . ' ' . $a['rep_apellidos']) ?></strong>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Detalle -->
            <div class="col-lg-8">
                <!-- Ficha Natación -->
                <div class="card">
                    <div class="card-header" style="background:<?= $moduloColor ?>20;">
                        <h3 class="card-title"><i class="fas fa-swimmer mr-2" style="color:<?= $moduloColor ?>"></i>Ficha de Natación</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 text-center">
                                <h6 class="text-muted">Nivel Actual</h6>
                                <?php if (!empty($ficha['nivel_nombre'])): ?>
                                <span class="badge badge-lg p-2" style="background:<?= htmlspecialchars($ficha['nivel_color'] ?? '#6c757d') ?>;color:white;font-size:1rem;">
                                    <?= htmlspecialchars($ficha['nivel_nombre']) ?>
                                </span>
                                <?php else: ?>
                                <span class="text-muted">Sin asignar</span>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-4 text-center">
                                <h6 class="text-muted">Fecha Ingreso</h6>
                                <strong><?= !empty($ficha['nfa_fecha_ingreso']) ? date('d/m/Y', strtotime($ficha['nfa_fecha_ingreso'])) : '—' ?></strong>
                            </div>
                            <div class="col-md-4 text-center">
                                <h6 class="text-muted">Estado Ficha</h6>
                                <span class="badge badge-<?= ($ficha['nfa_activo'] ?? 0) ? 'success' : 'secondary' ?>"><?= ($ficha['nfa_activo'] ?? 0) ? 'Activa' : 'Inactiva' ?></span>
                            </div>
                        </div>
                        <?php if (!empty($ficha['nfa_notas'])): ?>
                        <hr>
                        <p class="mb-0"><strong>Notas:</strong> <?= nl2br(htmlspecialchars($ficha['nfa_notas'])) ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Observaciones Médicas -->
                <?php if (!empty($a['alu_observaciones_medicas'])): ?>
                <div class="card card-outline card-danger">
                    <div class="card-header"><h3 class="card-title"><i class="fas fa-heartbeat mr-2 text-danger"></i>Observaciones Médicas</h3></div>
                    <div class="card-body"><?= nl2br(htmlspecialchars($a['alu_observaciones_medicas'])) ?></div>
                </div>
                <?php endif; ?>

                <!-- Inscripciones -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-clipboard-list mr-2"></i>Inscripciones</h3>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($inscripciones)): ?>
                        <div class="text-center py-3 text-muted">Sin inscripciones registradas</div>
                        <?php else: ?>
                        <table class="table table-sm mb-0">
                            <thead class="thead-light">
                                <tr><th>Grupo</th><th>Fecha</th><th>Estado</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($inscripciones as $ins): ?>
                                <tr>
                                    <td><?= htmlspecialchars($ins['ngr_nombre'] ?? $ins['grupo'] ?? '—') ?></td>
                                    <td><?= date('d/m/Y', strtotime($ins['nis_fecha_inscripcion'])) ?></td>
                                    <td>
                                        <?php $bc = ['ACTIVA'=>'success','CANCELADA'=>'danger','SUSPENDIDA'=>'warning','COMPLETADA'=>'info'][$ins['nis_estado']] ?? 'secondary'; ?>
                                        <span class="badge badge-<?= $bc ?>"><?= $ins['nis_estado'] ?></span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
