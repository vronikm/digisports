<?php
/**
 * DigiSports Store - Formulario Nuevo Cliente
 */
$moduloColor = $modulo_actual['color'] ?? '#F59E0B';
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6"><h1 class="m-0"><i class="fas fa-user-plus mr-2" style="color:<?= $moduloColor ?>"></i>Nuevo Cliente</h1></div>
            <div class="col-sm-6"><div class="float-sm-right"><a href="<?= url('store', 'cliente', 'index') ?>" class="btn btn-sm btn-outline-secondary"><i class="fas fa-arrow-left mr-1"></i> Volver</a></div></div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <form id="formCrearCliente">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
            <div class="row">
                <div class="col-md-8">
                    <div class="card shadow-sm">
                        <div class="card-header py-2"><h6 class="mb-0"><i class="fas fa-id-card mr-1"></i> Datos del Cliente</h6></div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group"><label class="small">Tipo Identificación</label>
                                        <select name="tipo_id" class="form-control form-control-sm">
                                            <option value="CED">Cédula</option><option value="RUC">RUC</option><option value="PAS">Pasaporte</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <div class="form-group"><label class="small">Nº Identificación</label><input type="text" name="identificacion" class="form-control form-control-sm" placeholder="CI / RUC / Pasaporte"></div>
                                </div>
                                <div class="col-md-6"><div class="form-group"><label class="small">Nombres *</label><input type="text" name="nombres" class="form-control form-control-sm" required></div></div>
                                <div class="col-md-6"><div class="form-group"><label class="small">Apellidos</label><input type="text" name="apellidos" class="form-control form-control-sm"></div></div>
                                <div class="col-md-6"><div class="form-group"><label class="small">Email</label><input type="email" name="email" class="form-control form-control-sm"></div></div>
                                <div class="col-md-3"><div class="form-group"><label class="small">Teléfono</label><input type="text" name="telefono" class="form-control form-control-sm"></div></div>
                                <div class="col-md-3"><div class="form-group"><label class="small">Celular</label><input type="text" name="celular" class="form-control form-control-sm"></div></div>
                                <div class="col-md-8"><div class="form-group"><label class="small">Dirección</label><input type="text" name="direccion" class="form-control form-control-sm"></div></div>
                                <div class="col-md-4"><div class="form-group"><label class="small">Ciudad</label><input type="text" name="ciudad" class="form-control form-control-sm"></div></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card shadow-sm">
                        <div class="card-header py-2"><h6 class="mb-0"><i class="fas fa-cog mr-1"></i> Opciones</h6></div>
                        <div class="card-body">
                            <div class="form-group"><label class="small">Fecha Nacimiento</label><input type="date" name="fecha_nacimiento" class="form-control form-control-sm"></div>
                            <div class="form-group"><label class="small">Género</label>
                                <select name="genero" class="form-control form-control-sm">
                                    <option value="">— Sin especificar —</option>
                                    <option value="M">Masculino</option><option value="F">Femenino</option><option value="O">Otro</option>
                                </select>
                            </div>
                            <div class="custom-control custom-switch mt-3">
                                <input type="checkbox" class="custom-control-input" id="swMkt" name="acepta_marketing" value="1">
                                <label class="custom-control-label small" for="swMkt">Acepta comunicaciones de marketing</label>
                            </div>
                            <div class="form-group mt-3"><label class="small">Notas</label><textarea name="notas" rows="3" class="form-control form-control-sm" placeholder="Observaciones internas..."></textarea></div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-block" style="background:<?= $moduloColor ?>;color:white"><i class="fas fa-save mr-1"></i> Registrar Cliente</button>
                </div>
            </div>
        </form>
    </div>
</section>

<?php ob_start(); ?>
<script>
document.getElementById('formCrearCliente').addEventListener('submit', function(e) {
    e.preventDefault();
    var fd = new FormData(this);
    if (!fd.get('acepta_marketing')) fd.set('acepta_marketing', '0');
    fetch('<?= url('store', 'cliente', 'crear') ?>', { method: 'POST', body: fd })
    .then(function(r) { return r.json(); })
    .then(function(d) {
        if (d.success) {
            Swal.fire({ icon: 'success', title: d.message, timer: 1500, showConfirmButton: false })
            .then(function() { window.location.href = '<?= url('store', 'cliente', 'ver') ?>&id=' + d.id; });
        } else {
            Swal.fire({ icon: 'error', title: 'Error', text: d.message });
        }
    }).catch(function() { Swal.fire({ icon: 'error', title: 'Error de conexión' }); });
});
</script>
<?php $scripts = ob_get_clean(); ?>
