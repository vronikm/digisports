<?php
/**
 * Vista: Formulario de Cliente (Crear/Editar)
 */

$cliente = $cliente ?? [];
$tiposCliente = $tiposCliente ?? [];
$tiposIdentificacion = $tiposIdentificacion ?? [];
$errores = $errores ?? [];
$esEdicion = !empty($cliente['cliente_id']);
$titulo = $esEdicion ? 'Editar Cliente' : 'Nuevo Cliente';
?>

<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">
                    <i class="fas fa-<?= $esEdicion ? 'edit' : 'user-plus' ?> text-primary"></i>
                    <?= $titulo ?>
                </h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= url('core', 'hub', 'index') ?>">Hub</a></li>
                    <li class="breadcrumb-item"><a href="<?= url('clientes', 'cliente', 'index') ?>">Clientes</a></li>
                    <li class="breadcrumb-item active"><?= $titulo ?></li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        
        <?php if (!empty($errores)): ?>
        <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <h5><i class="icon fas fa-ban"></i> Error</h5>
            <ul class="mb-0">
                <?php foreach ($errores as $error): ?>
                <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>
        
        <form method="POST" action="<?= url('clientes', 'cliente', $esEdicion ? 'actualizar' : 'guardar') ?>" id="formCliente">
            <input type="hidden" name="csrf_token" value="<?= \Security::generateCsrfToken() ?>">
            <?php if ($esEdicion): ?>
            <input type="hidden" name="cliente_id" value="<?= $cliente['cliente_id'] ?>">
            <?php endif; ?>
            
            <div class="row">
                <!-- Datos personales -->
                <div class="col-md-8">
                    <div class="card card-primary card-outline">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-id-card"></i> Datos de Identificación
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Tipo Identificación <span class="text-danger">*</span></label>
                                        <select name="tipo_identificacion" class="form-control" required>
                                            <option value="">Seleccione...</option>
                                            <?php foreach ($tiposIdentificacion as $key => $label): ?>
                                            <option value="<?= $key ?>" <?= ($cliente['tipo_identificacion'] ?? '') === $key ? 'selected' : '' ?>>
                                                <?= $label ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Identificación <span class="text-danger">*</span></label>
                                        <input type="text" name="identificacion" class="form-control" required
                                               maxlength="20"
                                               value="<?= htmlspecialchars($cliente['identificacion'] ?? '') ?>"
                                               placeholder="Número de documento">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Tipo de Cliente <span class="text-danger">*</span></label>
                                        <select name="tipo_cliente" class="form-control" required>
                                            <?php foreach ($tiposCliente as $key => $label): ?>
                                            <option value="<?= $key ?>" <?= ($cliente['tipo_cliente'] ?? 'CLIENTE') === $key ? 'selected' : '' ?>>
                                                <?= $label ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Nombres <span class="text-danger">*</span></label>
                                        <input type="text" name="nombres" class="form-control" required
                                               maxlength="150"
                                               value="<?= htmlspecialchars($cliente['nombres'] ?? '') ?>"
                                               placeholder="Nombres completos">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Apellidos <span class="text-danger">*</span></label>
                                        <input type="text" name="apellidos" class="form-control" required
                                               maxlength="150"
                                               value="<?= htmlspecialchars($cliente['apellidos'] ?? '') ?>"
                                               placeholder="Apellidos completos">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Contacto -->
                    <div class="card card-info card-outline">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-phone"></i> Información de Contacto
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Email</label>
                                        <input type="email" name="email" class="form-control"
                                               maxlength="100"
                                               value="<?= htmlspecialchars($cliente['email'] ?? '') ?>"
                                               placeholder="correo@ejemplo.com">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Teléfono</label>
                                        <input type="tel" name="telefono" class="form-control"
                                               maxlength="15"
                                               value="<?= htmlspecialchars($cliente['telefono'] ?? '') ?>"
                                               placeholder="Teléfono fijo">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Celular</label>
                                        <input type="tel" name="celular" class="form-control"
                                               maxlength="15"
                                               value="<?= htmlspecialchars($cliente['celular'] ?? '') ?>"
                                               placeholder="Número celular">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label>Dirección</label>
                                <textarea name="direccion" class="form-control" rows="2"
                                          maxlength="400"
                                          placeholder="Dirección completa"><?= htmlspecialchars($cliente['direccion'] ?? '') ?></textarea>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Fecha de Nacimiento</label>
                                        <input type="date" name="fecha_nacimiento" class="form-control"
                                               value="<?= htmlspecialchars($cliente['fecha_nacimiento'] ?? '') ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Panel lateral -->
                <div class="col-md-4">
                    <div class="card card-success card-outline">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-cog"></i> Estado
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label>Estado del Cliente</label>
                                <select name="estado" class="form-control">
                                    <option value="A" <?= ($cliente['estado'] ?? 'A') === 'A' ? 'selected' : '' ?>>
                                        Activo
                                    </option>
                                    <option value="I" <?= ($cliente['estado'] ?? '') === 'I' ? 'selected' : '' ?>>
                                        Inactivo
                                    </option>
                                </select>
                            </div>
                            
                            <?php if ($esEdicion): ?>
                            <div class="form-group">
                                <label>Saldo a Favor</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">$</span>
                                    </div>
                                    <input type="text" class="form-control text-right" readonly
                                           value="<?= number_format($cliente['saldo_abono'] ?? 0, 2) ?>">
                                </div>
                                <small class="text-muted">El saldo se gestiona desde el módulo de Abonos</small>
                            </div>
                            
                            <div class="form-group">
                                <label>Fecha de Registro</label>
                                <input type="text" class="form-control" readonly
                                       value="<?= date('d/m/Y H:i', strtotime($cliente['fecha_registro'] ?? 'now')) ?>">
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Acciones -->
                    <div class="card">
                        <div class="card-body">
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-save"></i>
                                <?= $esEdicion ? 'Guardar Cambios' : 'Crear Cliente' ?>
                            </button>
                            <a href="<?= url('clientes', 'cliente', 'index') ?>" class="btn btn-secondary btn-block">
                                <i class="fas fa-times"></i> Cancelar
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
        
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Validación del formulario
    document.getElementById('formCliente').addEventListener('submit', function(e) {
        const identificacion = document.querySelector('[name="identificacion"]').value;
        const nombres = document.querySelector('[name="nombres"]').value;
        const apellidos = document.querySelector('[name="apellidos"]').value;
        
        if (!identificacion.trim() || !nombres.trim() || !apellidos.trim()) {
            e.preventDefault();
            Swal.fire('Error', 'Complete todos los campos obligatorios', 'error');
            return false;
        }
    });
    
    // Validación de cédula ecuatoriana
    document.querySelector('[name="identificacion"]').addEventListener('blur', function() {
        const tipo = document.querySelector('[name="tipo_identificacion"]').value;
        const cedula = this.value;
        
        if (tipo === 'CED' && cedula.length === 10) {
            // Validación básica de cédula ecuatoriana
            const digitoRegion = parseInt(cedula.substring(0, 2));
            if (digitoRegion < 1 || digitoRegion > 24) {
                Swal.fire('Advertencia', 'El número de cédula parece incorrecto', 'warning');
            }
        }
    });
});
</script>
