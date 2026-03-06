<?php
/**
 * Vista: Listado de grupos de catálogos
 * Ruta: seguridad/catalogos/index.php
 */
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">
                    <i class="fas fa-list-alt mr-2"></i>Administración de Catálogos
                </h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= url('index', 'index', 'index') ?>">Inicio</a></li>
                    <li class="breadcrumb-item"><a href="<?= url('seguridad', 'dashboard', 'index') ?>">Seguridad</a></li>
                    <li class="breadcrumb-item active">Catálogos</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        
        <!-- Barra de búsqueda y acciones -->
        <div class="row mb-3">
            <div class="col-md-6">
                <form method="POST" class="form-inline" action="<?= url('seguridad', 'seguridad_tabla', 'index') ?>">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                    <div class="input-group input-group-sm">
                        <input type="text" name="filtro" class="form-control" 
                               placeholder="Buscar catálogo..." 
                               value="<?= htmlspecialchars($filtro ?? '') ?>">
                        <span class="input-group-append">
                            <button class="btn btn-info" type="submit">
                                <i class="fas fa-search"></i> Buscar
                            </button>
                        </span>
                    </div>
                </form>
            </div>
            <div class="col-md-6 text-right">
                <a href="<?= url('seguridad', 'seguridad_tabla', 'editar') ?>" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Crear Catálogo
                </a>
            </div>
        </div>

        <!-- Tabla de catálogos -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Listado de Catálogos</h3>
                <div class="card-tools">
                    <span class="badge badge-primary"><?= count($catalogos ?? []) ?></span>
                </div>
            </div>
            <div class="card-body">
                <?php if (empty($catalogos)): ?>
                    <div class="alert alert-info" role="alert">
                        <i class="fas fa-info-circle"></i> No hay catálogos registrados
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover">
                            <thead class="bg-light">
                                <tr>
                                    <th style="width: 30px">#</th>
                                    <th>Nombre</th>
                                    <th>Descripción</th>
                                    <th style="width: 80px">Ítems</th>
                                    <th style="width: 80px">Estado</th>
                                    <th style="width: 180px">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($catalogos as $cat): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($cat['st_id']) ?></td>
                                        <td>
                                            <strong><?= htmlspecialchars($cat['st_nombre']) ?></strong>
                                        </td>
                                        <td>
                                            <?= htmlspecialchars(substr($cat['st_descripcion'] ?? '', 0, 50)) ?>
                                        </td>
                                        <td>
                                            <span class="badge badge-info"><?= $cat['cantidad_items'] ?></span>
                                        </td>
                                        <td>
                                            <?php if ($cat['st_activo']): ?>
                                                <span class="badge badge-success">Activo</span>
                                            <?php else: ?>
                                                <span class="badge badge-danger">Inactivo</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="<?= url('seguridad', 'seguridad_tabla', 'items', ['id' => $cat['st_id']]) ?>"
                                               class="btn btn-xs btn-info" title="Ver ítems">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="<?= url('seguridad', 'seguridad_tabla', 'editar', ['id' => $cat['st_id']]) ?>"
                                               class="btn btn-xs btn-warning" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" class="btn btn-xs btn-danger btn-eliminar"
                                                    data-id="<?= (int)$cat['st_id'] ?>"
                                                    data-nombre="<?= htmlspecialchars($cat['st_nombre']) ?>">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</section>

<?php ob_start(); ?>
<script nonce="<?= cspNonce() ?>">
document.addEventListener('DOMContentLoaded', function () {
    var urlEliminar = '<?= url('seguridad', 'seguridad_tabla', 'eliminar') ?>';
    var csrfToken   = '<?= htmlspecialchars($csrf_token ?? '') ?>';

    document.querySelectorAll('.btn-eliminar').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var id     = this.dataset.id;
            var nombre = this.dataset.nombre;
            Swal.fire({
                title: '¿Eliminar catálogo?',
                text: '¿Deseas eliminar el catálogo "' + nombre + '"?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Confirmar',
                cancelButtonText: 'Cancelar'
            }).then(function (result) {
                if (!result.isConfirmed) return;
                fetch(urlEliminar, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: new URLSearchParams({ csrf_token: csrfToken, st_id: id })
                })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    if (data.success) {
                        Swal.fire({ toast: true, position: 'top-end', icon: 'success',
                            title: data.message, showConfirmButton: false,
                            timer: 3000, timerProgressBar: true });
                        setTimeout(function () { location.reload(); }, 1000);
                    } else {
                        Swal.fire({ icon: 'error', title: 'Error', text: data.message });
                    }
                })
                .catch(function (err) {
                    Swal.fire({ icon: 'error', title: 'Error',
                        text: 'Error al procesar la solicitud: ' + err.message });
                });
            });
        });
    });
});
</script>
<?php $scripts = ob_get_clean(); ?>
