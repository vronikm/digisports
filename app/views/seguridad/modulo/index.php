<?php
/**
 * DigiSports Seguridad - Lista de Módulos
 */
$modulos = $modulos ?? [];
?>
<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">
                    <i class="<?= htmlspecialchars($moduloIcono ?? 'fas fa-shield-alt') ?> mr-2" style="color: <?= htmlspecialchars($moduloColor ?? '#6366F1') ?>"></i>
                    <?= htmlspecialchars($moduloNombre ?? 'Seguridad') ?> - Módulos del Sistema
                </h1>
            </div>
            <div class="col-sm-6">
                <div class="float-sm-right">
                    <a href="<?= url('seguridad', 'modulo', 'crear') ?>" class="btn" style="background: <?= htmlspecialchars($moduloColor ?? '#6366F1') ?>; color: white;">
                        <i class="fas fa-plus mr-1"></i> Nuevo Módulo
                    </a>
                    <a href="<?= url('seguridad', 'modulo', 'iconos') ?>" class="btn btn-outline-secondary">
                        <i class="fas fa-icons mr-1"></i> Iconos
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
<section class="content">
    <div class="row g-3 justify-content-center">
        <?php foreach ($modulos as $m) { ?>
        <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
            <div class="card h-100 d-flex flex-column" style="border-top: 4px solid <?= htmlspecialchars($m['color'] ?? '#F59E0B') ?>;">
                <div class="card-body text-center flex-grow-1 d-flex flex-column justify-content-between">
                    <div>
                        <div class="mb-3">
                            <i class="fas <?= !empty($m['mod_icono']) ? $m['mod_icono'] : 'fa-cube' ?> fa-4x" style="color: <?= htmlspecialchars($m['mod_color'] ?? '#F59E0B') ?>;"></i>
                        </div>
                        <h5 class="card-title" style="color: <?= htmlspecialchars($m['mod_color'] ?? '#F59E0B') ?>;">
                            <?= htmlspecialchars($m['mod_nombre']) ?>
                        </h5>
                        <p class="card-text text-muted small">
                            <?= htmlspecialchars($m['mod_descripcion'] ?? 'Sin descripción') ?>
                        </p>
                        <div class="mb-3">
                            <span class="badge badge-light" style="border: 1px solid <?= htmlspecialchars($m['mod_color'] ?? '#F59E0B') ?>; color: <?= htmlspecialchars($m['mod_color'] ?? '#F59E0B') ?>;">
                                <?= $m['mod_codigo'] ?>
                            </span>
                            <?php if ($m['mod_es_externo'] == 'S') { ?>
                                <span class="badge badge-warning">Externo</span>
                            <?php } ?>
                            <span class="badge badge-<?= $m['mod_estado'] == 'A' ? 'success' : 'secondary' ?>">
                                <?= $m['mod_estado'] == 'A' ? 'Activo' : 'Inactivo' ?>
                            </span>
                        </div>
                        <small class="d-block mb-2" style="color: <?= htmlspecialchars($m['mod_color'] ?? '#F59E0B') ?>;">
                            <i class="fas fa-building mr-1"></i>
                            <?= $m['mod_tenants_activos'] ?? 0 ?> tenants
                        </small>
                        <div class="mt-3">
                            <?php if (empty($m['mod_id'])) { ?>
                                <button class="btn btn-danger btn-sm activar-modulo-btn w-100" data-codigo="<?= htmlspecialchars($m['mod_codigo']) ?>" data-nombre="<?= htmlspecialchars($m['mod_nombre']) ?>">
                                    <i class="fas fa-times-circle mr-1"></i> Inactivo - Activar
                                </button>
                            <?php } else { ?>
                                <button class="btn btn-success btn-sm w-100" disabled>
                                    <i class="fas fa-check-circle mr-1"></i> Activo
                                </button>
                            <?php } ?>
                        </div>
                    </div>
                    <div class="mt-3 d-flex justify-content-between">
                        <a href="<?= url('seguridad', 'modulo', 'editar', ['id' => $m['modulo_id']]) ?>" class="btn btn-outline-primary">
                            <i class="fas fa-edit"></i> Editar
                        </a>
                        <a href="<?= url('seguridad', 'modulo', 'duplicar', ['id' => $m['modulo_id']]) ?>" class="btn btn-outline-secondary">
                            <i class="fas fa-copy"></i>
                        </a>
                        <a href="<?= url('seguridad', 'modulo', 'eliminar', ['id' => $m['modulo_id']]) ?>" class="btn btn-outline-danger" onclick="return confirm('¿Eliminar módulo?')">
                            <i class="fas fa-trash"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php } ?>
    </div>
</section>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.activar-modulo-btn').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const codigo = this.getAttribute('data-codigo');
            const nombre = this.getAttribute('data-nombre');
            Swal.fire({
                title: '¿Activar módulo?',
                text: `¿Deseas activar "${nombre}" en el sistema?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Sí, activar',
                cancelButtonText: 'Cancelar'
            });
        });
    });
});
</script>
