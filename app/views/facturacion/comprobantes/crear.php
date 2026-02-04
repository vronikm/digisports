<?php
/**
 * Crear Nuevo Comprobante Electrónico
 */
?>
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="mb-0">
                    <i class="fas fa-file-invoice-dollar text-warning"></i> Nuevo Comprobante
                </h1>
                <a href="<?= url('facturacion', 'comprobante') ?>" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Volver al Listado
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card card-warning card-outline">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-edit"></i> Datos del Comprobante
                    </h3>
                </div>
                <div class="card-body">
                    
                    <!-- Mensaje de módulo en desarrollo -->
                    <div class="alert alert-warning text-center">
                        <i class="fas fa-hard-hat fa-3x mb-3"></i>
                        <h4>Módulo en Desarrollo</h4>
                        <p class="mb-3">
                            La emisión de comprobantes electrónicos requiere configuración adicional:
                        </p>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-check text-muted"></i> Certificado digital (.p12)</li>
                            <li><i class="fas fa-check text-muted"></i> Credenciales SRI</li>
                            <li><i class="fas fa-check text-muted"></i> Secuenciales autorizados</li>
                            <li><i class="fas fa-check text-muted"></i> Información del emisor</li>
                        </ul>
                    </div>

                    <hr>

                    <!-- Formulario de ejemplo (deshabilitado) -->
                    <form id="formComprobante">
                        <div class="form-group">
                            <label>Tipo de Comprobante</label>
                            <select class="form-control" disabled>
                                <option>Factura</option>
                                <option>Nota de Crédito</option>
                                <option>Nota de Débito</option>
                            </select>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Establecimiento</label>
                                <input type="text" class="form-control" value="001" disabled>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Punto de Emisión</label>
                                <input type="text" class="form-control" value="001" disabled>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Cliente</label>
                            <select class="form-control" disabled>
                                <option>-- Seleccionar cliente --</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Reserva Asociada (opcional)</label>
                            <select class="form-control" disabled>
                                <option>-- Ninguna --</option>
                            </select>
                        </div>

                        <hr>

                        <h5>Detalle</h5>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Descripción</th>
                                        <th width="100">Cantidad</th>
                                        <th width="120">P. Unitario</th>
                                        <th width="120">Total</th>
                                        <th width="50"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">
                                            <i class="fas fa-info-circle"></i> Agregue items al comprobante
                                        </td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="3" class="text-right"><strong>Subtotal 12%:</strong></td>
                                        <td>$0.00</td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td colspan="3" class="text-right"><strong>Subtotal 0%:</strong></td>
                                        <td>$0.00</td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td colspan="3" class="text-right"><strong>IVA 12%:</strong></td>
                                        <td>$0.00</td>
                                        <td></td>
                                    </tr>
                                    <tr class="table-warning">
                                        <td colspan="3" class="text-right"><strong>TOTAL:</strong></td>
                                        <td><strong>$0.00</strong></td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                    </form>

                </div>
                <div class="card-footer text-center">
                    <button type="button" class="btn btn-secondary" disabled>
                        <i class="fas fa-save"></i> Guardar Borrador
                    </button>
                    <button type="button" class="btn btn-warning" disabled>
                        <i class="fas fa-paper-plane"></i> Emitir Comprobante
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
