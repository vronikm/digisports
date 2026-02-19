<?php
/**
 * DigiSports Store - POS sin turno abierto
 */
$moduloColor = $modulo_actual['color'] ?? '#F59E0B';
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-12">
                <h1 class="m-0"><i class="fas fa-cash-register mr-2" style="color:<?= $moduloColor ?>"></i>Punto de Venta</h1>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="card shadow-sm">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-lock fa-4x mb-3 text-warning"></i>
                        <h3>Turno No Abierto</h3>
                        <p class="text-muted">Debe abrir un turno de caja antes de poder realizar ventas.</p>
                        <a href="<?= url('store', 'caja', 'index') ?>" class="btn btn-lg mt-3" style="background:<?= $moduloColor ?>;color:white;">
                            <i class="fas fa-unlock mr-2"></i> Ir a Gestión de Caja
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
