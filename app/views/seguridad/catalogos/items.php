<?php
/**
 * Vista: Listado de ítems de un catálogo
 * Ruta: seguridad/catalogos/items.php
 */
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">
                    <i class="fas fa-list mr-2"></i>Ítems: <?= htmlspecialchars($grupo['st_nombre'] ?? '') ?>
                </h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= url('index', 'index', 'index') ?>">Inicio</a></li>
                    <li class="breadcrumb-item"><a href="<?= url('seguridad', 'seguridad_tabla', 'index') ?>">Catálogos</a></li>
                    <li class="breadcrumb-item active">Ítems</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">

        <!-- Barra de acciones -->
        <div class="row mb-3">
            <div class="col-md-12 text-right">
                <a href="<?= url('seguridad', 'seguridad_tabla_catalogo', 'editar', ['grupoId' => $grupo['st_id']]) ?>"
                   class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Crear Ítem
                </a>
                <a href="<?= url('seguridad', 'seguridad_tabla', 'index') ?>" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
            </div>
        </div>

        <!-- Tabla de ítems -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    Ítems del Catálogo
                    <small class="text-muted ml-2" id="contadorItems"></small>
                </h3>
            </div>
            <div class="card-body">
                <div id="itemsContainer">
                    <div class="text-center py-4">
                        <i class="fas fa-spinner fa-spin fa-2x text-muted"></i>
                        <p class="text-muted mt-2">Cargando ítems...</p>
                    </div>
                </div>
            </div>
        </div>

    </div>
</section>

<?php ob_start(); ?>
<script nonce="<?= cspNonce() ?>">
(function () {
    // ── Configuración ────────────────────────────────────────────────────────
    var grupoId    = <?= (int)($grupo['st_id'] ?? 0) ?>;
    // El parámetro grupoId se añade con & porque la URL ya tiene ?r=...
    var urlListar  = '<?= url('seguridad', 'seguridad_tabla_catalogo', 'listar') ?>&grupoId=' + grupoId;
    var urlEditar  = '<?= url('seguridad', 'seguridad_tabla_catalogo', 'editar') ?>&grupoId=' + grupoId;
    var urlEliminar = '<?= url('seguridad', 'seguridad_tabla_catalogo', 'eliminar') ?>';
    var csrfToken  = '<?= htmlspecialchars($csrf_token ?? '') ?>';

    // ── Utilidades ───────────────────────────────────────────────────────────
    function esc(val) {
        if (val === null || val === undefined) return '';
        return String(val)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    // ── Carga de ítems (AJAX) ────────────────────────────────────────────────
    function cargarItems() {
        document.getElementById('itemsContainer').innerHTML =
            '<div class="text-center py-4"><i class="fas fa-spinner fa-spin fa-2x text-muted"></i>' +
            '<p class="text-muted mt-2">Cargando ítems...</p></div>';
        document.getElementById('contadorItems').textContent = '';

        fetch(urlListar, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (r) {
                if (!r.ok) throw new Error('HTTP ' + r.status);
                return r.json();
            })
            .then(function (data) {
                if (data.success && Array.isArray(data.items)) {
                    renderItems(data.items);
                } else {
                    mostrarError('Error al cargar ítems: ' + (data.message || 'Error desconocido'));
                }
            })
            .catch(function (err) {
                mostrarError('Error de conexión: ' + err.message);
            });
    }

    function renderItems(items) {
        var contEl = document.getElementById('contadorItems');
        contEl.textContent = '(' + items.length + ' registro' + (items.length !== 1 ? 's' : '') + ')';

        if (items.length === 0) {
            document.getElementById('itemsContainer').innerHTML =
                '<div class="alert alert-info"><i class="fas fa-info-circle"></i> ' +
                'No hay ítems registrados para este catálogo.</div>';
            return;
        }

        var rows = items.map(function (item) {
            var estadoBadge = item.stc_activo
                ? '<span class="badge badge-success">Activo</span>'
                : '<span class="badge badge-danger">Inactivo</span>';
            return '<tr>' +
                '<td>' + esc(item.stc_id) + '</td>' +
                '<td><strong>' + esc(item.stc_codigo) + '</strong></td>' +
                '<td>' + esc(item.stc_valor) + '</td>' +
                '<td>' + esc(item.stc_etiqueta) + '</td>' +
                '<td class="text-center">' + esc(item.stc_orden) + '</td>' +
                '<td>' + estadoBadge + '</td>' +
                '<td>' +
                    '<a href="' + urlEditar + '&itemId=' + esc(item.stc_id) + '" ' +
                       'class="btn btn-xs btn-warning" title="Editar">' +
                        '<i class="fas fa-edit"></i>' +
                    '</a> ' +
                    '<button type="button" class="btn btn-xs btn-danger btn-del-item" ' +
                            'data-id="' + esc(item.stc_id) + '" ' +
                            'data-etiqueta="' + esc(item.stc_etiqueta || item.stc_valor) + '">' +
                        '<i class="fas fa-trash"></i>' +
                    '</button>' +
                '</td>' +
                '</tr>';
        }).join('');

        document.getElementById('itemsContainer').innerHTML =
            '<div class="table-responsive">' +
            '<table class="table table-bordered table-striped table-hover">' +
            '<thead class="bg-light"><tr>' +
                '<th style="width:40px">#</th>' +
                '<th>Código</th>' +
                '<th>Valor</th>' +
                '<th>Etiqueta</th>' +
                '<th style="width:60px" class="text-center">Orden</th>' +
                '<th style="width:80px">Estado</th>' +
                '<th style="width:160px">Acciones</th>' +
            '</tr></thead>' +
            '<tbody>' + rows + '</tbody>' +
            '</table></div>';

        // Event delegation para botones eliminar (evita onclick inline)
        document.getElementById('itemsContainer').addEventListener('click', function (e) {
            var btn = e.target.closest('.btn-del-item');
            if (!btn) return;
            eliminarItem(btn.dataset.id, btn.dataset.etiqueta);
        });
    }

    function mostrarError(msg) {
        document.getElementById('itemsContainer').innerHTML =
            '<div class="alert alert-danger"><i class="fas fa-exclamation-triangle"></i> ' +
            esc(msg) + '</div>';
    }

    // ── Eliminar ítem ────────────────────────────────────────────────────────
    function eliminarItem(id, etiqueta) {
        Swal.fire({
            title: '¿Eliminar ítem?',
            text: 'Se eliminará "' + etiqueta + '" de forma permanente.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then(function (result) {
            if (!result.isConfirmed) return;
            fetch(urlEliminar, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: new URLSearchParams({ csrf_token: csrfToken, stc_id: id })
            })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.success) {
                    Swal.fire({ toast: true, position: 'top-end', icon: 'success',
                        title: data.message, showConfirmButton: false,
                        timer: 3000, timerProgressBar: true });
                    cargarItems();
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: data.message });
                }
            })
            .catch(function (err) {
                Swal.fire({ icon: 'error', title: 'Error de conexión', text: err.message });
            });
        });
    }

    // ── Inicio ───────────────────────────────────────────────────────────────
    document.addEventListener('DOMContentLoaded', cargarItems);
}());
</script>
<?php $scripts = ob_get_clean(); ?>
