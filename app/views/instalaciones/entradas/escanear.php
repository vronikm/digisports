<?php
/**
 * DigiSports Arena — Vista: Control de Acceso / Escanear Código
 * Pantalla de búsqueda rápida por código de entrada
 */
$csrf = $csrf_token ?? '';
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-qrcode mr-2 text-info"></i> Control de Acceso</h1>
            </div>
            <div class="col-sm-6 text-right">
                <a href="<?= url('instalaciones', 'entrada', 'index') ?>" class="btn btn-secondary">
                    <i class="fas fa-arrow-left mr-1"></i> Volver
                </a>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">

                <!-- Búsqueda -->
                <div class="card card-outline card-info">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-search fa-3x text-info mb-3"></i>
                        <h4 class="mb-3">Ingrese o escanee el código de entrada</h4>
                        <div class="input-group input-group-lg mb-3">
                            <input type="text" id="inputCodigo" class="form-control text-center font-weight-bold"
                                   placeholder="ENT250203XXXX" autofocus
                                   style="font-size: 1.5em; letter-spacing: 2px;">
                            <div class="input-group-append">
                                <button class="btn btn-info" id="btnBuscar">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                        <small class="text-muted">Presione Enter para buscar</small>
                    </div>
                </div>

                <!-- Resultado -->
                <div id="resultado" style="display:none;">
                    <div class="card" id="cardResultado">
                        <div class="card-header" id="resultadoHeader">
                            <h3 class="card-title" id="resultadoTitulo"></h3>
                        </div>
                        <div class="card-body" id="resultadoBody"></div>
                        <div class="card-footer text-center" id="resultadoFooter"></div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</section>

<script>
var inputCodigo = document.getElementById('inputCodigo');

inputCodigo.addEventListener('keydown', function(e) {
    if (e.key === 'Enter') buscarEntrada();
});
document.getElementById('btnBuscar').addEventListener('click', buscarEntrada);

function buscarEntrada() {
    var codigo = inputCodigo.value.trim().toUpperCase();
    if (!codigo) {
        inputCodigo.focus();
        return;
    }

    document.getElementById('resultado').style.display = 'block';
    document.getElementById('resultadoBody').innerHTML = '<div class="text-center py-3"><i class="fas fa-spinner fa-spin fa-2x text-info"></i></div>';
    document.getElementById('resultadoFooter').innerHTML = '';

    fetch('<?= url('instalaciones', 'entrada', 'buscarCodigo') ?>&codigo=' + encodeURIComponent(codigo))
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) {
                mostrarEntrada(data.data.entrada);
            } else {
                document.getElementById('cardResultado').className = 'card card-outline card-danger';
                document.getElementById('resultadoHeader').className = 'card-header bg-danger text-white';
                document.getElementById('resultadoTitulo').innerHTML = '<i class="fas fa-times-circle mr-1"></i> No encontrada';
                document.getElementById('resultadoBody').innerHTML = '<div class="text-center py-3"><p class="text-danger h5">' + (data.message || 'Código no encontrado') + '</p></div>';
                document.getElementById('resultadoFooter').innerHTML = '';
                inputCodigo.value = '';
                inputCodigo.focus();
            }
        })
        .catch(function() {
            document.getElementById('resultadoBody').innerHTML = '<p class="text-danger">Error de comunicación</p>';
        });
}

function mostrarEntrada(e) {
    var estadoColors = {VENDIDA:'success',USADA:'secondary',ANULADA:'danger',VENCIDA:'dark'};
    var color = estadoColors[e.ent_estado] || 'secondary';

    document.getElementById('cardResultado').className = 'card card-outline card-' + color;
    document.getElementById('resultadoHeader').className = 'card-header bg-' + color + ' text-white';
    document.getElementById('resultadoTitulo').innerHTML = '<i class="fas fa-ticket-alt mr-1"></i> ' + e.ent_codigo + ' — ' + e.ent_estado;

    var html = '<table class="table table-borderless">';
    html += '<tr><td class="text-muted" width="40%">Instalación:</td><td><strong>' + e.instalacion_nombre + '</strong></td></tr>';
    html += '<tr><td class="text-muted">Tipo:</td><td><span class="badge badge-primary">' + e.ent_tipo + '</span></td></tr>';
    html += '<tr><td class="text-muted">Fecha:</td><td>' + e.ent_fecha_entrada + '</td></tr>';
    if (e.cliente_nombre) {
        html += '<tr><td class="text-muted">Cliente:</td><td>' + e.cliente_nombre + '</td></tr>';
    }
    html += '<tr><td class="text-muted">Total:</td><td><strong class="text-success">$' + parseFloat(e.ent_total).toFixed(2) + '</strong></td></tr>';
    html += '<tr><td class="text-muted">Estado:</td><td><span class="badge badge-' + color + ' px-3 py-2">' + e.ent_estado + '</span></td></tr>';
    html += '</table>';

    document.getElementById('resultadoBody').innerHTML = html;

    // Botón de acción según estado
    var footer = '';
    if (e.ent_estado === 'VENDIDA') {
        footer = '<button class="btn btn-success btn-lg" onclick="registrarIngreso(' + e.ent_entrada_id + ', \'' + e.ent_codigo + '\')">';
        footer += '<i class="fas fa-door-open mr-2"></i> PERMITIR INGRESO</button>';
    } else if (e.ent_estado === 'USADA') {
        footer = '<div class="alert alert-secondary mb-0"><i class="fas fa-info-circle mr-1"></i> Esta entrada ya fue utilizada</div>';
    } else if (e.ent_estado === 'ANULADA') {
        footer = '<div class="alert alert-danger mb-0"><i class="fas fa-ban mr-1"></i> Entrada ANULADA — Acceso denegado</div>';
    }
    document.getElementById('resultadoFooter').innerHTML = footer;

    inputCodigo.value = '';
    inputCodigo.focus();
}

function registrarIngreso(id, codigo) {
    Swal.fire({
        title: '¿Permitir ingreso?',
        html: 'Entrada <strong>' + codigo + '</strong>',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        confirmButtonText: '<i class="fas fa-door-open mr-1"></i> Permitir',
        cancelButtonText: 'Cancelar'
    }).then(function(result) {
        if (result.isConfirmed) {
            var form = new FormData();
            form.append('entrada_id', id);
            fetch('<?= url('instalaciones', 'entrada', 'registrarIngreso') ?>', {
                method: 'POST', body: form
            }).then(function(r){ return r.json(); }).then(function(data) {
                if (data.success) {
                    Swal.fire({
                        title: '¡ACCESO PERMITIDO!',
                        text: data.message,
                        icon: 'success',
                        timer: 3000,
                        showConfirmButton: false
                    }).then(function() {
                        document.getElementById('resultado').style.display = 'none';
                        inputCodigo.focus();
                    });
                } else {
                    Swal.fire('ACCESO DENEGADO', data.message, 'error');
                }
            });
        }
    });
}
</script>
