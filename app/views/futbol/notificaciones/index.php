<?php
$moduloColor = $modulo_actual['color'] ?? '#22C55E';
$moduloIcono = $modulo_actual['icono'] ?? 'fas fa-futbol';
$moduloNombre = $modulo_actual['nombre'] ?? 'Fútbol';
?>

<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">
                    <i class="<?= $moduloIcono ?>" style="color: <?= $moduloColor ?>"></i>
                    Notificaciones
                </h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= url('futbol', 'dashboard', 'index') ?>">Fútbol</a></li>
                    <li class="breadcrumb-item active">Notificaciones</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- Main Content -->
<section class="content">
    <div class="container-fluid">

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center" style="border-top: 3px solid <?= $moduloColor ?>">
                <h3 class="card-title"><i class="fas fa-bell mr-2" style="color: <?= $moduloColor ?>"></i>Gestión de Notificaciones</h3>
                <button class="btn btn-sm text-white" style="background-color: <?= $moduloColor ?>" onclick="abrirModalNotificacion()">
                    <i class="fas fa-plus mr-1"></i> Nueva Notificación
                </button>
            </div>
            <div class="card-body">
                <?php if (!empty($notificaciones)): ?>
                <div class="table-responsive">
                    <table id="tblNotificaciones" class="table table-bordered table-hover table-striped">
                        <thead style="background-color: <?= $moduloColor ?>; color: #fff;">
                            <tr>
                                <th>#</th>
                                <th>Destinatario</th>
                                <th>Tipo</th>
                                <th>Canal</th>
                                <th>Asunto</th>
                                <th>Fecha</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($notificaciones as $i => $notif): ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td><?= htmlspecialchars($notif['fno_destinatario'] ?? trim(($notif['alu_nombres'] ?? '') . ' ' . ($notif['alu_apellidos'] ?? '')) ?: '—') ?></td>
                                <td>
                                    <?php
                                    $tipoNotifIcon = match($notif['fno_tipo']) {
                                        'PAGO_PENDIENTE' => 'fas fa-dollar-sign',
                                        'MORA'           => 'fas fa-exclamation-triangle',
                                        'ASISTENCIA'     => 'fas fa-clipboard-check',
                                        'GENERAL'        => 'fas fa-info-circle',
                                        'TORNEO'         => 'fas fa-trophy',
                                        'BIENVENIDA'     => 'fas fa-hand-sparkles',
                                        'RECORDATORIO'   => 'fas fa-clock',
                                        default          => 'fas fa-bell'
                                    };
                                    $tipoNotifBadge = match($notif['fno_tipo']) {
                                        'PAGO_PENDIENTE' => 'success',
                                        'MORA'           => 'danger',
                                        'ASISTENCIA'     => 'info',
                                        'GENERAL'        => 'secondary',
                                        'TORNEO'         => 'warning',
                                        'BIENVENIDA'     => 'primary',
                                        'RECORDATORIO'   => 'dark',
                                        default          => 'secondary'
                                    };
                                    ?>
                                    <span class="badge badge-<?= $tipoNotifBadge ?>">
                                        <i class="<?= $tipoNotifIcon ?> mr-1"></i><?= $notif['fno_tipo'] ?>
                                    </span>
                                </td>
                                <td>
                                    <?php
                                    $canalIcon = match($notif['fno_canal']) {
                                        'EMAIL'    => 'fas fa-envelope',
                                        'SMS'      => 'fas fa-sms',
                                        'WHATSAPP' => 'fab fa-whatsapp',
                                        'PUSH'     => 'fas fa-mobile-alt',
                                        'SISTEMA'  => 'fas fa-desktop',
                                        default    => 'fas fa-paper-plane'
                                    };
                                    $canalColor = match($notif['fno_canal']) {
                                        'EMAIL'    => '#dc3545',
                                        'SMS'      => '#6f42c1',
                                        'WHATSAPP' => '#25D366',
                                        'PUSH'     => '#0d6efd',
                                        'SISTEMA'  => '#6c757d',
                                        default    => '#6c757d'
                                    };
                                    ?>
                                    <i class="<?= $canalIcon ?>" style="color: <?= $canalColor ?>"></i>
                                    <?= $notif['fno_canal'] ?>
                                </td>
                                <td><?= htmlspecialchars($notif['fno_asunto'] ?? '') ?></td>
                                <td>
                                    <?= !empty($notif['fno_fecha_envio']) ? date('d/m/Y H:i', strtotime($notif['fno_fecha_envio'])) : '-' ?>
                                </td>
                                <td>
                                    <?php
                                    $estadoNotifBadge = match($notif['fno_estado']) {
                                        'PENDIENTE' => 'warning',
                                        'ENVIADO'   => 'info',
                                        'LEIDO'     => 'success',
                                        'FALLIDO'   => 'danger',
                                        default     => 'secondary'
                                    };
                                    ?>
                                    <span class="badge badge-<?= $estadoNotifBadge ?>"><?= $notif['fno_estado'] ?></span>
                                </td>
                                <td>
                                    <button class="btn btn-xs btn-outline-primary" onclick="verNotificacion(<?= $notif['fno_notificacion_id'] ?>)" title="Ver Detalle">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <?php if ($notif['fno_estado'] === 'FALLIDO' || $notif['fno_estado'] === 'PENDIENTE'): ?>
                                    <button class="btn btn-xs btn-outline-info" onclick="reenviarNotificacion(<?= $notif['fno_notificacion_id'] ?>)" title="Reenviar">
                                        <i class="fas fa-redo"></i>
                                    </button>
                                    <?php endif; ?>
                                    <button class="btn btn-xs btn-outline-danger" onclick="eliminarNotificacion(<?= $notif['fno_notificacion_id'] ?>)" title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-bell-slash fa-3x opacity-50 text-muted mb-3"></i>
                    <p class="text-muted">No hay notificaciones registradas.</p>
                    <button class="btn btn-sm text-white" style="background-color: <?= $moduloColor ?>" onclick="abrirModalNotificacion()">
                        <i class="fas fa-plus mr-1"></i> Crear primera notificación
                    </button>
                </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</section>

