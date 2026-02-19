<?php
/**
 * Vista: Administración de IPs Bloqueadas
 * Muestra IPs bloqueadas, intentos fallidos y configuración actual
 */
?>

<section class="content pt-3">
    <div class="container-fluid">

<!-- Header Premium -->
<?php
$headerTitle    = 'IPs Bloqueadas';
$headerSubtitle = 'Gestión de bloqueos por fuerza bruta';
$headerIcon     = 'fas fa-ban';
$headerButtons  = [];
include __DIR__ . '/../partials/header.php';
?>

<!-- Tarjetas de Configuración Actual -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="small-box bg-info">
            <div class="inner">
                <h3><?= $config['max_login_attempts'] ?></h3>
                <p>Intentos máximos permitidos</p>
            </div>
            <div class="icon"><i class="fas fa-key"></i></div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3><?= floor($config['brute_force_window'] / 60) ?> min</h3>
                <p>Ventana de detección</p>
            </div>
            <div class="icon"><i class="fas fa-clock"></i></div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="small-box bg-danger">
            <div class="inner">
                <h3><?= floor($config['ip_block_duration'] / 60) ?> min</h3>
                <p>Duración del bloqueo</p>
            </div>
            <div class="icon"><i class="fas fa-lock"></i></div>
        </div>
    </div>
</div>

<!-- IPs Bloqueadas Activas -->
<div class="card card-danger card-outline mb-4">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-ban mr-2"></i>IPs Bloqueadas Actualmente</h3>
        <div class="card-tools">
            <span class="badge badge-danger"><?= count($blockedIPs) ?></span>
        </div>
    </div>
    <div class="card-body p-0">
        <?php if (empty($blockedIPs)): ?>
            <div class="text-center py-4 text-muted">
                <i class="fas fa-check-circle fa-3x mb-2 text-success"></i>
                <p class="mb-0">No hay IPs bloqueadas en este momento</p>
            </div>
        <?php else: ?>
            <table class="table table-bordered table-hover mb-0">
                <thead class="thead-light">
                    <tr>
                        <th><i class="fas fa-globe mr-1"></i>IP</th>
                        <th><i class="fas fa-calendar mr-1"></i>Expira</th>
                        <th><i class="fas fa-hourglass-half mr-1"></i>Tiempo Restante</th>
                        <th class="text-center" style="width: 120px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($blockedIPs as $entry): ?>
                    <tr id="blocked-<?= md5($entry['ip']) ?>">
                        <td><code class="text-danger"><?= htmlspecialchars($entry['ip']) ?></code></td>
                        <td><?= date('d/m/Y H:i:s', strtotime($entry['expira'])) ?></td>
                        <td>
                            <span class="badge badge-warning"><?= htmlspecialchars($entry['restante']) ?></span>
                        </td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-success btn-desbloquear"
                                    data-ip="<?= htmlspecialchars($entry['ip']) ?>"
                                    title="Desbloquear IP">
                                <i class="fas fa-unlock"></i> Desbloquear
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<!-- Intentos Fallidos Recientes -->
<div class="card card-warning card-outline">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-exclamation-triangle mr-2"></i>Intentos Fallidos por IP</h3>
        <div class="card-tools">
            <span class="badge badge-warning"><?= count($failedAttempts) ?> IPs</span>
        </div>
    </div>
    <div class="card-body p-0">
        <?php if (empty($failedAttempts)): ?>
            <div class="text-center py-4 text-muted">
                <i class="fas fa-shield-alt fa-3x mb-2 text-success"></i>
                <p class="mb-0">No hay intentos fallidos registrados</p>
            </div>
        <?php else: ?>
            <table class="table table-bordered table-hover mb-0">
                <thead class="thead-light">
                    <tr>
                        <th><i class="fas fa-globe mr-1"></i>IP</th>
                        <th class="text-center"><i class="fas fa-bolt mr-1"></i>Recientes (<?= floor($config['brute_force_window'] / 60) ?>min)</th>
                        <th class="text-center"><i class="fas fa-list mr-1"></i>Total</th>
                        <th><i class="fas fa-clock mr-1"></i>Último Intento</th>
                        <th><i class="fas fa-hourglass mr-1"></i>Hace</th>
                        <th class="text-center" style="width: 120px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($failedAttempts as $entry): ?>
                    <?php
                        $pct = min(100, ($entry['intentos_recientes'] / $config['max_login_attempts']) * 100);
                        $barClass = $pct >= 100 ? 'bg-danger' : ($pct >= 60 ? 'bg-warning' : 'bg-info');
                    ?>
                    <tr id="attempts-<?= md5($entry['ip']) ?>">
                        <td><code><?= htmlspecialchars($entry['ip']) ?></code></td>
                        <td class="text-center">
                            <div class="progress progress-sm mb-1" style="height: 8px;">
                                <div class="progress-bar <?= $barClass ?>" style="width: <?= $pct ?>%"></div>
                            </div>
                            <span class="badge <?= $pct >= 100 ? 'badge-danger' : 'badge-secondary' ?>">
                                <?= $entry['intentos_recientes'] ?> / <?= $config['max_login_attempts'] ?>
                            </span>
                        </td>
                        <td class="text-center"><?= $entry['intentos_total'] ?></td>
                        <td><?= date('d/m/Y H:i:s', strtotime($entry['ultimo_intento'])) ?></td>
                        <td><span class="text-muted"><?= htmlspecialchars($entry['hace']) ?></span></td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-outline-danger btn-limpiar"
                                    data-ip="<?= htmlspecialchars($entry['ip']) ?>"
                                    title="Limpiar intentos">
                                <i class="fas fa-eraser"></i> Limpiar
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<!-- Info de configuración -->
<div class="callout callout-info mt-3">
    <h5><i class="fas fa-info-circle"></i> Política de bloqueo</h5>
    <p class="mb-0">
        Si una IP tiene más de <strong><?= $config['max_login_attempts'] ?></strong> intentos fallidos en
        <strong><?= floor($config['brute_force_window'] / 60) ?> minutos</strong>, se bloquea automáticamente por
        <strong><?= floor($config['ip_block_duration'] / 60) ?> minutos</strong>.
        Estos valores se configuran en <code>Config::SECURITY</code> (<em>config/app.php</em>).
    </p>
