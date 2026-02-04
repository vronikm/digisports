<?php
/**
 * Formulario de Crear/Editar Cancha
 * @var array $cancha (si es editar)
 * @var array $instalaciones
 * @var string $csrf_token
 * @var string $modo ('editar' o vacio para crear)
 */
$base = baseUrl();
$modo = $modo ?? '';
$tituloSeccion = $modo === 'editar' ? 'Editar Cancha' : 'Nueva Cancha';
$accion = $modo === 'editar' ? 'actualizar' : 'guardar';
// URLs encriptadas
$urlAccion = url('instalaciones', 'cancha', $accion);
$urlListado = url('instalaciones', 'cancha', 'index');
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light border-bottom">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-edit text-primary"></i> <?php echo $tituloSeccion; ?>
                    </h5>
                </div>

                <div class="card-body">
                    <form id="formCancha" method="post" 
                          action="<?php echo $urlAccion; ?>">
                        
                        <!-- CSRF Token -->
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                        
                        <?php if ($modo === 'editar'): ?>
                            <input type="hidden" name="cancha_id" value="<?php echo $cancha['cancha_id']; ?>">
                        <?php endif; ?>

                        <!-- Sección 1: Información Básica -->
                        <div class="border-bottom pb-4 mb-4">
                            <h6 class="fw-bold text-primary mb-3">
                                <i class="fas fa-info-circle"></i> Información Básica
                            </h6>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="nombre" class="form-label">Nombre de la Cancha <span class="text-danger">*</span></label>
                                    <input type="text" id="nombre" name="nombre" class="form-control" required
                                           value="<?php echo htmlspecialchars($cancha['nombre'] ?? ''); ?>"
                                           minlength="3" maxlength="100" 
                                           placeholder="Ej: Cancha 1, Piscina A">
                                    <small class="text-muted d-block mt-1">3 a 100 caracteres</small>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="tipo" class="form-label">Tipo de Cancha <span class="text-danger">*</span></label>
                                    <select id="tipo" name="tipo" class="form-select" required>
                                        <option value="">Selecciona un tipo...</option>
                                        <option value="futbol" <?php echo ($cancha['tipo'] ?? '') === 'futbol' ? 'selected' : ''; ?>>
                                            ⚽ Fútbol
                                        </option>
                                        <option value="futbol_sala" <?php echo ($cancha['tipo'] ?? '') === 'futbol_sala' ? 'selected' : ''; ?>>
                                            ⚽ Fútbol Sala
                                        </option>
                                        <option value="tenis" <?php echo ($cancha['tipo'] ?? '') === 'tenis' ? 'selected' : ''; ?>>
                                            🎾 Tenis
                                        </option>
                                        <option value="padel" <?php echo ($cancha['tipo'] ?? '') === 'padel' ? 'selected' : ''; ?>>
                                            🏐 Pádel
                                        </option>
                                        <option value="voleibol" <?php echo ($cancha['tipo'] ?? '') === 'voleibol' ? 'selected' : ''; ?>>
                                            🏐 Voleibol
                                        </option>
                                        <option value="basquetbol" <?php echo ($cancha['tipo'] ?? '') === 'basquetbol' ? 'selected' : ''; ?>>
                                            🏀 Basquetbol
                                        </option>
                                        <option value="piscina" <?php echo ($cancha['tipo'] ?? '') === 'piscina' ? 'selected' : ''; ?>>
                                            🏊 Piscina
                                        </option>
                                        <option value="gimnasio" <?php echo ($cancha['tipo'] ?? '') === 'gimnasio' ? 'selected' : ''; ?>>
                                            💪 Gimnasio
                                        </option>
                                        <option value="otro" <?php echo ($cancha['tipo'] ?? '') === 'otro' ? 'selected' : ''; ?>>
                                            ➕ Otro
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label for="instalacion_id" class="form-label">Instalación <span class="text-danger">*</span></label>
                                    <select id="instalacion_id" name="instalacion_id" class="form-select" required>
                                        <option value="">Selecciona la instalación...</option>
                                        <?php foreach ($instalaciones as $inst): ?>
                                            <option value="<?php echo $inst['instalacion_id']; ?>" 
                                                    <?php echo ($cancha['instalacion_id'] ?? '') == $inst['instalacion_id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($inst['nombre']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label for="descripcion" class="form-label">Descripción</label>
                                    <textarea id="descripcion" name="descripcion" class="form-control" rows="3"
                                              maxlength="500" placeholder="Información adicional sobre la cancha..."><?php echo htmlspecialchars($cancha['descripcion'] ?? ''); ?></textarea>
                                    <small class="text-muted d-block mt-1">Máximo 500 caracteres</small>
                                </div>
                            </div>
                        </div>

                        <!-- Sección 2: Especificaciones Técnicas -->
                        <div class="border-bottom pb-4 mb-4">
                            <h6 class="fw-bold text-primary mb-3">
                                <i class="fas fa-ruler"></i> Especificaciones
                            </h6>

                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="capacidad_maxima" class="form-label">Capacidad Máxima <span class="text-danger">*</span></label>
                                    <input type="number" id="capacidad_maxima" name="capacidad_maxima" 
                                           class="form-control" required min="1" max="1000"
                                           value="<?php echo htmlspecialchars($cancha['capacidad_maxima'] ?? ''); ?>"
                                           placeholder="Número de personas">
                                    <small class="text-muted d-block mt-1">Límite de personas simultáneas</small>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="largo" class="form-label">Largo (metros)</label>
                                    <input type="number" id="largo" name="largo" 
                                           class="form-control" min="0" step="0.1"
                                           value="<?php echo htmlspecialchars($cancha['largo'] ?? ''); ?>"
                                           placeholder="0.00">
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="ancho" class="form-label">Ancho (metros)</label>
                                    <input type="number" id="ancho" name="ancho" 
                                           class="form-control" min="0" step="0.1"
                                           value="<?php echo htmlspecialchars($cancha['ancho'] ?? ''); ?>"
                                           placeholder="0.00">
                                </div>
                            </div>
                        </div>

                        <!-- Sección 3: Estado (Solo para editar) -->
                        <?php if ($modo === 'editar'): ?>
                            <div class="border-bottom pb-4 mb-4">
                                <h6 class="fw-bold text-primary mb-3">
                                    <i class="fas fa-toggle-on"></i> Estado
                                </h6>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" id="estadoActivo" 
                                                   name="estado" value="ACTIVO" 
                                                   <?php echo ($cancha['estado'] ?? '') === 'ACTIVO' ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="estadoActivo">
                                                <span class="badge bg-success">Activo</span>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" id="estadoInactivo" 
                                                   name="estado" value="INACTIVO" 
                                                   <?php echo ($cancha['estado'] ?? '') === 'INACTIVO' ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="estadoInactivo">
                                                <span class="badge bg-warning">Inactivo</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Botones de Acción -->
                        <div class="d-flex justify-content-between">
                            <a href="<?php echo $urlListado; ?>" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Cancelar
                            </a>

                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> 
                                <?php echo $modo === 'editar' ? 'Actualizar' : 'Guardar'; ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('formCancha').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const form = this;
    const capacidad = document.getElementById('capacidad_maxima').value;
    const urlListado = '<?php echo $urlListado; ?>';
    
    // Validación en cliente
    if (parseInt(capacidad) < 1) {
        DigiAlert.warning('La capacidad debe ser mayor a 0');
        return false;
    }
    
    // Mostrar loading
    DigiAlert.loading('Guardando cancha...');
    
    // Enviar via AJAX
    fetch(form.action, {
        method: 'POST',
        body: new FormData(form)
    })
    .then(response => response.json())
    .then(data => {
        Swal.close();
        
        if (data.success) {
            DigiAlert.successAction(data.message, function() {
                if (data.data && data.data.redirect) {
                    window.location.href = data.data.redirect;
                } else {
                    window.location.href = urlListado;
                }
            });
        } else {
            DigiAlert.errorDetail('Error', data.message || 'Error al guardar la cancha');
        }
    })
    .catch(error => {
        Swal.close();
        DigiAlert.errorDetail('Error de conexión', 'No se pudo conectar con el servidor');
        console.error('Error:', error);
    });
    
    return false;
});
</script>
