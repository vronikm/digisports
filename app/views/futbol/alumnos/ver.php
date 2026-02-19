<?php
/**
 * DigiSports Fútbol - Ver Ficha del Alumno
 * Vista detallada con datos personales, representante, ficha deportiva, inscripciones y evaluaciones
 * 
 * @vars $alumno, $ficha, $hermanos, $inscripciones, $evaluaciones, $asistencia_resumen, $modulo_actual, $csrf_token
 */
$a             = $alumno ?? [];
$ficha         = $ficha ?? [];
$hermanos      = $hermanos ?? [];
$inscripciones = $inscripciones ?? [];
$evaluaciones  = $evaluaciones ?? [];
$asistencia    = $asistencia_resumen ?? [];
$moduloColor   = $modulo_actual['color'] ?? '#22C55E';
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">
                    <i class="fas fa-id-card mr-2" style="color:<?= $moduloColor ?>"></i>Ficha del Alumno
                </h1>
            </div>
            <div class="col-sm-6">
                <div class="float-sm-right">
                    <a href="<?= url('futbol', 'alumno', 'editar') ?>&id=<?= $a['alu_alumno_id'] ?? '' ?>" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-edit mr-1"></i>Editar
                    </a>
                    <a href="<?= url('futbol', 'alumno', 'index') ?>" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-arrow-left mr-1"></i>Volver
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <!-- ========== COLUMNA IZQUIERDA: Tarjeta + Representante ========== -->
            <div class="col-lg-4">
                <!-- Tarjeta Principal del Alumno -->
                <div class="card card-outline" style="border-top-color:<?= $moduloColor ?>">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <?php if (!empty($a['alu_foto'])): ?>
                            <img src="<?= htmlspecialchars($a['alu_foto']) ?>" alt="Foto" class="img-circle elevation-2" style="width:100px;height:100px;object-fit:cover;">
                            <?php else: ?>
                            <i class="fas fa-user-circle fa-5x" style="color:<?= $moduloColor ?>"></i>
                            <?php endif; ?>
                        </div>
                        <h4 class="mb-1"><?= htmlspecialchars(($a['alu_nombres'] ?? '') . ' ' . ($a['alu_apellidos'] ?? '')) ?></h4>
                        <p class="text-muted mb-2">
                            <?php if (!empty($a['alu_identificacion'])): ?>
                            <code><?= htmlspecialchars($a['alu_identificacion']) ?></code>
                            <?php else: ?>
                            <span class="text-muted">Sin identificación</span>
                            <?php endif; ?>
                        </p>
                        <?php
                        $estadoBadge = ['ACTIVO'=>'success','INACTIVO'=>'secondary','SUSPENDIDO'=>'warning'][$a['alu_estado'] ?? ''] ?? 'light';
                        ?>
                        <span class="badge badge-<?= $estadoBadge ?> px-3 py-1" style="font-size:.9rem;"><?= $a['alu_estado'] ?? '—' ?></span>
                    </div>
                    <div class="card-footer bg-white">
                        <ul class="list-unstyled mb-0">
                            <?php
                            // Calcular edad
                            $edadTxt = '—';
                            if (!empty($a['alu_fecha_nacimiento'])) {
                                $nac = new DateTime($a['alu_fecha_nacimiento']);
                                $hoy = new DateTime();
                                $edadTxt = $nac->diff($hoy)->y . ' años';
                            }
                            ?>
                            <li class="mb-2"><i class="fas fa-birthday-cake mr-2 text-info"></i>
                                <?= !empty($a['alu_fecha_nacimiento']) ? date('d/m/Y', strtotime($a['alu_fecha_nacimiento'])) . " ({$edadTxt})" : '—' ?>
                            </li>
                            <li class="mb-2"><i class="fas fa-venus-mars mr-2 text-info"></i>
                                <?= ($a['alu_genero'] ?? '') === 'M' ? 'Masculino' : (($a['alu_genero'] ?? '') === 'F' ? 'Femenino' : '—') ?>
                            </li>
                            <li class="mb-2"><i class="fas fa-envelope mr-2 text-info"></i>
                                <?= htmlspecialchars($a['alu_email'] ?? '—') ?>
                            </li>
                            <li class="mb-2"><i class="fas fa-phone mr-2 text-info"></i>
                                <?= htmlspecialchars($a['alu_telefono'] ?? '—') ?>
                            </li>
                            <li class="mb-2"><i class="fas fa-map-marker-alt mr-2 text-info"></i>
                                <?= htmlspecialchars($a['alu_direccion'] ?? '—') ?>
                            </li>
                            <?php if (!empty($a['sede_nombre'])): ?>
                            <li><i class="fas fa-building mr-2 text-info"></i>
                                <?= htmlspecialchars($a['sede_nombre']) ?>
                            </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>

                <!-- Representante -->
                <?php if (!empty($a['rep_nombre_completo'])): ?>
                <div class="card card-outline card-warning">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-user-friends mr-2"></i>Representante</h3>
                    </div>
                    <div class="card-body">
                        <h5 class="mb-2"><?= htmlspecialchars($a['rep_nombre_completo']) ?></h5>
                        <?php if (!empty($a['rep_identificacion'])): ?>
                        <p class="mb-1"><i class="fas fa-id-card mr-2 text-muted"></i>
                            <code><?= htmlspecialchars($a['rep_identificacion']) ?></code>
                        </p>
                        <?php endif; ?>
                        <?php if (!empty($a['alu_parentesco'])): ?>
                        <p class="mb-1"><i class="fas fa-link mr-2 text-muted"></i>
                            <?php
                            $parentLabels = ['PADRE'=>'Padre','MADRE'=>'Madre','TUTOR'=>'Tutor Legal','ABUELO'=>'Abuelo/a','TIO'=>'Tío/a','HERMANO'=>'Hermano/a','OTRO'=>'Otro'];
                            echo $parentLabels[$a['alu_parentesco']] ?? $a['alu_parentesco'];
                            ?>
                        </p>
                        <?php endif; ?>
                        <?php if (!empty($a['rep_telefono'])): ?>
                        <p class="mb-1"><i class="fas fa-phone mr-2 text-muted"></i>
                            <?= htmlspecialchars($a['rep_telefono']) ?>
                        </p>
                        <?php endif; ?>
                        <?php if (!empty($a['rep_email'])): ?>
                        <p class="mb-1"><i class="fas fa-envelope mr-2 text-muted"></i>
                            <?= htmlspecialchars($a['rep_email']) ?>
                        </p>
                        <?php endif; ?>
                        <?php if (!empty($a['rep_direccion'])): ?>
                        <p class="mb-1"><i class="fas fa-map-marker-alt mr-2 text-muted"></i>
                            <?= htmlspecialchars($a['rep_direccion']) ?>
                        </p>
                        <?php endif; ?>
                        <?php if ($a['rep_consentimiento'] ?? 0): ?>
                        <p class="mb-0"><span class="badge badge-success"><i class="fas fa-check mr-1"></i>Consentimiento de datos otorgado</span></p>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Hermanos inscritos -->
                <?php if (!empty($hermanos)): ?>
                <div class="card card-outline card-success">
                    <div class="card-header py-2">
                        <h3 class="card-title"><i class="fas fa-users mr-2"></i>Hermanos Inscritos</h3>
                    </div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush">
                            <?php foreach ($hermanos as $h): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center py-2">
                                <span>
                                    <i class="fas fa-child mr-1 text-muted"></i>
                                    <?= htmlspecialchars($h['alu_nombres'] . ' ' . $h['alu_apellidos']) ?>
                                </span>
                                <span class="badge badge-<?= $h['alu_estado'] === 'ACTIVO' ? 'success' : 'secondary' ?> badge-pill">
                                    <?= $h['alu_estado'] ?>
                                </span>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                        <div class="card-footer py-1 bg-light text-center">
                            <small class="text-muted"><i class="fas fa-tag mr-1"></i>Puede aplicar beca por hermanos</small>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Contacto de Emergencia -->
                <?php if (!empty($a['alu_contacto_emergencia']) || !empty($a['alu_telefono_emergencia'])): ?>
                <div class="card card-outline card-danger">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-ambulance mr-2 text-danger"></i>Contacto de Emergencia</h3>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($a['alu_contacto_emergencia'])): ?>
                        <p class="mb-1"><strong><?= htmlspecialchars($a['alu_contacto_emergencia']) ?></strong></p>
                        <?php endif; ?>
                        <?php if (!empty($a['alu_telefono_emergencia'])): ?>
                        <p class="mb-0"><i class="fas fa-phone mr-1"></i><?= htmlspecialchars($a['alu_telefono_emergencia']) ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Resumen de Asistencia -->
                <?php if (!empty($asistencia)): ?>
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-calendar-check mr-2"></i>Asistencia</h3>
                    </div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between">
                                <span>Asistencias</span>
                                <span class="badge badge-success badge-pill"><?= $asistencia['presentes'] ?? 0 ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span>Faltas</span>
                                <span class="badge badge-danger badge-pill"><?= $asistencia['ausentes'] ?? 0 ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span>Justificadas</span>
                                <span class="badge badge-warning badge-pill"><?= $asistencia['justificadas'] ?? 0 ?></span>
                            </li>
                            <?php
                            $total = ($asistencia['presentes'] ?? 0) + ($asistencia['ausentes'] ?? 0) + ($asistencia['justificadas'] ?? 0);
                            $pct = $total > 0 ? round(($asistencia['presentes'] / $total) * 100) : 0;
                            ?>
                            <li class="list-group-item">
                                <div class="d-flex justify-content-between mb-1">
                                    <span>% Asistencia</span>
                                    <strong><?= $pct ?>%</strong>
                                </div>
                                <div class="progress" style="height:6px;">
                                    <div class="progress-bar bg-success" style="width:<?= $pct ?>%"></div>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- ========== COLUMNA DERECHA: Ficha deportiva + Inscripciones ========== -->
            <div class="col-lg-8">
                <!-- Ficha Deportiva de Fútbol -->
                <div class="card">
                    <div class="card-header" style="background:<?= $moduloColor ?>20;">
                        <h3 class="card-title"><i class="fas fa-futbol mr-2" style="color:<?= $moduloColor ?>"></i>Ficha Deportiva de Fútbol</h3>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($ficha)): ?>
                        <div class="row text-center mb-3">
                            <div class="col-md-3">
                                <h6 class="text-muted mb-1">Categoría</h6>
                                <?php if (!empty($ficha['categoria_nombre'])): ?>
                                <span class="badge p-2" style="background:<?= htmlspecialchars($ficha['categoria_color'] ?? '#6c757d') ?>;color:white;font-size:.9rem;">
                                    <?= htmlspecialchars($ficha['categoria_nombre']) ?>
                                </span>
                                <?php else: ?>
                                <span class="text-muted">Sin asignar</span>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-3">
                                <h6 class="text-muted mb-1">Posición</h6>
                                <strong><?= htmlspecialchars($ficha['ffa_posicion_preferida'] ?? '—') ?></strong>
                            </div>
                            <div class="col-md-3">
                                <h6 class="text-muted mb-1">Pie Dominante</h6>
                                <strong><?= htmlspecialchars($ficha['ffa_pie_dominante'] ?? '—') ?></strong>
                            </div>
                            <div class="col-md-3">
                                <h6 class="text-muted mb-1">Objetivo</h6>
                                <?php
                                $objBadge = ['RECREATIVO'=>'info','FORMATIVO'=>'primary','COMPETITIVO'=>'success'][$ficha['ffa_objetivo'] ?? ''] ?? 'secondary';
                                ?>
                                <span class="badge badge-<?= $objBadge ?>"><?= $ficha['ffa_objetivo'] ?? '—' ?></span>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-md-3">
                                <small class="text-muted d-block">Club Anterior</small>
                                <?= htmlspecialchars($ficha['ffa_club_anterior'] ?? '—') ?>
                            </div>
                            <div class="col-md-2">
                                <small class="text-muted d-block">Camiseta</small>
                                <?= !empty($ficha['ffa_numero_camiseta']) ? '#' . $ficha['ffa_numero_camiseta'] : '—' ?>
                                <?= !empty($ficha['ffa_talla_camiseta']) ? '(' . $ficha['ffa_talla_camiseta'] . ')' : '' ?>
                            </div>
                            <div class="col-md-2">
                                <small class="text-muted d-block">Aut. Médica</small>
                                <?= ($ficha['ffa_autorizacion_medica'] ?? 0) ? '<span class="badge badge-success">Sí</span>' : '<span class="badge badge-danger">No</span>' ?>
                            </div>
                            <div class="col-md-2">
                                <small class="text-muted d-block">Ingreso</small>
                                <?= !empty($ficha['ffa_fecha_ingreso']) ? date('d/m/Y', strtotime($ficha['ffa_fecha_ingreso'])) : '—' ?>
                            </div>
                            <div class="col-md-3">
                                <small class="text-muted d-block">Estado Ficha</small>
                                <span class="badge badge-<?= ($ficha['ffa_activo'] ?? 0) ? 'success' : 'secondary' ?>">
                                    <?= ($ficha['ffa_activo'] ?? 0) ? 'Activa' : 'Inactiva' ?>
                                </span>
                            </div>
                        </div>
                        <?php if (!empty($ficha['ffa_experiencia_previa'])): ?>
                        <hr>
                        <p class="mb-0"><strong>Experiencia:</strong> <?= htmlspecialchars($ficha['ffa_experiencia_previa']) ?></p>
                        <?php endif; ?>
                        <?php if (!empty($ficha['ffa_notas'])): ?>
                        <hr>
                        <p class="mb-0"><strong>Notas:</strong> <?= nl2br(htmlspecialchars($ficha['ffa_notas'])) ?></p>
                        <?php endif; ?>
                        <?php else: ?>
                        <div class="text-center text-muted py-3">
                            <i class="fas fa-futbol fa-2x mb-2 opacity-50"></i>
                            <p>No se ha creado la ficha deportiva</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Observaciones Médicas -->
                <?php
                $tieneMedico = !empty($a['alu_tipo_sangre']) || !empty($a['alu_alergias']) || !empty($a['alu_condiciones_medicas'])
                             || !empty($a['alu_medicamentos']) || !empty($a['alu_observaciones_medicas']);
                if ($tieneMedico): ?>
                <div class="card card-outline card-danger">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-heartbeat mr-2 text-danger"></i>Información Médica</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <?php if (!empty($a['alu_tipo_sangre'])): ?>
                            <div class="col-md-2"><small class="text-muted d-block">Tipo Sangre</small><strong><?= $a['alu_tipo_sangre'] ?></strong></div>
                            <?php endif; ?>
                            <?php if (!empty($a['alu_alergias'])): ?>
                            <div class="col-md-5"><small class="text-muted d-block">Alergias</small><?= htmlspecialchars($a['alu_alergias']) ?></div>
                            <?php endif; ?>
                            <?php if (!empty($a['alu_condiciones_medicas'])): ?>
                            <div class="col-md-5"><small class="text-muted d-block">Condiciones</small><?= htmlspecialchars($a['alu_condiciones_medicas']) ?></div>
                            <?php endif; ?>
                        </div>
                        <?php if (!empty($a['alu_medicamentos'])): ?>
                        <div class="mt-2"><small class="text-muted d-block">Medicamentos</small><?= htmlspecialchars($a['alu_medicamentos']) ?></div>
                        <?php endif; ?>
                        <?php if (!empty($a['alu_observaciones_medicas'])): ?>
                        <hr>
                        <p class="mb-0"><?= nl2br(htmlspecialchars($a['alu_observaciones_medicas'])) ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Inscripciones -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-clipboard-list mr-2"></i>Inscripciones</h3>
                        <div class="card-tools">
                            <a href="<?= url('futbol', 'inscripcion', 'crear') ?>&alumno_id=<?= $a['alu_alumno_id'] ?? '' ?>" class="btn btn-sm btn-outline-success">
                                <i class="fas fa-plus mr-1"></i>Nueva Inscripción
                            </a>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($inscripciones)): ?>
                        <div class="text-center py-3 text-muted">
                            <i class="fas fa-clipboard fa-2x mb-2 opacity-50"></i>
                            <p>Sin inscripciones registradas</p>
                        </div>
                        <?php else: ?>
                        <table class="table table-sm table-hover mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Grupo</th>
                                    <th>Período</th>
                                    <th>Fecha Inscripción</th>
                                    <th class="text-center">Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($inscripciones as $ins): ?>
                                <tr>
                                    <td>
                                        <?php if (!empty($ins['grupo_color'])): ?>
                                        <span class="badge mr-1" style="background:<?= htmlspecialchars($ins['grupo_color']) ?>">&nbsp;</span>
                                        <?php endif; ?>
                                        <?= htmlspecialchars($ins['grupo_nombre'] ?? $ins['fgr_nombre'] ?? '—') ?>
                                    </td>
                                    <td><?= htmlspecialchars($ins['periodo_nombre'] ?? $ins['fpe_nombre'] ?? '—') ?></td>
                                    <td><?= !empty($ins['fin_fecha_inscripcion']) ? date('d/m/Y', strtotime($ins['fin_fecha_inscripcion'])) : '—' ?></td>
                                    <td class="text-center">
                                        <?php
                                        $estIns = $ins['fin_estado'] ?? '';
                                        $bcIns = ['ACTIVA'=>'success','CANCELADA'=>'danger','SUSPENDIDA'=>'warning','COMPLETADA'=>'info'][$estIns] ?? 'secondary';
                                        ?>
                                        <span class="badge badge-<?= $bcIns ?>"><?= $estIns ?></span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Evaluaciones -->
                <?php if (!empty($evaluaciones)): ?>
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-chart-line mr-2"></i>Últimas Evaluaciones</h3>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Fecha</th>
                                    <th>Tipo</th>
                                    <th class="text-center">Puntuación</th>
                                    <th>Observaciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($evaluaciones as $ev): ?>
                                <tr>
                                    <td><?= !empty($ev['fev_fecha']) ? date('d/m/Y', strtotime($ev['fev_fecha'])) : '—' ?></td>
                                    <td><?= htmlspecialchars($ev['fev_tipo'] ?? $ev['tipo_nombre'] ?? '—') ?></td>
                                    <td class="text-center">
                                        <?php if (isset($ev['fev_calificacion'])): ?>
                                        <span class="badge badge-primary"><?= $ev['fev_calificacion'] ?>/10</span>
                                        <?php else: ?>
                                        —
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($ev['fev_observacion'] ?? '—') ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Notas Generales -->
                <?php if (!empty($a['alu_notas'])): ?>
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-sticky-note mr-2"></i>Notas</h3>
                    </div>
                    <div class="card-body"><?= nl2br(htmlspecialchars($a['alu_notas'])) ?></div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