</div>

    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Desbloquear IP
    document.querySelectorAll('.btn-desbloquear').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var ip = this.dataset.ip;
            var row = this.closest('tr');
            Swal.fire({
                title: '¿Desbloquear IP?',
                html: 'Se desbloqueará la IP <code>' + ip + '</code> y se limpiarán sus intentos fallidos.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fas fa-unlock"></i> Sí, desbloquear',
                cancelButtonText: 'Cancelar'
            }).then(function(result) {
                if (result.isConfirmed) {
                    var form = new FormData();
                    form.append('ip', ip);
                    fetch('<?= url("seguridad", "auditoria", "desbloquearIp") ?>', {
                        method: 'POST',
                        body: form
                    })
                    .then(function(r) { return r.json(); })
                    .then(function(data) {
                        if (data.success) {
                            Swal.fire('Desbloqueada', data.message, 'success').then(function() {
                                location.reload();
                            });
                        } else {
                            Swal.fire('Error', data.message, 'error');
                        }
                    })
                    .catch(function() { Swal.fire('Error', 'No se pudo procesar la solicitud', 'error'); });
                }
            });
        });
    });

    // Limpiar intentos
    document.querySelectorAll('.btn-limpiar').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var ip = this.dataset.ip;
            var row = this.closest('tr');
            Swal.fire({
                title: '¿Limpiar intentos?',
                html: 'Se eliminarán los intentos fallidos de <code>' + ip + '</code>.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fas fa-eraser"></i> Sí, limpiar',
                cancelButtonText: 'Cancelar'
            }).then(function(result) {
                if (result.isConfirmed) {
                    var form = new FormData();
                    form.append('ip', ip);
                    fetch('<?= url("seguridad", "auditoria", "limpiarIntentos") ?>', {
                        method: 'POST',
                        body: form
                    })
                    .then(function(r) { return r.json(); })
                    .then(function(data) {
                        if (data.success) {
                            Swal.fire('Limpiado', data.message, 'success').then(function() {
                                location.reload();
                            });
                        } else {
                            Swal.fire('Error', data.message, 'error');
                        }
                    })
                    .catch(function() { Swal.fire('Error', 'No se pudo procesar la solicitud', 'error'); });
                }
            });
        });
    });
});
</script>
