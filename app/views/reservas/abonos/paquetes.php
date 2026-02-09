<?php
/**
 * DigiSports Arena — Vista: Paquetes de Horas
 * CRUD de paquetes con descuentos para compra de horas prepagadas
 */

$paquetes  = $paquetes ?? [];
$csrfToken = $csrf_token ?? '';
?>

<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">
                    <i class="fas fa-gift mr-2 text-primary"></i>
                    Paquetes de Horas
                </h1>
            </div>
            <div class="col-sm-6">
                <div class="float-sm-right">
                    <button class="btn btn-primary" data-toggle="modal" data-target="#modalPaquete" onclick="limpiarFormPaquete()">
                        <i class="fas fa-plus-circle mr-1"></i> Nuevo Paquete
                    </button>
                    <a href="<?= url('reservas', 'abon', 'index') ?>" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left mr-1"></i> Monederos
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">

        <?php if (!empty($paquetes)): ?>
        <div class="row">
            <?php foreach ($paquetes as $p): ?>
            <?php $inactivo = ($p['paq_estado'] ?? '') !== 'ACTIVO'; ?>
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card h-100 <?= $inactivo ? 'bg-light' : '' ?>" 
                     style="border-top: 4px solid <?= $inactivo ? '#ccc' : '#3B82F6' ?>; border-radius: 12px;">
                    <div class="card-body text-center">
                        <?php if ($p['paq_descuento_pct'] > 0): ?>
                        <span class="badge badge-danger mb-2" style="font-size: 1rem; position: absolute; top: 10px; right: 10px;">
                            -<?= number_format($p['paq_descuento_pct'], 0) ?>%
                        </span>
                        <?php endif; ?>

                        <div class="mb-3">
                            <i class="fas fa-gift fa-3x" style="color: <?= $inactivo ? '#ccc' : '#3B82F6' ?>"></i>
                        </div>

                        <h4 class="font-weight-bold"><?= htmlspecialchars($p['paq_nombre']) ?></h4>
                        
                        <?php if (!empty($p['paq_descripcion'])): ?>
                        <p class="text-muted small mb-3"><?= htmlspecialchars($p['paq_descripcion']) ?></p>
                        <?php endif; ?>

                        <div class="mb-3">
                            <span class="h2 text-success font-weight-bold">
                                $<?= number_format($p['paq_precio_paquete'], 2) ?>
                            </span>
                            <?php if ($p['paq_precio_normal'] > $p['paq_precio_paquete']): ?>
                            <br>
                            <del class="text-muted">$<?= number_format($p['paq_precio_normal'], 2) ?></del>
                            <?php endif; ?>
                        </div>

                        <div class="d-flex justify-content-around mb-3">
                            <div>
                                <strong><?= $p['paq_horas_incluidas'] ?></strong>
                                <br><small class="text-muted">Horas</small>
                            </div>
                            <div>
                                <strong><?= $p['paq_dias_vigencia'] ?></strong>
                                <br><small class="text-muted">Días</small>
                            </div>
                            <div>
                                <strong>$<?= number_format($p['paq_precio_paquete'] / max($p['paq_horas_incluidas'], 1), 2) ?></strong>
                                <br><small class="text-muted">$/hora</small>
                            </div>
                        </div>

                        <span class="badge <?= $inactivo ? 'badge-secondary' : 'badge-success' ?> px-3 py-1">
                            <?= $p['paq_estado'] ?>
                        </span>

                        <div class="mt-3">
                            <button class="btn btn-sm btn-outline-primary btn-editar-paquete" 
                                    data-paquete='<?= json_encode($p) ?>'>
                                <i class="fas fa-edit mr-1"></i> Editar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="fas fa-gift fa-4x mb-3 text-muted" style="opacity: .2"></i>
                <p class="text-muted mb-3">No hay paquetes configurados</p>
                <button class="btn btn-primary" data-toggle="modal" data-target="#modalPaquete" onclick="limpiarFormPaquete()">
                    <i class="fas fa-plus-circle mr-1"></i> Crear Primer Paquete
                </button>
            </div>
        </div>
        <?php endif; ?>

    </div>
</section>

