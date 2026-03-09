<?php
/**
 * Vista de Asistencia - Módulo Fútbol
 * UX one-click: cada botón guarda inmediatamente vía AJAX individual
 */
$moduloColor  = '#22C55E';
$moduloIcon   = 'fas fa-futbol';
$fecha_actual = $fecha    ?? date('Y-m-d');
$alumnos      = $alumnos  ?? [];
$grupoId      = $grupoId  ?? 0;
$csrfToken    = $csrf_token ?? '';

// Nombre del grupo seleccionado para el encabezado
$nombreGrupoActual = '';
foreach ($grupos ?? [] as $g) {
    if ($g['fgr_grupo_id'] == $grupoId) {
        $nombreGrupoActual = $g['fgr_nombre'];
        break;
    }
}
?>

<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">
                    <i class="<?= $moduloIcon ?>" style="color: <?= $moduloColor ?>"></i>
                    Control de Asistencia
                </h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= url('futbol', 'dashboard', 'index') ?>">Fútbol</a></li>
                    <li class="breadcrumb-item active">Asistencia</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">

        <!-- Filtros -->
        <div class="card">
            <div class="card-header py-2" style="border-top: 3px solid <?= $moduloColor ?>">
                <h3 class="card-title"><i class="fas fa-filter mr-1"></i> Filtros</h3>
            </div>
            <div class="card-body py-2">
                <div class="row align-items-end">
                    <div class="col-md-3">
                        <div class="form-group mb-0">
                            <label class="mb-1"><i class="fas fa-calendar-alt mr-1"></i> Fecha</label>
                            <input type="date" class="form-control" id="fechaAsistencia"
                                   value="<?= htmlspecialchars($fecha_actual) ?>">
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="form-group mb-0">
                            <label class="mb-1"><i class="fas fa-users mr-1"></i> Grupo</label>
                            <select class="form-control" id="grupoAsistencia">
                                <option value="">Seleccionar grupo...</option>
                                <?php foreach ($grupos ?? [] as $g): ?>
                                    <option value="<?= $g['fgr_grupo_id'] ?>"
                                            <?= $grupoId == $g['fgr_grupo_id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($g['fgr_nombre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-success btn-block" id="btnCargar">
                            <i class="fas fa-search mr-1"></i> Cargar
                        </button>
                    </div>
                    <div class="col-md-2">
                        <a href="<?= url('futbol', 'asistencia', 'reporte') ?>"
                           class="btn btn-outline-secondary btn-block">
                            <i class="fas fa-chart-bar mr-1"></i> Reporte
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <?php if (!empty($alumnos)): ?>

        <!-- Barra de contadores + acciones rápidas -->
        <div class="d-flex flex-wrap align-items-center justify-content-between mb-2" style="gap:8px;">
            <div class="d-flex flex-wrap" style="gap:6px;" id="contadores">
                <span class="badge badge-pill badge-success px-3 py-2">
                    <i class="fas fa-check mr-1"></i>Presentes&nbsp;<strong id="cntPresente">0</strong>
                </span>
                <span class="badge badge-pill badge-warning px-3 py-2" style="color:#212529;">
                    <i class="fas fa-clock mr-1"></i>Tardanzas&nbsp;<strong id="cntTardanza">0</strong>
                </span>
                <span class="badge badge-pill badge-danger px-3 py-2">
                    <i class="fas fa-times mr-1"></i>Ausentes&nbsp;<strong id="cntAusente">0</strong>
                </span>
                <span class="badge badge-pill badge-info px-3 py-2">
                    <i class="fas fa-file-alt mr-1"></i>Justificados&nbsp;<strong id="cntJustificado">0</strong>
                </span>
                <span class="badge badge-pill badge-secondary px-3 py-2">
                    <i class="fas fa-question mr-1"></i>Sin marcar&nbsp;<strong id="cntPendiente">0</strong>
                </span>
            </div>
            <div>
                <button type="button" class="btn btn-success btn-sm js-marcar-todos" data-estado="PRESENTE">
                    <i class="fas fa-check-double mr-1"></i>Todos Presente
                </button>
                <button type="button" class="btn btn-outline-secondary btn-sm ml-1" id="btnLimpiar">
                    <i class="fas fa-eraser mr-1"></i>Limpiar
                </button>
            </div>
        </div>

        <!-- Tabla de alumnos con botones one-click -->
        <div class="card">
            <div class="card-header py-2" style="border-top: 3px solid <?= $moduloColor ?>">
                <h3 class="card-title">
                    <i class="fas fa-clipboard-check mr-1"></i>
                    <?= htmlspecialchars($nombreGrupoActual) ?>
                    <small class="text-muted ml-2"><?= date('d/m/Y', strtotime($fecha_actual)) ?></small>
                </h3>
                <div class="card-tools">
                    <span class="badge badge-secondary"><?= count($alumnos) ?> alumnos</span>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0" id="tablaAsistencia">
                        <thead class="thead-light">
                            <tr>
                                <th width="36" class="text-center text-muted">#</th>
                                <th>Alumno</th>
                                <th>Asistencia</th>
                                <th width="28"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($alumnos as $i => $asis):
                                $estadoActual = $asis['estado_asistencia'] ?? '';
                                $claseBtn = [
                                    'PRESENTE'   => 'success',
                                    'TARDANZA'   => 'warning',
                                    'AUSENTE'    => 'danger',
                                    'JUSTIFICADO'=> 'info',
                                ];
                            ?>
                            <tr class="fila-alumno"
                                data-inscripcion="<?= (int)$asis['fin_inscripcion_id'] ?>"
                                data-alumno="<?= (int)$asis['alu_alumno_id'] ?>"
                                data-estado="<?= htmlspecialchars($estadoActual) ?>">

                                <td class="text-center text-muted align-middle"><?= $i + 1 ?></td>

                                <td class="align-middle fw-500">
                                    <?= htmlspecialchars($asis['nombre']) ?>
                                </td>

                                <td class="align-middle py-1">
                                    <div class="btn-group btn-group-sm w-100" role="group">
                                        <?php foreach ($claseBtn as $estado => $color):
                                            $esActivo = $estadoActual === $estado;
                                            $clase    = $esActivo ? "btn-{$color}" : "btn-outline-{$color}";
                                            $labels   = ['PRESENTE'=>'Presente','TARDANZA'=>'Tardanza','AUSENTE'=>'Ausente','JUSTIFICADO'=>'Justificado'];
                                            $icons    = ['PRESENTE'=>'fa-check','TARDANZA'=>'fa-clock','AUSENTE'=>'fa-times','JUSTIFICADO'=>'fa-file-alt'];
                                        ?>
                                        <button type="button"
                                                class="btn btn-asist <?= $clase ?>"
                                                data-estado="<?= $estado ?>">
                                            <i class="fas <?= $icons[$estado] ?> mr-1"></i><?= $labels[$estado] ?>
                                        </button>
                                        <?php endforeach; ?>
                                    </div>
                                </td>

                                <td class="text-center align-middle">
                                    <span class="ind-ok text-success <?= $estadoActual ? '' : 'd-none' ?>">
                                        <i class="fas fa-check-circle"></i>
                                    </span>
                                    <span class="ind-spin d-none">
                                        <i class="fas fa-circle-notch fa-spin text-muted"></i>
                                    </span>
                                    <span class="ind-err d-none text-danger" title="Error — haga clic de nuevo">
                                        <i class="fas fa-exclamation-circle"></i>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <?php else: ?>
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="fas fa-clipboard-check fa-3x text-muted mb-3 d-block opacity-50"></i>
                <p class="text-muted mb-0">Seleccione fecha y grupo para cargar la asistencia.</p>
            </div>
        </div>
        <?php endif; ?>

    </div>
</section>

<?php ob_start(); ?>
<style nonce="<?= cspNonce() ?>">
/* Botones de asistencia */
.btn-asist {
    flex: 1;
    font-size: 0.77rem;
    padding: 4px 5px;
    white-space: nowrap;
    transition: opacity 0.12s ease, transform 0.08s ease;
}
/* Botones no seleccionados: reducir prominencia */
.btn-asist.btn-outline-success,
.btn-asist.btn-outline-warning,
.btn-asist.btn-outline-danger,
.btn-asist.btn-outline-info { opacity: 0.38; }
.btn-asist.btn-outline-success:hover,
.btn-asist.btn-outline-warning:hover,
.btn-asist.btn-outline-danger:hover,
.btn-asist.btn-outline-info:hover   { opacity: 0.82; }
/* Botón activo: micro-bounce */
.btn-asist.btn-success,
.btn-asist.btn-warning,
.btn-asist.btn-danger,
.btn-asist.btn-info { opacity: 1; }
/* Filas */
.fila-alumno:hover { background: rgba(34,197,94,.04) !important; }
.fw-500 { font-weight: 500; }
/* Contadores */
#contadores .badge { font-size: 0.82rem; cursor: default; }
</style>

<script nonce="<?= cspNonce() ?>">
(function () {
    'use strict';

    var URL_MARCAR = '<?= url("futbol", "asistencia", "marcarUno") ?>';
    var URL_CARGAR = '<?= url("futbol", "asistencia", "index") ?>';
    var CSRF       = '<?= addslashes($csrfToken) ?>';
    var GRUPO_ID   = <?= (int)$grupoId ?>;
    var FECHA      = '<?= htmlspecialchars($fecha_actual, ENT_QUOTES) ?>';

    var COLOR_MAP = { PRESENTE: 'success', TARDANZA: 'warning', AUSENTE: 'danger', JUSTIFICADO: 'info' };

    // ── Toast micro-feedback ─────────────────────────────────────
    function toast(mensaje, tipo) {
        if (typeof Swal === 'undefined') return;
        Swal.fire({
            toast: true, position: 'top-end', icon: tipo || 'info',
            title: mensaje, showConfirmButton: false,
            timer: 2000, timerProgressBar: true
        });
    }

    // ── Contadores ───────────────────────────────────────────────
    function actualizarContadores() {
        var cnt = { PRESENTE: 0, TARDANZA: 0, AUSENTE: 0, JUSTIFICADO: 0, PENDIENTE: 0 };
        document.querySelectorAll('.fila-alumno').forEach(function (fila) {
            var e = fila.dataset.estado || '';
            cnt.hasOwnProperty(e) ? cnt[e]++ : cnt.PENDIENTE++;
        });
        document.getElementById('cntPresente').textContent    = cnt.PRESENTE;
        document.getElementById('cntTardanza').textContent    = cnt.TARDANZA;
        document.getElementById('cntAusente').textContent     = cnt.AUSENTE;
        document.getElementById('cntJustificado').textContent = cnt.JUSTIFICADO;
        document.getElementById('cntPendiente').textContent   = cnt.PENDIENTE;
    }

    // ── Actualizar botones de una fila ───────────────────────────
    function aplicarEstadoVisual(fila, estado) {
        fila.querySelectorAll('.btn-asist').forEach(function (btn) {
            var e = btn.dataset.estado;
            var c = COLOR_MAP[e] || 'secondary';
            btn.className = 'btn btn-sm btn-asist ' + (e === estado ? 'btn-' + c : 'btn-outline-' + c);
        });
        fila.dataset.estado = estado || '';
    }

    // ── Guardar un alumno vía AJAX ───────────────────────────────
    function guardarUno(fila, estado) {
        var okEl   = fila.querySelector('.ind-ok');
        var spinEl = fila.querySelector('.ind-spin');
        var errEl  = fila.querySelector('.ind-err');

        // Optimista: actualizar UI de inmediato
        aplicarEstadoVisual(fila, estado);
        actualizarContadores();
        okEl.classList.add('d-none');
        errEl.classList.add('d-none');
        spinEl.classList.remove('d-none');

        var fd = new FormData();
        fd.append('csrf_token',    CSRF);
        fd.append('inscripcion_id', fila.dataset.inscripcion);
        fd.append('alumno_id',      fila.dataset.alumno);
        fd.append('grupo_id',       GRUPO_ID);
        fd.append('fecha',          FECHA);
        fd.append('estado',         estado);

        fetch(URL_MARCAR, { method: 'POST', body: fd })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                spinEl.classList.add('d-none');
                if (res.success) {
                    okEl.classList.remove('d-none');
                } else {
                    // Revertir estado visual
                    aplicarEstadoVisual(fila, '');
                    actualizarContadores();
                    errEl.classList.remove('d-none');
                    toast(res.message || 'Error al guardar', 'error');
                }
            })
            .catch(function () {
                spinEl.classList.add('d-none');
                aplicarEstadoVisual(fila, '');
                actualizarContadores();
                errEl.classList.remove('d-none');
                toast('Error de conexión', 'error');
            });
    }

    // ── Clic en botón de asistencia ──────────────────────────────
    document.addEventListener('click', function (e) {
        var btn = e.target.closest('.btn-asist');
        if (!btn) return;
        var fila   = btn.closest('.fila-alumno');
        var estado = btn.dataset.estado;
        if (!fila || !estado || !GRUPO_ID) return;
        guardarUno(fila, estado);
    });

    // ── Marcar todos ─────────────────────────────────────────────
    document.addEventListener('click', function (e) {
        var btn = e.target.closest('.js-marcar-todos');
        if (!btn) return;
        var estado = btn.dataset.estado;
        document.querySelectorAll('.fila-alumno').forEach(function (fila) {
            // Solo guardar los que no tienen ya ese estado (evitar requests innecesarios)
            if (fila.dataset.estado !== estado) guardarUno(fila, estado);
        });
    });

    // ── Limpiar selección visual (sin guardar en BD) ─────────────
    var btnLimpiar = document.getElementById('btnLimpiar');
    if (btnLimpiar) {
        btnLimpiar.addEventListener('click', function () {
            document.querySelectorAll('.fila-alumno').forEach(function (fila) {
                aplicarEstadoVisual(fila, '');
                fila.querySelector('.ind-ok').classList.add('d-none');
                fila.querySelector('.ind-err').classList.add('d-none');
            });
            actualizarContadores();
        });
    }

    // ── Botón Cargar ─────────────────────────────────────────────
    document.getElementById('btnCargar').addEventListener('click', function () {
        var fecha   = document.getElementById('fechaAsistencia').value;
        var grupoId = document.getElementById('grupoAsistencia').value;
        if (!fecha || !grupoId) {
            if (typeof Swal !== 'undefined') Swal.fire('Atención', 'Seleccione fecha y grupo.', 'warning');
            return;
        }
        var form = document.createElement('form');
        form.method = 'POST';
        form.action = URL_CARGAR;
        [{ n: 'fecha', v: fecha }, { n: 'grupo', v: grupoId }].forEach(function (p) {
            var inp = document.createElement('input');
            inp.type = 'hidden'; inp.name = p.n; inp.value = p.v;
            form.appendChild(inp);
        });
        document.body.appendChild(form);
        form.submit();
    });

    // ── Inicializar contadores al cargar ─────────────────────────
    actualizarContadores();

}());
</script>
<?php $scripts = ob_get_clean(); ?>
