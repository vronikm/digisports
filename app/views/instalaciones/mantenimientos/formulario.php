<?php
/**
 * Formulario de Crear/Editar Mantenimiento
 * @var array $mantenimiento (si es editar)
 * @var array $canchas
 * @var array $usuarios
 * @var string $csrf_token
 * @var string $modo ('editar' o vacio para crear)
 */
$modo = $modo ?? '';
$tituloSeccion = $modo === 'editar' ? 'Editar Mantenimiento' : 'Programar Mantenimiento';
$accion = $modo === 'editar' ? 'actualizar' : 'guardar';
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light border-bottom">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-wrench text-primary"></i> <?php echo $tituloSeccion; ?>
                    </h5>
                </div>

                <div class="card-body">
                    <form id="formMantenimiento" method="post" 
                          action="<?php echo url('instalaciones', 'mantenimiento', $accion); ?>">
                        
                        <!-- CSRF Token -->
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                        
                        <?php if ($modo === 'editar'): ?>
                            <input type="hidden" name="mantenimiento_id" value="<?php echo $mantenimiento['mantenimiento_id']; ?>">
                        <?php endif; ?>

                        <!-- Sección 1: Información Básica -->
                        <div class="border-bottom pb-4 mb-4">
                            <h6 class="fw-bold text-primary mb-3">
                                <i class="fas fa-info-circle"></i> Información Básica
                            </h6>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="cancha_id" class="form-label">Cancha <span class="text-danger">*</span></label>
                                    <select id="cancha_id" name="cancha_id" class="form-select" required <?php echo $modo === 'editar' ? 'disabled' : ''; ?>>
                                        <option value="">Selecciona una cancha...</option>
                                        <?php foreach ($canchas as $cancha): ?>
                                            <option value="<?php echo $cancha['can_cancha_id']; ?>" 
                                                    <?php echo ($mantenimiento['cancha_id'] ?? '') == $cancha['can_cancha_id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($cancha['can_nombre']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <?php if ($modo === 'editar'): ?>
                                        <input type="hidden" name="cancha_id" value="<?php echo $mantenimiento['cancha_id']; ?>">
                                    <?php endif; ?>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="tipo" class="form-label">Tipo de Mantenimiento <span class="text-danger">*</span></label>
                                    <select id="tipo" name="tipo" class="form-select" required>
                                        <option value="">Selecciona...</option>
                                        <option value="preventivo" <?php echo ($mantenimiento['tipo'] ?? '') === 'preventivo' ? 'selected' : ''; ?>>
                                            🔍 Preventivo
                                        </option>
                                        <option value="correctivo" <?php echo ($mantenimiento['tipo'] ?? '') === 'correctivo' ? 'selected' : ''; ?>>
                                            🔧 Correctivo
                                        </option>
                                        <option value="limpieza" <?php echo ($mantenimiento['tipo'] ?? '') === 'limpieza' ? 'selected' : ''; ?>>
                                            🧹 Limpieza
                                        </option>
                                        <option value="reparacion" <?php echo ($mantenimiento['tipo'] ?? '') === 'reparacion' ? 'selected' : ''; ?>>
                                            🛠️ Reparación
                                        </option>
                                        <option value="inspeccion" <?php echo ($mantenimiento['tipo'] ?? '') === 'inspeccion' ? 'selected' : ''; ?>>
                                            👁️ Inspección
                                        </option>
                                        <option value="otra" <?php echo ($mantenimiento['tipo'] ?? '') === 'otra' ? 'selected' : ''; ?>>
                                            ➕ Otra
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label for="descripcion" class="form-label">Descripción <span class="text-danger">*</span></label>
                                    <textarea id="descripcion" name="descripcion" class="form-control" rows="3"
                                              required minlength="5" maxlength="500" 
                                              placeholder="Describe en detalle el mantenimiento a realizar..."><?php echo htmlspecialchars($mantenimiento['descripcion'] ?? ''); ?></textarea>
                                    <small class="text-muted d-block mt-1">Mínimo 5, máximo 500 caracteres</small>
                                </div>
                            </div>
                        </div>

                        <!-- Sección 2: Fechas y Horarios -->
                        <div class="border-bottom pb-4 mb-4">
                            <h6 class="fw-bold text-primary mb-3">
                                <i class="fas fa-calendar"></i> Fechas y Horarios
                            </h6>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="fecha_inicio" class="form-label">Fecha de Inicio <span class="text-danger">*</span></label>
                                    <input type="datetime-local" id="fecha_inicio" name="fecha_inicio" 
                                           class="form-control" required
                                           value="<?php echo isset($mantenimiento['fecha_inicio']) ? substr($mantenimiento['fecha_inicio'], 0, 16) : ''; ?>">
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="fecha_fin" class="form-label">Fecha de Finalización <span class="text-danger">*</span></label>
                                    <input type="datetime-local" id="fecha_fin" name="fecha_fin" 
                                           class="form-control" required
                                           value="<?php echo isset($mantenimiento['fecha_fin']) ? substr($mantenimiento['fecha_fin'], 0, 16) : ''; ?>">
                                </div>
                            </div>
                        </div>

                        <!-- Sección 3: Responsable y Recurrencia -->
                        <div class="border-bottom pb-4 mb-4">
                            <h6 class="fw-bold text-primary mb-3">
                                <i class="fas fa-user"></i> Responsable y Recurrencia
                            </h6>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="responsable_id" class="form-label">Responsable (Técnico/Admin)</label>
                                    <select id="responsable_id" name="responsable_id" class="form-select">
                                        <option value="">Sin asignar</option>
                                        <?php foreach ($usuarios as $usuario): ?>
                                            <option value="<?php echo $usuario['usuario_id']; ?>" 
                                                    <?php echo ($mantenimiento['responsable_id'] ?? '') == $usuario['usuario_id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($usuario['nombre']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="recurrir" class="form-label">¿Mantenimiento Recurrente?</label>
                                    <select id="recurrir" name="recurrir" class="form-select" onchange="toggleRecurrencia()">
                                        <option value="NO" <?php echo ($mantenimiento['recurrir'] ?? 'NO') === 'NO' ? 'selected' : ''; ?>>No</option>
                                        <option value="SI" <?php echo ($mantenimiento['recurrir'] ?? '') === 'SI' ? 'selected' : ''; ?>>Sí</option>
                                    </select>
                                </div>
                            </div>

                            <div id="divRecurrencia" class="row" style="display: <?php echo ($mantenimiento['recurrir'] ?? 'NO') === 'SI' ? 'flex' : 'none'; ?>;">
                                <div class="col-md-12 mb-3">
                                    <label for="cadencia_recurrencia" class="form-label">Cada cuánto tiempo (días)</label>
                                    <input type="number" id="cadencia_recurrencia" name="cadencia_recurrencia" 
                                           class="form-control" min="1" max="365"
                                           value="<?php echo htmlspecialchars($mantenimiento['cadencia_recurrencia'] ?? '7'); ?>">
                                </div>
                            </div>
                        </div>

                        <!-- Sección 4: Estado (Solo para editar) -->
                        <?php if ($modo === 'editar'): ?>
                            <div class="border-bottom pb-4 mb-4">
                                <h6 class="fw-bold text-primary mb-3">
                                    <i class="fas fa-toggle-on"></i> Estado
                                </h6>

                                <div class="row">
                                    <div class="col-md-12">
                                        <select id="estado" name="estado" class="form-select" required>
                                            <option value="PROGRAMADO" <?php echo ($mantenimiento['estado'] ?? '') === 'PROGRAMADO' ? 'selected' : ''; ?>>
                                                Programado
                                            </option>
                                            <option value="EN_PROGRESO" <?php echo ($mantenimiento['estado'] ?? '') === 'EN_PROGRESO' ? 'selected' : ''; ?>>
                                                En Progreso
                                            </option>
                                            <option value="COMPLETADO" <?php echo ($mantenimiento['estado'] ?? '') === 'COMPLETADO' ? 'selected' : ''; ?>>
                                                Completado
                                            </option>
                                            <option value="CANCELADO" <?php echo ($mantenimiento['estado'] ?? '') === 'CANCELADO' ? 'selected' : ''; ?>>
                                                Cancelado
                                            </option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Sección 5: Notas -->
                        <div class="mb-4">
                            <label for="notas" class="form-label">Notas Adicionales</label>
                            <textarea id="notas" name="notas" class="form-control" rows="3" maxlength="1000"
                                      placeholder="Información adicional sobre el mantenimiento..."><?php echo htmlspecialchars($mantenimiento['notas'] ?? ''); ?></textarea>
                            <small class="text-muted d-block mt-1">Máximo 1000 caracteres</small>
                        </div>

                        <!-- Botones de Acción -->
                        <div class="d-flex justify-content-between">
                            <a href="<?php echo url('instalaciones', 'mantenimiento', 'index'); ?>" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Cancelar
                            </a>

                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> 
                                <?php echo $modo === 'editar' ? 'Actualizar' : 'Programar'; ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function toggleRecurrencia() {
    const recurrir = document.getElementById('recurrir').value;
    const divRecurrencia = document.getElementById('divRecurrencia');
    divRecurrencia.style.display = recurrir === 'SI' ? 'flex' : 'none';
}

document.getElementById('formMantenimiento').addEventListener('submit', function(e) {
    const fechaInicio = new Date(document.getElementById('fecha_inicio').value);
    const fechaFin = new Date(document.getElementById('fecha_fin').value);
    
    if (fechaFin <= fechaInicio) {
        e.preventDefault();
        alert('La fecha de fin debe ser posterior a la fecha de inicio');
        return false;
    }
    
    return true;
});
</script>
