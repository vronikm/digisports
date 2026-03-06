<?php
/**
 * Vista: Crear/Editar ítem de catálogo
 * Ruta: seguridad/catalogos/item_editar.php
 */
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">
                    <i class="fas fa-edit mr-2"></i><?= htmlspecialchars($title ?? '') ?>
                </h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= url('index', 'index', 'index') ?>">Inicio</a></li>
                    <li class="breadcrumb-item"><a href="<?= url('seguridad', 'seguridad_tabla', 'index') ?>">Catálogos</a></li>
                    <li class="breadcrumb-item">
                        <a href="<?= url('seguridad', 'seguridad_tabla', 'items', ['id' => $grupo['st_id']]) ?>">
                            <?= htmlspecialchars($grupo['st_nombre'] ?? '') ?>
                        </a>
                    </li>
                    <li class="breadcrumb-item active"><?= htmlspecialchars($title ?? '') ?></li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><?= htmlspecialchars($title ?? '') ?></h3>
                        <small class="text-muted d-block mt-2">
                            Catálogo: <strong><?= htmlspecialchars($grupo['st_nombre'] ?? '') ?></strong>
                        </small>
                    </div>
                    <form id="formItem" method="POST">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                        <input type="hidden" name="stc_id" value="<?= htmlspecialchars($item['stc_id'] ?? '') ?>">
                        <input type="hidden" name="stc_tabla_id" value="<?= htmlspecialchars($grupo['st_id'] ?? '') ?>">

                        <div class="card-body">
                            <!-- Código -->
                            <div class="form-group">
                                <label for="stc_codigo">Código <span class="text-danger">*</span></label>
                                <input type="text" id="stc_codigo" name="stc_codigo" class="form-control" 
                                       value="<?= htmlspecialchars($item['stc_codigo'] ?? '') ?>"
                                       placeholder="Ej: CEDULA, DNI, PASAPORTE"
                                       required maxlength="100">
                                <small class="form-text text-muted">
                                    Código único para este ítem dentro del catálogo
                                </small>
                            </div>

                            <!-- Valor -->
                            <div class="form-group">
                                <label for="stc_valor">Valor <span class="text-danger">*</span></label>
                                <input type="text" id="stc_valor" name="stc_valor" class="form-control" 
                                       value="<?= htmlspecialchars($item['stc_valor'] ?? '') ?>"
                                       placeholder="Ej: 1, 2, EC, PE"
                                       required maxlength="255">
                                <small class="form-text text-muted">
                                    Valor que se enviará en formularios
                                </small>
                            </div>

                            <!-- Etiqueta -->
                            <div class="form-group">
                                <label for="stc_etiqueta">Etiqueta <span class="text-danger">*</span></label>
                                <input type="text" id="stc_etiqueta" name="stc_etiqueta" class="form-control" 
                                       value="<?= htmlspecialchars($item['stc_etiqueta'] ?? '') ?>"
                                       placeholder="Ej: Cédula, DNI, Pasaporte"
                                       required maxlength="255">
                                <small class="form-text text-muted">
                                    Etiqueta visible en los formularios
                                </small>
                            </div>

                            <!-- Orden -->
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="stc_orden">Orden</label>
                                    <input type="number" id="stc_orden" name="stc_orden" class="form-control" 
                                           value="<?= htmlspecialchars($item['stc_orden'] ?? 0) ?>"
                                           min="0" step="10">
                                    <small class="form-text text-muted">
                                        Número para ordenar ítems (menor = primero)
                                    </small>
                                </div>

                                <!-- Estado -->
                                <div class="form-group col-md-6">
                                    <label>&nbsp;</label>
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="stc_activo" 
                                               name="stc_activo" value="1" 
                                               <?php if ($item['stc_activo'] ?? 1) echo 'checked'; ?>>
                                        <label class="custom-control-label" for="stc_activo">
                                            Activo
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary" id="btnGuardar">
                                <i class="fas fa-save"></i> <?= $item ? 'Actualizar' : 'Crear' ?>
                            </button>
                            <a href="<?= url('seguridad', 'seguridad_tabla', 'items', ['id' => $grupo['st_id']]) ?>" 
                               class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
// URLs cifradas precalculadas en servidor (NO concatenar al token)
$urlCrear      = url('seguridad', 'seguridad_tabla_catalogo', 'crear');
$urlActualizar = url('seguridad', 'seguridad_tabla_catalogo', 'actualizar');
$urlItems      = url('seguridad', 'seguridad_tabla', 'items');
$isNew         = empty($item['stc_id']);
ob_start();
?>
<script nonce="<?= cspNonce() ?>">
(function () {
    var urlEndpoint  = <?= $isNew ? json_encode($urlCrear) : json_encode($urlActualizar) ?>;
    var urlItemsBase = <?= json_encode($urlItems) ?>;

    document.getElementById('formItem').addEventListener('submit', function (e) {
        e.preventDefault();
        var form = this;

        Swal.fire({
            title: '<?= $isNew ? "¿Crear ítem?" : "¿Guardar cambios?" ?>',
            text:  '<?= $isNew ? "Se creará un nuevo ítem en el catálogo." : "Se actualizará la información del ítem del catálogo." ?>',
            icon:  'question',
            showCancelButton:   true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor:  '#6c757d',
            confirmButtonText:  '<i class="fas fa-save"></i> <?= $isNew ? "Sí, crear" : "Sí, guardar" ?>',
            cancelButtonText:   'Cancelar'
        }).then(function (result) {
            if (!result.isConfirmed) return;

            var btn = document.getElementById('btnGuardar');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando…';

            fetch(urlEndpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: new URLSearchParams(new FormData(form))
            })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.success) {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: data.message || 'Ítem guardado correctamente',
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true
                    });
                    // Redirigir al listado usando & porque urlItemsBase ya contiene ?r=TOKEN
                    var grupoId = document.querySelector('input[name="stc_tabla_id"]').value;
                    setTimeout(function () {
                        window.location.href = urlItemsBase + '&id=' + grupoId;
                    }, 1600);
                } else {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'error',
                        title: data.message || 'Error al guardar el ítem',
                        showConfirmButton: false,
                        timer: 4000,
                        timerProgressBar: true
                    });
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-save"></i> <?= $isNew ? "Crear" : "Actualizar" ?>';
                }
            })
            .catch(function () {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'error',
                    title: 'Error de comunicación con el servidor',
                    showConfirmButton: false,
                    timer: 4000
                });
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-save"></i> <?= $isNew ? "Crear" : "Actualizar" ?>';
            });
        });
    });
}());
</script>
<?php $scripts = ob_get_clean(); ?>