<!-- ============================================================= -->
<!-- MODAL: NUEVA NOTIFICACIÓN -->
<!-- ============================================================= -->
<div class="modal fade" id="modalNotificacion" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header text-white" style="background-color: <?= $moduloColor ?>">
                <h5 class="modal-title"><i class="fas fa-bell mr-2"></i>Nueva Notificación</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form id="formNotificacion" method="POST" action="<?= url('futbol', 'notificacion', 'crear') ?>">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tipo <span class="text-danger">*</span></label>
                                <select name="tipo" id="notificacion_tipo" class="form-control" required>
                                    <option value="">Seleccione...</option>
                                    <option value="PAGO_PENDIENTE">Pago Pendiente</option>
                                    <option value="MORA">Mora</option>
                                    <option value="ASISTENCIA">Asistencia</option>
                                    <option value="GENERAL">General</option>
                                    <option value="TORNEO">Torneo</option>
                                    <option value="BIENVENIDA">Bienvenida</option>
                                    <option value="RECORDATORIO">Recordatorio</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Canal <span class="text-danger">*</span></label>
                                <select name="canal" id="notificacion_canal" class="form-control" required>
                                    <option value="">Seleccione...</option>
                                    <option value="EMAIL">Email</option>
                                    <option value="SMS">SMS</option>
                                    <option value="WHATSAPP">WhatsApp</option>
                                    <option value="PUSH">Push</option>
                                    <option value="SISTEMA">Sistema</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Alumno</label>
                                <select name="alumno_id" id="notificacion_alumno" class="form-control">
                                    <option value="">— Todos / Sin asignar —</option>
                                    <?php foreach ($alumnos ?? [] as $al): ?>
                                    <option value="<?= $al['alu_alumno_id'] ?>"><?= htmlspecialchars($al['alu_apellidos'] . ', ' . $al['alu_nombres']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="text-muted">Seleccione un alumno o deje vacío para notificación general.</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Destinatario</label>
                                <input type="text" name="destinatario" id="notificacion_destinatario" class="form-control" placeholder="Email, teléfono (opcional)">
                                <small class="text-muted">Email, teléfono u otro dato de contacto.</small>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Asunto</label>
                        <input type="text" name="asunto" id="notificacion_asunto" class="form-control" placeholder="Asunto de la notificación">
                    </div>
                    <div class="form-group">
                        <label>Mensaje <span class="text-danger">*</span></label>
                        <textarea name="mensaje" id="notificacion_mensaje" class="form-control" rows="5" required placeholder="Escriba el contenido del mensaje..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn text-white" style="background-color: <?= $moduloColor ?>">
                        <i class="fas fa-paper-plane mr-1"></i> Enviar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ============================================================= -->
<!-- SCRIPTS -->
<!-- ============================================================= -->
<?php ob_start(); ?>
<script>
$(document).ready(function() {
    $('#tblNotificaciones').DataTable({
        language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' },
        responsive: true,
        order: [[5, 'desc']]
    });
});

function abrirModalNotificacion() {
    $('#formNotificacion')[0].reset();
    $('#formNotificacion').attr('action', '<?= url('futbol', 'notificacion', 'crear') ?>');
    $('#modalNotificacion').modal('show');
}

function verNotificacion(id) {
    // Mostrar detalle en modal (la acción 'ver' no existe, mostramos un SweetAlert con info)
    Swal.fire({
        title: 'Detalle de Notificación #' + id,
        text: 'Funcionalidad de detalle pendiente de implementar.',
        icon: 'info'
    });
}

function reenviarNotificacion(id) {
    Swal.fire({
        title: '¿Reenviar notificación?',
        text: 'Se intentará enviar nuevamente esta notificación.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '<?= $moduloColor ?>',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="fas fa-redo mr-1"></i> Reenviar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post('<?= url('futbol', 'notificacion', 'reenviar') ?>', {
                id: id,
                csrf_token: '<?= $csrf_token ?>'
            }, function(res) {
                if (res.success) {
                    Swal.fire({ icon: 'success', title: '¡Reenviada!', text: res.message, timer: 2000, showConfirmButton: false }).then(() => location.reload());
                } else {
                    Swal.fire('Error', res.message || 'No se pudo reenviar.', 'error');
                }
            }, 'json').fail(function() {
                Swal.fire('Error', 'Error de conexión.', 'error');
            });
        }
    });
}

function eliminarNotificacion(id) {
    Swal.fire({
        title: '¿Eliminar notificación?',
        text: 'Esta acción no se puede deshacer.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = '<?= url('futbol', 'notificacion', 'eliminar') ?>&id=' + id;
        }
    });
}
</script>
<?php $scripts = ob_get_clean(); ?>