<!-- Modal Paquete -->
<div class="modal fade" id="modalPaquete" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="formPaquete" method="POST">
                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                <input type="hidden" name="paquete_id" id="paq_id" value="0">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalPaqueteTitle">
                        <i class="fas fa-gift mr-2"></i>Nuevo Paquete
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label>Nombre <span class="text-danger">*</span></label>
                                <input type="text" name="nombre" id="paq_nombre" class="form-control" 
                                       placeholder="Ej: Paquete Bronce - 10 horas" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Estado</label>
                                <select name="estado" id="paq_estado" class="form-control">
                                    <option value="ACTIVO">Activo</option>
                                    <option value="INACTIVO">Inactivo</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Descripción</label>
                        <textarea name="descripcion" id="paq_descripcion" class="form-control" rows="2" 
                                  placeholder="Descripción del paquete..."></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Horas Incluidas <span class="text-danger">*</span></label>
                                <input type="number" name="horas_incluidas" id="paq_horas" class="form-control" 
                                       min="1" value="10" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Precio Normal ($)</label>
                                <input type="number" name="precio_normal" id="paq_precio_normal" class="form-control" 
                                       min="0" step="0.01" value="0">
                                <small class="text-muted">Precio sin descuento</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Precio Paquete ($) <span class="text-danger">*</span></label>
                                <input type="number" name="precio_paquete" id="paq_precio_paquete" class="form-control" 
                                       min="1" step="0.01" value="0" required>
                                <small class="text-muted">Precio con descuento</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Vigencia (días)</label>
                                <input type="number" name="dias_vigencia" id="paq_vigencia" class="form-control" 
                                       min="7" value="90">
                            </div>
                        </div>
                    </div>
                    <div id="descuentoPreview" class="alert alert-info" style="display:none;">
                        <i class="fas fa-percent mr-1"></i>
                        Descuento: <strong id="descuentoPct">0</strong>% — 
                        Ahorro: <strong id="descuentoAhorro">$0</strong>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i> Guardar Paquete
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function limpiarFormPaquete() {
    document.getElementById('paq_id').value = '0';
    document.getElementById('paq_nombre').value = '';
    document.getElementById('paq_descripcion').value = '';
    document.getElementById('paq_horas').value = '10';
    document.getElementById('paq_precio_normal').value = '0';
    document.getElementById('paq_precio_paquete').value = '0';
    document.getElementById('paq_vigencia').value = '90';
    document.getElementById('paq_estado').value = 'ACTIVO';
    document.getElementById('modalPaqueteTitle').innerHTML = '<i class="fas fa-gift mr-2"></i>Nuevo Paquete';
    document.getElementById('descuentoPreview').style.display = 'none';
}

document.addEventListener('DOMContentLoaded', function() {
    // Editar paquete
    document.querySelectorAll('.btn-editar-paquete').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var p = JSON.parse(this.dataset.paquete);
            document.getElementById('paq_id').value = p.paq_paquete_id;
            document.getElementById('paq_nombre').value = p.paq_nombre;
            document.getElementById('paq_descripcion').value = p.paq_descripcion || '';
            document.getElementById('paq_horas').value = p.paq_horas_incluidas;
            document.getElementById('paq_precio_normal').value = p.paq_precio_normal;
            document.getElementById('paq_precio_paquete').value = p.paq_precio_paquete;
            document.getElementById('paq_vigencia').value = p.paq_dias_vigencia;
            document.getElementById('paq_estado').value = p.paq_estado;
            document.getElementById('modalPaqueteTitle').innerHTML = '<i class="fas fa-edit mr-2"></i>Editar Paquete';
            calcDescuento();
            $('#modalPaquete').modal('show');
        });
    });

    // Calcular descuento en tiempo real
    function calcDescuento() {
        var normal = parseFloat(document.getElementById('paq_precio_normal').value) || 0;
        var paquete = parseFloat(document.getElementById('paq_precio_paquete').value) || 0;
        var preview = document.getElementById('descuentoPreview');
        if (normal > 0 && paquete > 0 && paquete < normal) {
            var pct = ((1 - paquete / normal) * 100).toFixed(1);
            var ahorro = (normal - paquete).toFixed(2);
            document.getElementById('descuentoPct').textContent = pct;
            document.getElementById('descuentoAhorro').textContent = '$' + ahorro;
            preview.style.display = 'block';
        } else {
            preview.style.display = 'none';
        }
    }
    document.getElementById('paq_precio_normal').addEventListener('input', calcDescuento);
    document.getElementById('paq_precio_paquete').addEventListener('input', calcDescuento);

    // Submit AJAX
    document.getElementById('formPaquete').addEventListener('submit', function(e) {
        e.preventDefault();
        var formData = new FormData(this);
        fetch('<?= url('reservas', 'abon', 'guardarPaquete') ?>', { method: 'POST', body: formData })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.status === 'success' || data.success) {
                    Swal.fire('¡Guardado!', data.message || '', 'success')
                        .then(function() { location.reload(); });
                } else {
                    Swal.fire('Error', data.message || 'Error al guardar', 'error');
                }
            })
            .catch(function() { Swal.fire('Error', 'Error de conexión', 'error'); });
    });
});
</script>
