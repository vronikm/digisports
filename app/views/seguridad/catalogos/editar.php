<?php
/**
 * Vista: Crear/Editar grupo de catálogo
 * Ruta: seguridad/catalogos/editar.php
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
                    </div>
                    <form id="formCatalogo" method="POST">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                        <input type="hidden" name="st_id" value="<?= htmlspecialchars($catalogo['st_id'] ?? '') ?>">

                        <div class="card-body">
                            <!-- Nombre -->
                            <div class="form-group">
                                <label for="st_nombre">Nombre del Catálogo <span class="text-danger">*</span></label>
                                <input type="text" id="st_nombre" name="st_nombre" class="form-control" 
                                       value="<?= htmlspecialchars($catalogo['st_nombre'] ?? '') ?>"
                                       placeholder="Ej: tipo_documento, nacionalidad"
                                       required maxlength="255">
                                <small class="form-text text-muted">
                                    Nombre único que identifica el grupo de catálogo
                                </small>
                            </div>

                            <!-- Descripción -->
                            <div class="form-group">
                                <label for="st_descripcion">Descripción</label>
                                <textarea id="st_descripcion" name="st_descripcion" class="form-control" 
                                          rows="3" placeholder="Descripción del catálogo" 
                                          maxlength="500"><?= htmlspecialchars($catalogo['st_descripcion'] ?? '') ?></textarea>
                                <small class="form-text text-muted">
                                    Describe el propósito de este catálogo
                                </small>
                            </div>

                            <!-- Estado -->
                            <div class="form-group">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="st_activo" 
                                           name="st_activo" value="1" 
                                           <?= (($catalogo['st_activo'] ?? 1) ? 'checked' : '') ?>>
                                    <label class="custom-control-label" for="st_activo">
                                        Activo
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary" id="btnGuardar">
                                <i class="fas fa-save"></i> <?= $catalogo ? 'Actualizar' : 'Crear' ?>
                            </button>
                            <a href="<?= url('seguridad', 'seguridad_tabla', 'index') ?>" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<?php ob_start(); ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.getElementById('formCatalogo').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const isNew = !document.querySelector('input[name="st_id"]').value;
    const action = isNew ? 'crear' : 'actualizar';
    const url = '<?= url('seguridad', 'seguridad_tabla', '') ?>' + action;
    
    Swal.fire({
        title: '¿Confirmar?',
        text: `¿Deseas ${isNew ? 'crear' : 'actualizar'} este catálogo?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Confirmar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('btnGuardar').disabled = true;
            
            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: new URLSearchParams(new FormData(this))
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: data.message,
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true
                    });
                    setTimeout(() => {
                        window.location.href = '<?= url('seguridad', 'seguridad_tabla', 'index') ?>';
                    }, 1500);
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message
                    });
                    document.getElementById('btnGuardar').disabled = false;
                }
            })
            .catch(err => {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error al procesar: ' + err.message
                });
                document.getElementById('btnGuardar').disabled = false;
            });
        }
    });
});
</script>
<?php $GLOBALS['extra_scripts'] = ob_get_clean(); ?>
