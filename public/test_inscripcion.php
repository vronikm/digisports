<!DOCTYPE html>
<html>
<head>
    <title>Test Modal Bootstrap 4</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">
    <div class="content-wrapper" style="margin-left:0">
        <section class="content p-4">
            <h3>Test Modal Bootstrap 4 - Inscripciones</h3>
            <hr>
            <p><strong>Test 1:</strong> Botón con data-toggle (método Bootstrap nativo):</p>
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#testModal1">
                Abrir Modal (data-toggle)
            </button>
            
            <p class="mt-3"><strong>Test 2:</strong> Botón con onclick + jQuery .modal('show'):</p>
            <button type="button" class="btn btn-success" onclick="$('#testModal1').modal('show')">
                Abrir Modal (jQuery)
            </button>
            
            <p class="mt-3"><strong>Test 3:</strong> Botón con onclick + vanilla JS:</p>
            <button type="button" class="btn btn-warning" onclick="document.getElementById('testModal1').classList.add('show'); document.getElementById('testModal1').style.display='block'; document.body.classList.add('modal-open');">
                Abrir Modal (vanilla JS)
            </button>

            <p class="mt-3"><strong>Test 4:</strong> Réplica exacta del botón de inscripciones:</p>
            <button type="button" class="btn btn-success" onclick="abrirNuevaInscripcion()">
                <i class="fas fa-plus"></i> Nueva Inscripción
            </button>

            <!-- Modal -->
            <div class="modal fade" id="testModal1" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header bg-success text-white">
                            <h5 class="modal-title" id="modalInscripcionTitle">Nueva Inscripción</h5>
                            <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                        </div>
                        <div class="modal-body">
                            <p>¡El modal funciona! Si ves esto, Bootstrap 4 modals funcionan correctamente.</p>
                            <form id="formInscripcion">
                                <div class="form-group">
                                    <label>Alumno</label>
                                    <select class="form-control" id="fin_alumno_id"><option>Test</option></select>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
<script>
function abrirNuevaInscripcion() {
    console.log('abrirNuevaInscripcion called');
    console.log('jQuery version:', $.fn.jquery);
    console.log('Bootstrap modal fn:', typeof $.fn.modal);
    console.log('Modal element:', document.getElementById('testModal1'));
    $('#testModal1').modal('show');
}

$(document).ready(function() {
    console.log('jQuery loaded:', typeof $);
    console.log('Bootstrap modal available:', typeof $.fn.modal);
});
</script>
</body>
</html>
