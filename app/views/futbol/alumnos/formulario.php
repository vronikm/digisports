<?php
/**
 * DigiSports Fútbol - Formulario Crear/Editar Alumno
 * Módulo autosuficiente: incluye datos del alumno, representante y ficha deportiva de fútbol
 * 
 * @vars $alumno, $ficha, $categorias, $sedes, $sede_activa, $csrf_token, $modulo_actual
 */
$alumno       = $alumno ?? [];
$ficha        = $ficha ?? [];
$categorias   = $categorias ?? [];
$sedes        = $sedes ?? [];
$sedeActiva   = $sede_activa ?? null;
$editando     = !empty($alumno);
$moduloColor  = $modulo_actual['color'] ?? '#22C55E';
$fotoAlumno   = $foto_alumno ?? null;  // array de core_archivos o null
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">
                    <i class="fas fa-<?= $editando ? 'edit' : 'user-plus' ?> mr-2" style="color:<?= $moduloColor ?>"></i>
                    <?= $editando ? 'Editar Alumno' : 'Nuevo Alumno' ?>
                </h1>
            </div>
            <div class="col-sm-6">
                <div class="float-sm-right">
                    <a href="<?= url('futbol', 'alumno', 'index') ?>" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-arrow-left mr-1"></i>Volver al Listado
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <form id="formAlumnoFutbol">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
            <?php if ($editando): ?>
            <input type="hidden" name="id" value="<?= $alumno['alu_alumno_id'] ?>">
            <?php endif; ?>

            <div class="row">
                <!-- ========== COLUMNA PRINCIPAL ========== -->
                <div class="col-lg-8">
                    <!-- Datos Personales -->
                    <div class="card card-outline" style="border-top-color:<?= $moduloColor ?>">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-user mr-2"></i>Datos Personales</h3>
                        </div>
                        <div class="card-body">
                            <!-- Fila 1: Nombre completo -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Nombres <span class="text-danger">*</span></label>
                                        <input type="text" name="nombres" class="form-control" required maxlength="150"
                                               value="<?= htmlspecialchars($alumno['alu_nombres'] ?? '') ?>"
                                               placeholder="Nombres del alumno">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Apellidos <span class="text-danger">*</span></label>
                                        <input type="text" name="apellidos" class="form-control" required maxlength="150"
                                               value="<?= htmlspecialchars($alumno['alu_apellidos'] ?? '') ?>"
                                               placeholder="Apellidos del alumno">
                                    </div>
                                </div>
                            </div>
                            <!-- Fila 2: Identificación, Nacimiento, Género, Estado -->
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Identificación</label>
                                        <div class="row no-gutters">
                                            <div class="col-auto">
                                                <select name="tipo_identificacion" id="tipo_identificacion" class="form-control"
                                                        style="border-radius:.25rem 0 0 .25rem; border-right:none; min-width:110px;">
                                                    <option value="CED" <?= ($alumno['alu_tipo_identificacion'] ?? 'CED') === 'CED' ? 'selected' : '' ?>>Cédula/RUC</option>
                                                    <option value="PAS" <?= ($alumno['alu_tipo_identificacion'] ?? '') === 'PAS' ? 'selected' : '' ?>>Pasaporte</option>
                                                </select>
                                            </div>
                                            <div class="col">
                                                <input type="text" name="identificacion" id="identificacion" class="form-control"
                                                       style="border-radius:0 .25rem .25rem 0;" maxlength="20"
                                                       value="<?= htmlspecialchars($alumno['alu_identificacion'] ?? '') ?>"
                                                       placeholder="Ej: 0912345678">
                                            </div>
                                        </div>
                                        <small id="id_feedback" class="form-text"></small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Fecha de Nacimiento <span class="text-danger">*</span></label>
                                        <input type="date" name="fecha_nacimiento" id="fecha_nacimiento" class="form-control" required
                                               value="<?= htmlspecialchars($alumno['alu_fecha_nacimiento'] ?? '') ?>">
                                        <small id="edad_info" class="form-text text-info"></small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Género</label>
                                        <select name="genero" class="form-control">
                                            <option value="">— Seleccionar —</option>
                                            <option value="M" <?= ($alumno['alu_genero'] ?? '') === 'M' ? 'selected' : '' ?>>Masculino</option>
                                            <option value="F" <?= ($alumno['alu_genero'] ?? '') === 'F' ? 'selected' : '' ?>>Femenino</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>Estado</label>
                                        <select name="estado" class="form-control">
                                            <option value="ACTIVO" <?= ($alumno['alu_estado'] ?? 'ACTIVO') === 'ACTIVO' ? 'selected' : '' ?>>Activo</option>
                                            <option value="INACTIVO" <?= ($alumno['alu_estado'] ?? '') === 'INACTIVO' ? 'selected' : '' ?>>Inactivo</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <!-- Panel: resultado búsqueda de alumno existente (cross-tenant) -->
                            <?php if (!$editando): ?>
                            <div id="panelAlumnoEncontrado" style="display:none;">
                                <div id="alertAlumnoEncontrado" class="alert border p-3 mb-2">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div id="alumnoEncontradoInfo" class="flex-grow-1"></div>
                                        <button type="button" class="btn btn-xs btn-outline-secondary ml-2" id="btnCerrarAlumnoFound">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                    <div class="mt-2" id="alumnoEncontradoAcciones"></div>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Ficha Deportiva de Fútbol -->
                    <div class="card card-outline" style="border-top-color:<?= $moduloColor ?>">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-futbol mr-2" style="color:<?= $moduloColor ?>"></i>Ficha Deportiva de Fútbol</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Categoría</label>
                                        <select name="categoria_id" id="categoria_id" class="form-control">
                                            <option value="">— Seleccionar —</option>
                                            <?php foreach ($categorias as $cat): ?>
                                            <option value="<?= $cat['fct_categoria_id'] ?>"
                                                    data-min="<?= $cat['fct_edad_min'] ?? '' ?>"
                                                    data-max="<?= $cat['fct_edad_max'] ?? '' ?>"
                                                    <?= ($ficha['ffa_categoria_id'] ?? '') == $cat['fct_categoria_id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($cat['fct_nombre']) ?>
                                                (<?= $cat['fct_edad_min'] ?? '?' ?>-<?= $cat['fct_edad_max'] ?? '?' ?> años)
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <small id="categoria_sugerida" class="form-text text-success"></small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Posición Preferida</label>
                                        <select name="posicion_preferida" class="form-control">
                                            <option value="">— Seleccionar —</option>
                                            <?php
                                            $posiciones = ['Portero','Defensa Central','Lateral Derecho','Lateral Izquierdo','Mediocampista','Volante','Extremo Derecho','Extremo Izquierdo','Delantero Centro','Segundo Delantero'];
                                            foreach ($posiciones as $pos): ?>
                                            <option value="<?= $pos ?>" <?= ($ficha['ffa_posicion_preferida'] ?? '') === $pos ? 'selected' : '' ?>><?= $pos ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Pie Dominante</label>
                                        <select name="pie_dominante" class="form-control">
                                            <option value="">— Seleccionar —</option>
                                            <option value="DERECHO" <?= ($ficha['ffa_pie_dominante'] ?? '') === 'DERECHO' ? 'selected' : '' ?>>Derecho</option>
                                            <option value="IZQUIERDO" <?= ($ficha['ffa_pie_dominante'] ?? '') === 'IZQUIERDO' ? 'selected' : '' ?>>Izquierdo</option>
                                            <option value="AMBIDIESTRO" <?= ($ficha['ffa_pie_dominante'] ?? '') === 'AMBIDIESTRO' ? 'selected' : '' ?>>Ambidiestro</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Objetivo</label>
                                        <select name="objetivo" class="form-control">
                                            <option value="RECREATIVO" <?= ($ficha['ffa_objetivo'] ?? 'RECREATIVO') === 'RECREATIVO' ? 'selected' : '' ?>>Recreativo</option>
                                            <option value="FORMATIVO" <?= ($ficha['ffa_objetivo'] ?? '') === 'FORMATIVO' ? 'selected' : '' ?>>Formativo</option>
                                            <option value="COMPETITIVO" <?= ($ficha['ffa_objetivo'] ?? '') === 'COMPETITIVO' ? 'selected' : '' ?>>Competitivo</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Club Anterior</label>
                                        <input type="text" name="club_anterior" class="form-control" maxlength="100"
                                               value="<?= htmlspecialchars($ficha['ffa_club_anterior'] ?? '') ?>" placeholder="Nombre del club...">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Nro. Camiseta</label>
                                        <input type="number" name="numero_camiseta" class="form-control" min="1" max="99"
                                               value="<?= htmlspecialchars($ficha['ffa_numero_camiseta'] ?? '') ?>" placeholder="#">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Talla Camiseta</label>
                                        <select name="talla_camiseta" class="form-control">
                                            <option value="">—</option>
                                            <?php foreach (['XS','S','M','L','XL','XXL'] as $t): ?>
                                            <option value="<?= $t ?>" <?= ($ficha['ffa_talla_camiseta'] ?? '') === $t ? 'selected' : '' ?>><?= $t ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Experiencia Previa</label>
                                        <input type="text" name="experiencia_previa" class="form-control" maxlength="300"
                                               value="<?= htmlspecialchars($ficha['ffa_experiencia_previa'] ?? '') ?>" placeholder="Describa brevemente...">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Autorización Médica</label>
                                        <div class="custom-control custom-switch mt-2">
                                            <input type="checkbox" class="custom-control-input" id="chkAutMedica" name="autorizacion_medica" value="1"
                                                   <?= ($ficha['ffa_autorizacion_medica'] ?? 0) ? 'checked' : '' ?>>
                                            <label class="custom-control-label" for="chkAutMedica">Sí, autorizado</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Campos de Ficha Adicionales -->
                    <?php
                    $campos_ficha = $campos_ficha ?? [];
                    $datosCustom  = [];
                    if (!empty($ficha['ffa_datos_custom'])) {
                        $datosCustom = json_decode($ficha['ffa_datos_custom'], true) ?: [];
                    }
                    ?>
                    <?php if (!empty($campos_ficha)): ?>
                    <div class="card card-outline" style="border-top-color:<?= $moduloColor ?>">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-clipboard-list mr-2" style="color:<?= $moduloColor ?>"></i>Campos Adicionales de Ficha</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                            <?php foreach ($campos_ficha as $campo): ?>
                                <?php
                                $clave      = $campo['fcf_clave'];
                                $tipo       = $campo['fcf_tipo'] ?? 'TEXT';
                                $requerido  = $campo['fcf_requerido'] ? 'required' : '';
                                $placeholder = htmlspecialchars($campo['fcf_placeholder'] ?? '');
                                $fieldValue  = $datosCustom[$clave] ?? '';
                                $fieldName   = 'campo_custom[' . htmlspecialchars($clave) . ']';
                                ?>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label><?= htmlspecialchars($campo['fcf_etiqueta']) ?><?= $campo['fcf_requerido'] ? ' <span class="text-danger">*</span>' : '' ?></label>
                                        <?php if ($tipo === 'SELECT' && !empty($campo['fcf_opciones'])): ?>
                                            <?php $opciones = json_decode($campo['fcf_opciones'], true) ?: []; ?>
                                            <select name="<?= $fieldName ?>" class="form-control" <?= $requerido ?>>
                                                <option value="">— Seleccionar —</option>
                                                <?php foreach ($opciones as $opt): ?>
                                                <option value="<?= htmlspecialchars($opt) ?>" <?= $fieldValue === $opt ? 'selected' : '' ?>><?= htmlspecialchars($opt) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        <?php elseif ($tipo === 'TEXTAREA'): ?>
                                            <textarea name="<?= $fieldName ?>" class="form-control" rows="3" placeholder="<?= $placeholder ?>" <?= $requerido ?>><?= htmlspecialchars($fieldValue) ?></textarea>
                                        <?php elseif ($tipo === 'CHECKBOX'): ?>
                                            <div class="custom-control custom-switch mt-2">
                                                <input type="checkbox" class="custom-control-input" id="campo_<?= htmlspecialchars($clave) ?>"
                                                       name="<?= $fieldName ?>" value="1" <?= !empty($fieldValue) ? 'checked' : '' ?>>
                                                <label class="custom-control-label" for="campo_<?= htmlspecialchars($clave) ?>">Sí</label>
                                            </div>
                                        <?php elseif ($tipo === 'DATE'): ?>
                                            <input type="date" name="<?= $fieldName ?>" class="form-control" value="<?= htmlspecialchars($fieldValue) ?>" <?= $requerido ?>>
                                        <?php elseif ($tipo === 'NUMBER'): ?>
                                            <input type="number" name="<?= $fieldName ?>" class="form-control" value="<?= htmlspecialchars($fieldValue) ?>" placeholder="<?= $placeholder ?>" <?= $requerido ?>>
                                        <?php else: ?>
                                            <input type="text" name="<?= $fieldName ?>" class="form-control" value="<?= htmlspecialchars($fieldValue) ?>" placeholder="<?= $placeholder ?>" maxlength="300" <?= $requerido ?>>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Observaciones Médicas -->
                    <div class="card card-outline card-danger">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-heartbeat mr-2 text-danger"></i>Información Médica</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Tipo de Sangre</label>
                                        <select name="tipo_sangre" class="form-control">
                                            <option value="">—</option>
                                            <?php foreach (['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $ts): ?>
                                            <option value="<?= $ts ?>" <?= ($alumno['alu_tipo_sangre'] ?? '') === $ts ? 'selected' : '' ?>><?= $ts ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Alergias</label>
                                        <input type="text" name="alergias" class="form-control" maxlength="300"
                                               value="<?= htmlspecialchars($alumno['alu_alergias'] ?? '') ?>" placeholder="Ninguna conocida">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Condiciones Médicas</label>
                                        <input type="text" name="condiciones_medicas" class="form-control" maxlength="300"
                                               value="<?= htmlspecialchars($alumno['alu_condiciones_medicas'] ?? '') ?>" placeholder="Asma, diabetes, etc.">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Medicamentos en uso</label>
                                        <input type="text" name="medicamentos" class="form-control" maxlength="300"
                                               value="<?= htmlspecialchars($alumno['alu_medicamentos'] ?? '') ?>" placeholder="Nombre del medicamento...">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Contacto de Emergencia</label>
                                        <div class="input-group">
                                            <input type="text" name="contacto_emergencia" class="form-control" maxlength="200"
                                                   value="<?= htmlspecialchars($alumno['alu_contacto_emergencia'] ?? '') ?>" placeholder="Nombre">
                                            <input type="text" name="telefono_emergencia" class="form-control" maxlength="20"
                                                   value="<?= htmlspecialchars($alumno['alu_telefono_emergencia'] ?? '') ?>" placeholder="Teléfono">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group mb-0">
                                <label>Observaciones Médicas Adicionales</label>
                                <textarea name="observaciones_medicas" class="form-control" rows="2" maxlength="500"
                                          placeholder="Cualquier dato médico relevante..."><?= htmlspecialchars($alumno['alu_observaciones_medicas'] ?? '') ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ========== COLUMNA LATERAL ========== -->
                <div class="col-lg-4">

                    <!-- Foto del Alumno -->
                    <div class="card card-outline" style="border-top-color:<?= $moduloColor ?>">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-camera mr-2"></i>Foto del Alumno</h3>
                        </div>
                        <div class="card-body text-center">
                            <!-- Preview de la foto -->
                            <div id="fotoPreviewContainer" style="margin-bottom:12px;">
                                <?php if ($fotoAlumno): ?>
                                <img id="fotoPreview"
                                     src="<?= \Config::baseUrl('archivo.php?id=' . $fotoAlumno['arc_id']) ?>"
                                     alt="Foto del alumno"
                                     class="img-circle elevation-2"
                                     style="width:120px;height:120px;object-fit:cover;">
                                <?php else: ?>
                                <div id="fotoPlaceholder" class="bg-secondary"
                                     style="display:flex;align-items:center;justify-content:center;width:120px;height:120px;border-radius:50%;margin:0 auto;">
                                    <i class="fas fa-user fa-3x text-white"></i>
                                </div>
                                <img id="fotoPreview" src="" alt="" class="img-circle elevation-2"
                                     style="width:120px;height:120px;object-fit:cover;display:none;">
                                <?php endif; ?>
                            </div>

                            <!-- Input oculto -->
                            <input type="file" id="inputFoto" accept="image/jpeg,image/png,image/webp"
                                   style="display:none;">

                            <div class="d-flex justify-content-center" style="gap:6px;">
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="btnSeleccionarFoto">
                                    <i class="fas fa-image mr-1"></i>
                                    <?= $fotoAlumno ? 'Cambiar' : 'Seleccionar' ?>
                                </button>
                                <?php if ($fotoAlumno): ?>
                                <button type="button" class="btn btn-sm btn-outline-danger" id="btnEliminarFoto"
                                        data-arc-id="<?= $fotoAlumno['arc_id'] ?>">
                                    <i class="fas fa-trash"></i>
                                </button>
                                <?php endif; ?>
                            </div>

                            <!-- Acciones tras selección -->
                            <div id="fotoAcciones" style="display:none; margin-top:10px;">
                                <?php if ($editando): ?>
                                <button type="button" class="btn btn-sm btn-success mr-1" id="btnSubirFoto">
                                    <i class="fas fa-upload mr-1"></i>Subir Foto
                                </button>
                                <?php else: ?>
                                <small class="text-info d-block mb-2">
                                    <i class="fas fa-info-circle mr-1"></i>Se guardará al registrar al alumno
                                </small>
                                <?php endif; ?>
                                <button type="button" class="btn btn-sm btn-secondary" id="btnCancelarFoto">
                                    Cancelar
                                </button>
                            </div>

                            <div id="fotoFeedback" class="mt-2 small"></div>
                        </div>
                    </div>

                    <!-- Sede -->
                    <?php if (!empty($sedes)): ?>
                    <div class="card card-outline" style="border-top-color:<?= $moduloColor ?>">
                        <div class="card-header"><h3 class="card-title"><i class="fas fa-building mr-2"></i>Sede</h3></div>
                        <div class="card-body">
                            <div class="form-group mb-0">
                                <select name="sede_id" class="form-control">
                                    <option value="">— Sin sede —</option>
                                    <?php foreach ($sedes as $s): ?>
                                    <option value="<?= $s['sed_sede_id'] ?>"
                                            <?= ($alumno['alu_sede_id'] ?? $sedeActiva) == $s['sed_sede_id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($s['sed_nombre']) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Representante / Padre de Familia -->
                    <?php
                    // Datos del representante precargados (si estamos editando)
                    $rep = $representante ?? [];
                    $hermanos = $hermanos ?? [];
                    ?>
                    <div class="card card-outline card-warning">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-user-friends mr-2"></i>Representante / Padre de Familia</h3>
                        </div>
                        <div class="card-body">
                            <!-- Buscador por identificación -->
                            <div class="form-group">
                                <label>Identificación del Representante <span class="text-danger">*</span></label>
                                <div class="row no-gutters">
                                    <div class="col-auto">
                                        <select id="tipo_id_rep" class="form-control"
                                                style="border-radius:.25rem 0 0 .25rem; border-right:none; min-width:115px;"
                                                title="Tipo de documento">
                                            <option value="CED" <?= ($rep['cli_tipo_identificacion'] ?? 'CED') !== 'PAS' ? 'selected' : '' ?>>Cédula/RUC</option>
                                            <option value="PAS" <?= ($rep['cli_tipo_identificacion'] ?? '') === 'PAS' ? 'selected' : '' ?>>Pasaporte</option>
                                        </select>
                                    </div>
                                    <div class="col">
                                        <input type="text" id="rep_cedula" class="form-control" maxlength="20"
                                               style="border-radius:0; border-right:none;"
                                               value="<?= htmlspecialchars($rep['cli_identificacion'] ?? '') ?>"
                                               placeholder="Ingrese cédula o RUC">
                                    </div>
                                    <div class="col-auto">
                                        <button type="button" class="btn btn-warning" id="btnBuscarRep"
                                                style="border-radius:0 .25rem .25rem 0;">
                                            <i class="fas fa-search mr-1"></i>Buscar
                                        </button>
                                    </div>
                                </div>
                                <small id="rep_feedback" class="form-text"></small>
                            </div>

                            <!-- Campo oculto con el ID del cliente seleccionado -->
                            <input type="hidden" name="representante_id" id="representante_id"
                                   value="<?= htmlspecialchars($rep['cli_cliente_id'] ?? '') ?>">

                            <!-- Panel de datos del representante (visible cuando se encuentra o se crea) -->
                            <div id="panelRepresentante" style="<?= !empty($rep) ? '' : 'display:none;' ?>"
                                 data-rep-nombres="<?= htmlspecialchars($rep['cli_nombres'] ?? '') ?>"
                                 data-rep-apellidos="<?= htmlspecialchars($rep['cli_apellidos'] ?? '') ?>">
                                <div class="alert alert-light border p-3 mb-3" id="repDatosCard">
                                    <!-- Vista de solo lectura -->
                                    <div id="repVistaLectura">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <h6 class="mb-0 text-dark">
                                                <i class="fas fa-user-check mr-1 text-success"></i>
                                                <span id="repNombreDisplay"><?= htmlspecialchars(trim(($rep['cli_nombres'] ?? '') . ' ' . ($rep['cli_apellidos'] ?? ''))) ?></span>
                                            </h6>
                                            <div class="btn-group btn-group-xs">
                                                <button type="button" class="btn btn-xs btn-outline-primary" id="btnEditarRep" title="Editar datos del representante">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button type="button" class="btn btn-xs btn-outline-secondary" id="btnLimpiarRep" title="Cambiar representante">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="row text-sm">
                                            <div class="col-6">
                                                <i class="fas fa-id-card mr-1 text-muted"></i>
                                                <span id="repCedulaDisplay"><?= htmlspecialchars($rep['cli_identificacion'] ?? '') ?></span>
                                            </div>
                                            <div class="col-6">
                                                <i class="fas fa-phone mr-1 text-muted"></i>
                                                <span id="repTelefonoDisplay"><?= htmlspecialchars($rep['cli_telefono'] ?? $rep['cli_celular'] ?? '') ?></span>
                                            </div>
                                        </div>
                                        <div class="row text-sm mt-1">
                                            <div class="col-6">
                                                <i class="fas fa-envelope mr-1 text-muted"></i>
                                                <span id="repEmailDisplay"><?= htmlspecialchars($rep['cli_email'] ?? '') ?></span>
                                            </div>
                                            <div class="col-6">
                                                <i class="fas fa-map-marker-alt mr-1 text-muted"></i>
                                                <span id="repDireccionDisplay"><?= htmlspecialchars($rep['cli_direccion'] ?? '') ?></span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Formulario de edición (oculto por defecto) -->
                                    <div id="repVistaEdicion" style="display:none;">
                                        <h6 class="mb-2 text-primary"><i class="fas fa-edit mr-1"></i>Editar datos del representante</h6>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group mb-2">
                                                    <label class="text-sm mb-1">Nombres <span class="text-danger">*</span></label>
                                                    <input type="text" id="edit_rep_nombres" class="form-control form-control-sm" maxlength="150">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group mb-2">
                                                    <label class="text-sm mb-1">Apellidos <span class="text-danger">*</span></label>
                                                    <input type="text" id="edit_rep_apellidos" class="form-control form-control-sm" maxlength="150">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group mb-2">
                                                    <label class="text-sm mb-1">Teléfono <span class="text-danger">*</span></label>
                                                    <input type="text" id="edit_rep_telefono" class="form-control form-control-sm" maxlength="20">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group mb-2">
                                                    <label class="text-sm mb-1">Email</label>
                                                    <input type="email" id="edit_rep_email" class="form-control form-control-sm" maxlength="200">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group mb-2">
                                            <label class="text-sm mb-1">Dirección</label>
                                            <input type="text" id="edit_rep_direccion" class="form-control form-control-sm" maxlength="300">
                                        </div>
                                        <div class="d-flex justify-content-end">
                                            <button type="button" class="btn btn-xs btn-secondary mr-2" id="btnCancelarEditRep">
                                                <i class="fas fa-times mr-1"></i>Cancelar
                                            </button>
                                            <button type="button" class="btn btn-xs btn-primary" id="btnGuardarEditRep">
                                                <i class="fas fa-save mr-1"></i>Guardar cambios
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Parentesco -->
                                <div class="form-group">
                                    <label>Parentesco <span class="text-danger">*</span></label>
                                    <select name="parentesco" class="form-control" required>
                                        <option value="">— Seleccionar —</option>
                                        <?php
                                        $parentescos = ['PADRE'=>'Padre','MADRE'=>'Madre','TUTOR'=>'Tutor Legal','ABUELO'=>'Abuelo/a','TIO'=>'Tío/a','HERMANO'=>'Hermano/a','OTRO'=>'Otro'];
                                        $parentActual = $alumno['alu_parentesco'] ?? $alumno['alu_representante_parentesco'] ?? '';
                                        foreach ($parentescos as $k => $v): ?>
                                        <option value="<?= $k ?>" <?= $parentActual === $k ? 'selected' : '' ?>><?= $v ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <!-- Consentimiento de datos -->
                                <div class="form-group mb-0">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="chkConsentimiento" name="consentimiento_datos" value="1"
                                               <?= ($rep['cli_consentimiento_datos'] ?? 0) ? 'checked' : '' ?>>
                                        <label class="custom-control-label text-sm" for="chkConsentimiento">
                                            Autorizo el tratamiento de los datos personales de mi(s) representado(s) menores de edad, 
                                            conforme a la política de privacidad y protección de datos.
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Panel para crear nuevo representante (si no se encuentra) -->
                            <div id="panelNuevoRep" style="display:none;">
                                <div class="alert alert-info py-2 mb-3">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    No se encontró. Complete los datos para registrar un nuevo representante.
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Nombres <span class="text-danger">*</span></label>
                                            <input type="text" id="nuevo_rep_nombres" class="form-control" maxlength="150" placeholder="Nombres">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Apellidos <span class="text-danger">*</span></label>
                                            <input type="text" id="nuevo_rep_apellidos" class="form-control" maxlength="150" placeholder="Apellidos">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Teléfono <span class="text-danger">*</span></label>
                                            <input type="text" id="nuevo_rep_telefono" class="form-control" maxlength="20" placeholder="09XX XXX XXX">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Email</label>
                                            <input type="email" id="nuevo_rep_email" class="form-control" maxlength="200" placeholder="correo@ejemplo.com">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Dirección</label>
                                    <input type="text" id="nuevo_rep_direccion" class="form-control" maxlength="300" placeholder="Ciudad, sector, calle...">
                                </div>
                                <div class="form-group">
                                    <label>Parentesco <span class="text-danger">*</span></label>
                                    <select id="nuevo_rep_parentesco" class="form-control">
                                        <option value="">— Seleccionar —</option>
                                        <?php foreach ($parentescos as $k => $v): ?>
                                        <option value="<?= $k ?>"><?= $v ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="custom-control custom-checkbox mb-3">
                                    <input type="checkbox" class="custom-control-input" id="chkConsentimientoNuevo" value="1">
                                    <label class="custom-control-label text-sm" for="chkConsentimientoNuevo">
                                        Autorizo el tratamiento de los datos personales de mi(s) representado(s) menores de edad.
                                    </label>
                                </div>
                                <button type="button" class="btn btn-success btn-sm btn-block" id="btnCrearRep">
                                    <i class="fas fa-user-plus mr-1"></i>Registrar Representante
                                </button>
                            </div>

                            <!-- Alerta de hermanos inscritos -->
                            <div id="panelHermanos" style="<?= !empty($hermanos) ? '' : 'display:none;' ?>" class="mt-3">
                                <div class="alert alert-success py-2 mb-0">
                                    <i class="fas fa-users mr-1"></i>
                                    <strong>Hermanos inscritos:</strong>
                                    <span id="hermanosLista">
                                        <?php if (!empty($hermanos)): ?>
                                        <?php foreach ($hermanos as $h): ?>
                                        <span class="badge badge-light ml-1"><?= htmlspecialchars($h['alu_nombres'] . ' ' . $h['alu_apellidos']) ?></span>
                                        <?php endforeach; ?>
                                        <?php endif; ?>
                                    </span>
                                    <br><small class="text-muted"><i class="fas fa-tag mr-1"></i>Puede aplicar beca por hermanos</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Notas -->
                    <div class="card card-outline card-secondary">
                        <div class="card-header"><h3 class="card-title"><i class="fas fa-sticky-note mr-2"></i>Notas</h3></div>
                        <div class="card-body">
                            <textarea name="notas" class="form-control" rows="3" maxlength="500"
                                      placeholder="Observaciones generales..."><?= htmlspecialchars($alumno['alu_notas'] ?? '') ?></textarea>
                        </div>
                    </div>

                    <!-- Botón Guardar -->
                    <button type="submit" id="btnGuardar" class="btn btn-block btn-lg" style="background:<?= $moduloColor ?>;color:white;">
                        <i class="fas fa-save mr-2"></i><?= $editando ? 'Actualizar Alumno' : 'Registrar Alumno' ?>
                    </button>

                    <?php if ($editando): ?>
                    <a href="<?= url('futbol', 'alumno', 'ver') ?>&id=<?= $alumno['alu_alumno_id'] ?>" class="btn btn-outline-info btn-block mt-2">
                        <i class="fas fa-id-card mr-1"></i>Ver Ficha Completa
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>
</section>

<?php ob_start(); ?>
<script nonce="<?= cspNonce() ?>">
$(function() {
    // Calcular edad al cambiar fecha de nacimiento
    $('#fecha_nacimiento').on('change', calcularEdad).trigger('change');

    // Buscar alumno existente (solo en modo crear)
    <?php if (!$editando): ?>
    $('#identificacion').on('blur', function() {
        var val = $(this).val().trim();
        if (!val) return;
        var r = validarDoc(val, $('#tipo_identificacion').val());
        if (r.valido) { buscarAlumnoExistente(); }
    });
    $('#btnCerrarAlumnoFound').on('click', function() {
        $('#panelAlumnoEncontrado').slideUp();
    });
    <?php endif; ?>

    // Buscar representante al presionar Enter o botón
    $('#rep_cedula').on('keypress', function(e) {
        if (e.which === 13) { e.preventDefault(); buscarRepresentante(); }
    });
    $('#btnBuscarRep').on('click', buscarRepresentante);
    $('#btnLimpiarRep').on('click', limpiarRepresentante);
    $('#btnCrearRep').on('click', crearRepresentante);
    $('#btnEditarRep').on('click', abrirEdicionRepresentante);
    $('#btnCancelarEditRep').on('click', cerrarEdicionRepresentante);
    $('#btnGuardarEditRep').on('click', guardarEdicionRepresentante);

    // Submit del formulario
    $('#formAlumnoFutbol').on('submit', guardarAlumno);
});

function calcularEdad() {
    var val = $('#fecha_nacimiento').val();
    if (!val) { $('#edad_info').text(''); $('#categoria_sugerida').text(''); return; }
    
    var nacimiento = new Date(val);
    var hoy = new Date();
    var edad = hoy.getFullYear() - nacimiento.getFullYear();
    var m = hoy.getMonth() - nacimiento.getMonth();
    if (m < 0 || (m === 0 && hoy.getDate() < nacimiento.getDate())) edad--;
    
    if (edad < 0 || edad > 100) { $('#edad_info').text('Fecha inválida'); return; }
    $('#edad_info').html('<i class="fas fa-child mr-1"></i>Edad: <strong>' + edad + ' años</strong>');
    
    // Sugerir categoría por edad
    var sugerida = null;
    $('#categoria_id option').each(function() {
        var min = parseInt($(this).data('min'));
        var max = parseInt($(this).data('max'));
        if (!isNaN(min) && !isNaN(max) && edad >= min && edad <= max) {
            sugerida = $(this).text().trim();
            if (!$('#categoria_id').val()) {
                $(this).prop('selected', true);
            }
        }
    });
    if (sugerida) {
        $('#categoria_sugerida').html('<i class="fas fa-lightbulb mr-1"></i>Sugerida: <strong>' + sugerida + '</strong>');
    } else {
        $('#categoria_sugerida').text('');
    }
}

// ===================== BÚSQUEDA DE ALUMNO EXISTENTE =====================

/**
 * Busca en el sistema si ya existe un alumno con la identificación ingresada.
 * Funciona cross-tenant para detectar registros previos de otros sistemas.
 */
function buscarAlumnoExistente() {
    var val  = $('#identificacion').val().trim();
    var tipo = $('#tipo_identificacion').val();

    if (!val) {
        $('#id_feedback').text('Ingrese la identificación para buscar').removeClass('text-success text-danger').addClass('text-warning');
        return;
    }
    var r = validarDoc(val, tipo);
    if (!r.valido) {
        $('#id_feedback').text(r.mensaje).removeClass('text-success text-warning').addClass('text-danger');
        return;
    }

    $('#panelAlumnoEncontrado').slideUp();
    $('#id_feedback').text('Verificando...').removeClass('text-success text-danger text-warning').addClass('text-info');

    $.ajax({
        url: '<?= url('futbol', 'alumno', 'buscarAlumno') ?>',
        type: 'GET',
        data: { identificacion: val },
        dataType: 'json',
        success: function(res) {
            if (res.success && res.data) {
                mostrarAlumnoEncontrado(res.data, res.mismo_tenant);
                $('#id_feedback').text('⚠ Identificación ya registrada en el sistema').removeClass('text-success text-danger text-info').addClass('text-warning');
            } else {
                $('#id_feedback').text(r.mensaje).removeClass('text-danger text-warning text-info').addClass('text-success');
                $('#panelAlumnoEncontrado').slideUp();
            }
        },
        error: function() {
            $('#id_feedback').text('').removeClass('text-info');
        }
    });
}

/**
 * Muestra el panel informativo con el alumno encontrado.
 * - mismo_tenant=true  → el alumno ya está en ESTE sistema → ofrecer ir a editar
 * - mismo_tenant=false → viene de otro tenant → ofrecer pre-cargar datos
 */
function mostrarAlumnoEncontrado(data, mismoTenant) {
    var nombre = (data.alu_nombres || '') + ' ' + (data.alu_apellidos || '');
    var detalle = '<strong>' + nombre.trim() + '</strong>';
    if (data.alu_fecha_nacimiento) detalle += ' &nbsp;·&nbsp; Nac: ' + data.alu_fecha_nacimiento;
    if (data.alu_genero) detalle += ' &nbsp;·&nbsp; ' + (data.alu_genero === 'M' ? 'Masculino' : data.alu_genero === 'F' ? 'Femenino' : data.alu_genero);

    var alert = $('#alertAlumnoEncontrado');
    var infoDiv = $('#alumnoEncontradoInfo');
    var accionesDiv = $('#alumnoEncontradoAcciones');

    if (mismoTenant) {
        alert.removeClass('alert-info').addClass('alert-warning');
        infoDiv.html(
            '<i class="fas fa-exclamation-triangle text-warning mr-1"></i>' +
            '<strong>Este alumno ya está registrado en este sistema.</strong><br>' +
            '<span class="text-sm">' + detalle + '</span>'
        );
        accionesDiv.html(
            '<a href="<?= url('futbol', 'alumno', 'editar') ?>&id=' + data.alu_alumno_id + '" class="btn btn-sm btn-warning">' +
            '<i class="fas fa-edit mr-1"></i>Ir a editar este alumno</a>'
        );
    } else {
        alert.removeClass('alert-warning').addClass('alert-info');
        infoDiv.html(
            '<i class="fas fa-info-circle text-info mr-1"></i>' +
            '<strong>Datos encontrados en otro sistema.</strong><br>' +
            '<span class="text-sm">' + detalle + '</span>'
        );
        accionesDiv.html(
            '<button type="button" class="btn btn-sm btn-primary" id="btnCargarDatosAlumno">' +
            '<i class="fas fa-file-import mr-1"></i>Cargar datos en el formulario</button> ' +
            '<button type="button" class="btn btn-sm btn-outline-secondary ml-1" id="btnIgnorarAlumnoFound">' +
            '<i class="fas fa-times mr-1"></i>Continuar sin cargar</button>'
        );
        // Guardar los datos en el panel para usarlos al pulsar "Cargar"
        $('#panelAlumnoEncontrado').data('alumno-data', data);

        // Event delegation para botones dinámicos
        $(document).off('click.alumnoFound').on('click.alumnoFound', '#btnCargarDatosAlumno', function() {
            cargarDatosAlumno($('#panelAlumnoEncontrado').data('alumno-data'));
            $('#panelAlumnoEncontrado').slideUp();
        }).on('click.alumnoFound', '#btnIgnorarAlumnoFound', function() {
            $('#panelAlumnoEncontrado').slideUp();
        });
    }

    $('#panelAlumnoEncontrado').slideDown();
}

/**
 * Pre-carga en el formulario los datos personales y médicos del alumno encontrado.
 * No toca los campos de ficha deportiva (son tenant-específicos).
 */
function cargarDatosAlumno(data) {
    $('[name="nombres"]').val(data.alu_nombres || '');
    $('[name="apellidos"]').val(data.alu_apellidos || '');
    if (data.alu_genero)     $('[name="genero"]').val(data.alu_genero);
    if (data.alu_fecha_nacimiento) $('[name="fecha_nacimiento"]').val(data.alu_fecha_nacimiento).trigger('change');
    // Médicos
    if (data.alu_tipo_sangre)           $('[name="tipo_sangre"]').val(data.alu_tipo_sangre);
    if (data.alu_alergias)              $('[name="alergias"]').val(data.alu_alergias);
    if (data.alu_condiciones_medicas)   $('[name="condiciones_medicas"]').val(data.alu_condiciones_medicas);
    if (data.alu_medicamentos)          $('[name="medicamentos"]').val(data.alu_medicamentos);
    if (data.alu_contacto_emergencia)   $('[name="contacto_emergencia"]').val(data.alu_contacto_emergencia);
    if (data.alu_telefono_emergencia)   $('[name="telefono_emergencia"]').val(data.alu_telefono_emergencia);
    if (data.alu_observaciones_medicas) $('[name="observaciones_medicas"]').val(data.alu_observaciones_medicas);

    Swal.fire({ icon: 'success', title: 'Datos cargados', text: 'Revise y complete la información antes de guardar', timer: 2500, showConfirmButton: false });
}

// ===================== VALIDACIÓN IDENTIFICACIÓN ECUADOR =====================

/**
 * Enrutador principal: cédula/RUC o pasaporte según el tipo seleccionado.
 * tipo: 'CED' (cédula/RUC) | 'PAS' (pasaporte)
 */
function validarDoc(val, tipo) {
    if (tipo === 'PAS') return validarPasaporte(val);
    return validarIdentificacion(val);
}

/**
 * Valida número de pasaporte internacional.
 * Acepta alfanumérico + guión, 5-20 caracteres.
 */
function validarPasaporte(val) {
    val = (val || '').trim();
    if (!val) return { valido: false, tipo: 'PAS', mensaje: 'Ingrese el número de pasaporte' };
    if (val.length < 5 || val.length > 20) {
        return { valido: false, tipo: 'PAS', mensaje: '⚠ El pasaporte debe tener entre 5 y 20 caracteres' };
    }
    if (!/^[A-Za-z0-9\-]+$/.test(val)) {
        return { valido: false, tipo: 'PAS', mensaje: '⚠ Solo se permiten letras, números y guión' };
    }
    return { valido: true, tipo: 'PAS', mensaje: '✓ Pasaporte válido' };
}

/**
 * Valida cédula (10 dígitos) o RUC (13 dígitos) ecuatoriano.
 * Retorna { valido, tipo, mensaje }
 *   tipo: 'CEDULA' | 'RUC_NATURAL' | 'RUC_JURIDICO' | 'RUC_PUBLICO' | ''
 */
function validarIdentificacion(val) {
    val = (val || '').trim();
    if (!/^\d+$/.test(val)) return { valido: false, tipo: '', mensaje: 'Solo se permiten dígitos' };

    if (val.length === 10) {
        return _validarCedula(val);
    } else if (val.length === 13) {
        return _validarRUC(val);
    }
    return { valido: false, tipo: '', mensaje: 'Debe tener 10 dígitos (cédula) o 13 dígitos (RUC)' };
}

function _validarCedula(val) {
    var d = val.split('').map(Number);
    var prov = d[0] * 10 + d[1];
    if (prov < 1 || (prov > 24 && prov !== 30)) {
        return { valido: false, tipo: '', mensaje: '⚠ Código de provincia inválido (' + prov + ')' };
    }
    if (d[2] > 5) {
        return { valido: false, tipo: '', mensaje: '⚠ Tercer dígito inválido para persona natural' };
    }
    var sum = 0;
    for (var i = 0; i < 9; i++) {
        var k = d[i] * (i % 2 === 0 ? 2 : 1);
        sum += k > 9 ? k - 9 : k;
    }
    var check = (10 - (sum % 10)) % 10;
    if (check !== d[9]) return { valido: false, tipo: '', mensaje: '✗ Cédula inválida (dígito verificador)' };
    return { valido: true, tipo: 'CEDULA', mensaje: '✓ Cédula válida' };
}

function _validarRUC(val) {
    var d = val.split('').map(Number);
    var prov = d[0] * 10 + d[1];
    if (prov < 1 || (prov > 24 && prov !== 30)) {
        return { valido: false, tipo: '', mensaje: '⚠ Código de provincia inválido (' + prov + ')' };
    }
    var ter = d[2];
    if (ter >= 0 && ter <= 5) {
        // Persona natural: primeros 10 = cédula válida + sufijo 001-999
        var ced = val.substring(0, 10);
        var cedResult = _validarCedula(ced);
        if (!cedResult.valido) return { valido: false, tipo: '', mensaje: '✗ RUC inválido (base cédula incorrecta)' };
        var sufijo = parseInt(val.substring(10), 10);
        if (sufijo < 1) return { valido: false, tipo: '', mensaje: '✗ RUC inválido (sufijo debe ser ≥ 001)' };
        return { valido: true, tipo: 'RUC_NATURAL', mensaje: '✓ RUC persona natural válido' };
    } else if (ter === 6) {
        // Entidad pública: verificador en posición 9 (base 1-8)
        var coef6 = [3, 2, 7, 6, 5, 4, 3, 2];
        var sum6 = 0;
        for (var i = 0; i < 8; i++) sum6 += d[i] * coef6[i];
        var check6 = 11 - (sum6 % 11);
        if (check6 === 11) check6 = 0;
        if (check6 === 10 || check6 !== d[8]) return { valido: false, tipo: '', mensaje: '✗ RUC entidad pública inválido' };
        return { valido: true, tipo: 'RUC_PUBLICO', mensaje: '✓ RUC entidad pública válido' };
    } else if (ter === 9) {
        // Persona jurídica: verificador en posición 10 (base 1-9)
        var coef9 = [4, 3, 2, 7, 6, 5, 4, 3, 2];
        var sum9 = 0;
        for (var i = 0; i < 9; i++) sum9 += d[i] * coef9[i];
        var check9 = 11 - (sum9 % 11);
        if (check9 === 11) check9 = 0;
        if (check9 === 10 || check9 !== d[9]) return { valido: false, tipo: '', mensaje: '✗ RUC persona jurídica inválido' };
        return { valido: true, tipo: 'RUC_JURIDICO', mensaje: '✓ RUC persona jurídica válido' };
    }
    return { valido: false, tipo: '', mensaje: '⚠ Tercer dígito de RUC inválido (' + ter + ')' };
}

// Feedback en tiempo real para identificación del alumno
$('#identificacion').on('blur', function() {
    var val = $(this).val().trim();
    var tipo = $('#tipo_identificacion').val();
    var fb = $('#id_feedback');
    if (!val) { fb.text(''); return; }
    var r = validarDoc(val, tipo);
    fb.text(r.mensaje)
      .removeClass('text-success text-danger text-warning')
      .addClass(r.valido ? 'text-success' : (val.length >= 5 ? 'text-danger' : 'text-warning'));
});

// Al cambiar tipo de identificación del alumno: resetear feedback y ajustar placeholder
$('#tipo_identificacion').on('change', function() {
    var tipo = $(this).val();
    $('#identificacion')
        .attr('placeholder', tipo === 'PAS' ? 'Ej: AB123456' : 'Ej: 0912345678')
        .attr('maxlength', tipo === 'PAS' ? '20' : '13');
    $('#id_feedback').text('');
});

// Feedback en tiempo real para identificación del representante
$('#rep_cedula').on('blur', function() {
    var val = $(this).val().trim();
    var tipo = $('#tipo_id_rep').val();
    var fb = $('#rep_feedback');
    if ($('#representante_id').val()) return; // ya hay representante cargado
    if (!val) { fb.text(''); return; }
    var r = validarDoc(val, tipo);
    if (r.valido) {
        fb.text(r.mensaje).removeClass('text-danger text-warning text-info').addClass('text-success');
    } else if (val.length >= 5) {
        fb.text(r.mensaje).removeClass('text-success text-warning text-info').addClass('text-danger');
    } else {
        fb.text('').removeClass('text-danger text-success text-info text-warning');
    }
});

// Al cambiar tipo de identificación del representante: resetear feedback y ajustar placeholder
$('#tipo_id_rep').on('change', function() {
    var tipo = $(this).val();
    $('#rep_cedula')
        .attr('placeholder', tipo === 'PAS' ? 'Ej: AB123456' : 'Ingrese cédula o RUC')
        .attr('maxlength', tipo === 'PAS' ? '20' : '13');
    $('#rep_feedback').text('');
    // Si había un representante cargado, limpiarlo porque el tipo cambió
    if ($('#representante_id').val()) { limpiarRepresentante(); }
});

// ===================== REPRESENTANTE =====================

/**
 * Buscar representante por cédula/RUC en la tabla clientes
 */
function buscarRepresentante() {
    var cedula = $('#rep_cedula').val().trim();
    var tipo   = $('#tipo_id_rep').val();
    if (!cedula) {
        $('#rep_feedback').text('Ingrese la identificación del representante').removeClass('text-success text-danger text-info').addClass('text-warning');
        return;
    }
    var r = validarDoc(cedula, tipo);
    if (!r.valido) {
        $('#rep_feedback').text(r.mensaje || 'Identificación inválida').removeClass('text-success text-info text-warning').addClass('text-danger');
        return;
    }

    $('#btnBuscarRep').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
    $('#rep_feedback').text('Buscando...').removeClass('text-danger text-success text-warning').addClass('text-info');

    $.ajax({
        url: '<?= url('futbol', 'alumno', 'buscarRepresentante') ?>',
        type: 'GET',
        data: { cedula: cedula },
        dataType: 'json',
        success: function(res) {
            $('#btnBuscarRep').prop('disabled', false).html('<i class="fas fa-search mr-1"></i>Buscar');
            if (res.success && res.data) {
                // Representante encontrado → mostrar datos
                cargarRepresentante(res.data);
                $('#rep_feedback').text('✓ Representante encontrado').removeClass('text-danger text-warning text-info').addClass('text-success');
                // Cargar hermanos
                if (res.hermanos && res.hermanos.length > 0) {
                    mostrarHermanos(res.hermanos);
                } else {
                    $('#panelHermanos').hide();
                }
            } else {
                // No encontrado → mostrar formulario de creación
                $('#panelRepresentante').hide();
                $('#panelNuevoRep').slideDown();
                $('#panelHermanos').hide();
                $('#representante_id').val('');
                $('#rep_feedback').text('No encontrado. Registre los datos del representante.').removeClass('text-success text-info').addClass('text-warning');
            }
        },
        error: function() {
            $('#btnBuscarRep').prop('disabled', false).html('<i class="fas fa-search mr-1"></i>Buscar');
            $('#rep_feedback').text('Error de comunicación').removeClass('text-success text-info').addClass('text-danger');
        }
    });
}

/**
 * Cargar datos del representante encontrado en el panel
 */
function cargarRepresentante(data) {
    $('#representante_id').val(data.cli_cliente_id);
    $('#repNombreDisplay').text((data.cli_nombres || '') + ' ' + (data.cli_apellidos || ''));
    $('#repCedulaDisplay').text(data.cli_identificacion || '');
    $('#repTelefonoDisplay').text(data.cli_telefono || data.cli_celular || '');
    $('#repEmailDisplay').text(data.cli_email || '');
    $('#repDireccionDisplay').text(data.cli_direccion || '');
    // Guardar nombres/apellidos por separado para el formulario de edición
    $('#panelRepresentante').data('rep-nombres', data.cli_nombres || '').data('rep-apellidos', data.cli_apellidos || '');

    // Marcar consentimiento si ya lo dio
    if (data.cli_consentimiento_datos == 1) {
        $('#chkConsentimiento').prop('checked', true);
    }

    $('#panelNuevoRep').hide();
    $('#panelRepresentante').slideDown();
}

/**
 * Abrir modo edición del representante: rellena campos con valores actuales
 */
function abrirEdicionRepresentante() {
    var nombre = $('#repNombreDisplay').text().trim().split(' ');
    // Llenar campos con los valores mostrados actualmente
    $('#edit_rep_telefono').val($('#repTelefonoDisplay').text().trim());
    $('#edit_rep_email').val($('#repEmailDisplay').text().trim());
    $('#edit_rep_direccion').val($('#repDireccionDisplay').text().trim());
    // Para nombres/apellidos usamos atributos data guardados al cargar
    $('#edit_rep_nombres').val($('#panelRepresentante').data('rep-nombres') || '');
    $('#edit_rep_apellidos').val($('#panelRepresentante').data('rep-apellidos') || '');

    $('#repVistaLectura').hide();
    $('#repVistaEdicion').slideDown();
}

/**
 * Cerrar modo edición sin guardar
 */
function cerrarEdicionRepresentante() {
    $('#repVistaEdicion').hide();
    $('#repVistaLectura').slideDown();
}

/**
 * Guardar cambios del representante vía AJAX
 */
function guardarEdicionRepresentante() {
    var clienteId = $('#representante_id').val();
    if (!clienteId) { Swal.fire('Error', 'No hay representante seleccionado', 'error'); return; }

    var nombres   = $('#edit_rep_nombres').val().trim();
    var apellidos = $('#edit_rep_apellidos').val().trim();
    var telefono  = $('#edit_rep_telefono').val().trim();

    if (!nombres || !apellidos) { Swal.fire('Error', 'Nombres y apellidos son obligatorios', 'warning'); return; }
    if (!telefono) { Swal.fire('Error', 'El teléfono es obligatorio', 'warning'); return; }

    var btn = $('#btnGuardarEditRep');
    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i>Guardando...');

    $.ajax({
        url: '<?= url('futbol', 'alumno', 'actualizarRepresentante') ?>',
        type: 'POST',
        data: {
            csrf_token: $('input[name="csrf_token"]').val(),
            cliente_id: clienteId,
            nombres:    nombres,
            apellidos:  apellidos,
            telefono:   telefono,
            email:      $('#edit_rep_email').val().trim(),
            direccion:  $('#edit_rep_direccion').val().trim()
        },
        dataType: 'json',
        success: function(res) {
            btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i>Guardar cambios');
            if (res.success && res.data) {
                cargarRepresentante(res.data);
                cerrarEdicionRepresentante();
                Swal.fire({ icon: 'success', title: 'Datos actualizados', timer: 1500, showConfirmButton: false });
            } else {
                Swal.fire('Error', res.message || 'No se pudo actualizar', 'error');
            }
        },
        error: function() {
            btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i>Guardar cambios');
            Swal.fire('Error', 'Error de comunicación con el servidor', 'error');
        }
    });
}

/**
 * Mostrar hermanos inscritos (detección de beca)
 */
function mostrarHermanos(hermanos) {
    if (!hermanos || hermanos.length === 0) {
        $('#panelHermanos').hide();
        return;
    }
    var html = '';
    hermanos.forEach(function(h) {
        html += '<span class="badge badge-light ml-1">' + (h.alu_nombres || '') + ' ' + (h.alu_apellidos || '') + '</span>';
    });
    $('#hermanosLista').html(html);
    $('#panelHermanos').slideDown();
}

/**
 * Limpiar representante seleccionado
 */
function limpiarRepresentante() {
    $('#representante_id').val('');
    $('#rep_cedula').val('');
    $('#rep_feedback').text('');
    $('#panelRepresentante').hide();
    $('#panelNuevoRep').hide();
    $('#panelHermanos').hide();
}

/**
 * Crear nuevo representante (inline) vía AJAX
 */
function crearRepresentante() {
    var nombres = $('#nuevo_rep_nombres').val().trim();
    var apellidos = $('#nuevo_rep_apellidos').val().trim();
    var telefono = $('#nuevo_rep_telefono').val().trim();
    var cedula = $('#rep_cedula').val().trim();
    var tipo = $('#tipo_id_rep').val();
    var parentesco = $('#nuevo_rep_parentesco').val();

    if (!nombres || !apellidos) { Swal.fire('Error', 'Nombres y apellidos son obligatorios', 'warning'); return; }
    if (!telefono) { Swal.fire('Error', 'El teléfono es obligatorio', 'warning'); return; }
    if (!cedula) { Swal.fire('Error', 'La identificación es obligatoria', 'warning'); return; }
    var rVal = validarDoc(cedula, tipo);
    if (!rVal.valido) { Swal.fire('Identificación inválida', rVal.mensaje, 'warning'); return; }
    if (!parentesco) { Swal.fire('Error', 'Seleccione el parentesco', 'warning'); return; }

    $.ajax({
        url: '<?= url('futbol', 'alumno', 'crearRepresentante') ?>',
        type: 'POST',
        data: {
            csrf_token: $('input[name="csrf_token"]').val(),
            tipo_identificacion: tipo,
            identificacion: cedula,
            nombres: nombres,
            apellidos: apellidos,
            telefono: telefono,
            email: $('#nuevo_rep_email').val().trim(),
            direccion: $('#nuevo_rep_direccion').val().trim(),
            consentimiento: $('#chkConsentimientoNuevo').is(':checked') ? 1 : 0
        },
        dataType: 'json',
        success: function(res) {
            if (res.success && res.data) {
                cargarRepresentante(res.data);
                // Establecer parentesco del nuevo
                $('select[name="parentesco"]').val(parentesco);
                $('#panelNuevoRep').hide();
                $('#rep_feedback').text('✓ Representante registrado').removeClass('text-danger text-warning').addClass('text-success');
                Swal.fire({ icon: 'success', title: 'Representante registrado', timer: 1500, showConfirmButton: false });
            } else {
                Swal.fire('Error', res.message || 'No se pudo registrar', 'error');
            }
        },
        error: function() {
            Swal.fire('Error', 'Error de comunicación con el servidor', 'error');
        }
    });
}

// ===================== GUARDAR ALUMNO =====================

function guardarAlumno(e) {
    e.preventDefault();
    
    var form = document.getElementById('formAlumnoFutbol');
    
    // Validar representante
    var repId = $('#representante_id').val();
    if (!repId) {
        Swal.fire('Representante requerido', 'Debe buscar y seleccionar un representante por su cédula', 'warning');
        return;
    }

    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }
    
    var btn = $('#btnGuardar');
    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i>Guardando...');
    
    var formData = new FormData(form);
    // Asegurar checkbox
    if (!$('#chkAutMedica').is(':checked')) {
        formData.set('autorizacion_medica', '0');
    }
    // Consentimiento
    if ($('#chkConsentimiento').is(':checked')) {
        formData.set('consentimiento_datos', '1');
    }
    
    var esEdicion = formData.has('id') && formData.get('id');
    var actionUrl = esEdicion
        ? '<?= url('futbol', 'alumno', 'editar') ?>'
        : '<?= url('futbol', 'alumno', 'crear') ?>';
    
    $.ajax({
        url: actionUrl,
        type: 'POST',
        data: new URLSearchParams(formData).toString(),
        contentType: 'application/x-www-form-urlencoded',
        dataType: 'json',
        success: function(res) {
            if (res.success) {
                // Si es nuevo alumno y hay foto pendiente, subirla antes de redirigir
                if (!esEdicion && typeof window._fotoGetSelectedFile === 'function' && window._fotoGetSelectedFile()) {
                    btn.html('<i class="fas fa-spinner fa-spin mr-2"></i>Subiendo foto...');
                    window._fotoUploadParaNuevoAlumno(res.alumno_id, function(fotoError) {
                        var txt = fotoError
                            ? 'Alumno registrado. No se pudo subir la foto: ' + fotoError
                            : 'Alumno registrado con foto correctamente';
                        Swal.fire({
                            icon: fotoError ? 'warning' : 'success',
                            title: 'Alumno Registrado',
                            text: txt,
                            timer: 2500,
                            showConfirmButton: false
                        }).then(function() {
                            window.location.href = '<?= url('futbol', 'alumno', 'index') ?>';
                        });
                    });
                    return;
                }
                Swal.fire({
                    icon: 'success',
                    title: esEdicion ? 'Alumno Actualizado' : 'Alumno Registrado',
                    text: res.message || 'Operación exitosa',
                    timer: 2000,
                    showConfirmButton: false
                }).then(function() {
                    window.location.href = '<?= url('futbol', 'alumno', 'index') ?>';
                });
            } else {
                Swal.fire('Error', res.message || 'No se pudo guardar', 'error');
                btn.prop('disabled', false).html('<i class="fas fa-save mr-2"></i><?= $editando ? 'Actualizar Alumno' : 'Registrar Alumno' ?>');
            }
        },
        error: function(xhr) {
            console.error('Error:', xhr.responseText);
            Swal.fire('Error', 'Error de comunicación con el servidor', 'error');
            btn.prop('disabled', false).html('<i class="fas fa-save mr-2"></i><?= $editando ? 'Actualizar Alumno' : 'Registrar Alumno' ?>');
        }
    });
}

// ===================== FOTO DEL ALUMNO =====================
(function() {
    var alumnoId   = <?= (int)($alumno['alu_alumno_id'] ?? 0) ?>; // 0 en modo crear; se asigna tras guardar
    var csrfToken  = '<?= htmlspecialchars($csrf_token ?? '', ENT_QUOTES) ?>';
    var urlSubir   = '<?= url('futbol', 'alumno', 'subirFoto') ?>';
    var urlEliminar= '<?= url('futbol', 'alumno', 'eliminarFoto') ?>';
    var selectedFile = null;

    $('#btnSeleccionarFoto').on('click', function() { $('#inputFoto').click(); });

    $('#inputFoto').on('change', function() {
        var file = this.files[0];
        if (!file) return;

        // Validación client-side (5 MB)
        if (file.size > 5 * 1024 * 1024) {
            mostrarFeedback('El archivo supera el límite de 5 MB', 'text-danger');
            return;
        }

        selectedFile = file;
        // Preview local
        var reader = new FileReader();
        reader.onload = function(e) {
            $('#fotoPlaceholder').hide();
            $('#fotoPreview').attr('src', e.target.result).show();
        };
        reader.readAsDataURL(file);
        $('#fotoAcciones').show();
        mostrarFeedback('');
    });

    $('#btnCancelarFoto').on('click', function() {
        selectedFile = null;
        $('#inputFoto').val('');
        $('#fotoAcciones').hide();
        mostrarFeedback('');
    });

    $('#btnSubirFoto').on('click', function() {
        if (!selectedFile) return;
        var btn = $(this);
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i>Subiendo...');

        var fd = new FormData();
        fd.append('csrf_token', csrfToken);
        fd.append('alumno_id', alumnoId);
        fd.append('foto', selectedFile);

        $.ajax({
            url: urlSubir,
            type: 'POST',
            data: fd,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(res) {
                btn.prop('disabled', false).html('<i class="fas fa-upload mr-1"></i>Subir Foto');
                if (res.success) {
                    $('#fotoAcciones').hide();
                    mostrarFeedback('');
                    selectedFile = null;
                    // Actualizar preview al URL real del servidor
                    $('#fotoPlaceholder').hide();
                    $('#fotoPreview').attr('src', res.foto_url).show();
                    // Agregar botón eliminar si no existía
                    if (!$('#btnEliminarFoto').length) {
                        $('<button type="button" class="btn btn-sm btn-outline-danger ml-1" id="btnEliminarFoto"><i class="fas fa-trash"></i></button>')
                            .data('arc-id', res.arc_id)
                            .insertAfter('#btnSeleccionarFoto');
                        bindEliminarFoto();
                    } else {
                        $('#btnEliminarFoto').data('arc-id', res.arc_id);
                    }
                    $('#btnSeleccionarFoto').html('<i class="fas fa-image mr-1"></i>Cambiar');
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: 'Foto actualizada correctamente',
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true
                    });
                } else {
                    mostrarFeedback(res.message || 'Error al subir la foto', 'text-danger');
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'error',
                        title: res.message || 'Error al subir la foto',
                        showConfirmButton: false,
                        timer: 4000,
                        timerProgressBar: true
                    });
                }
            },
            error: function() {
                btn.prop('disabled', false).html('<i class="fas fa-upload mr-1"></i>Subir Foto');
                mostrarFeedback('Error de comunicación con el servidor', 'text-danger');
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'error',
                    title: 'Error de comunicación con el servidor',
                    showConfirmButton: false,
                    timer: 4000,
                    timerProgressBar: true
                });
            }
        });
    });

    function bindEliminarFoto() {
        $('#btnEliminarFoto').off('click').on('click', function() {
            var arcId = $(this).data('arc-id');

            Swal.fire({
                title: '¿Eliminar foto?',
                text: 'Se eliminará la foto del alumno. Esta acción no se puede deshacer.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e74c3c',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fas fa-trash mr-1"></i>Sí, eliminar',
                cancelButtonText: 'Cancelar',
                reverseButtons: true
            }).then(function(result) {
                if (!result.isConfirmed) return;

                $.ajax({
                    url: urlEliminar,
                    type: 'POST',
                    data: { csrf_token: csrfToken, alumno_id: alumnoId, arc_id: arcId },
                    dataType: 'json',
                    success: function(res) {
                        if (res.success) {
                            $('#fotoPreview').attr('src', '').hide();
                            $('#fotoPlaceholder').show();
                            $('#btnEliminarFoto').remove();
                            $('#btnSeleccionarFoto').html('<i class="fas fa-image mr-1"></i>Seleccionar');
                            mostrarFeedback('');
                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'success',
                                title: 'Foto eliminada correctamente',
                                showConfirmButton: false,
                                timer: 3000,
                                timerProgressBar: true
                            });
                        } else {
                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'error',
                                title: res.message || 'Error al eliminar la foto',
                                showConfirmButton: false,
                                timer: 4000,
                                timerProgressBar: true
                            });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'error',
                            title: 'Error de comunicación con el servidor',
                            showConfirmButton: false,
                            timer: 4000,
                            timerProgressBar: true
                        });
                    }
                });
            });
        });
    }
    bindEliminarFoto();

    function mostrarFeedback(msg, cls) {
        $('#fotoFeedback').text(msg).attr('class', 'mt-2 small ' + (cls || ''));
    }

    // Exponer función para subir foto al crear un nuevo alumno (llamada desde guardarAlumno)
    window._fotoGetSelectedFile = function() { return selectedFile; };
    window._fotoUploadParaNuevoAlumno = function(nuevoAlumnoId, onDone) {
        if (!selectedFile) { onDone(); return; }
        alumnoId = nuevoAlumnoId;
        var fd = new FormData();
        fd.append('csrf_token', csrfToken);
        fd.append('alumno_id', alumnoId);
        fd.append('foto', selectedFile);
        $.ajax({
            url: urlSubir, type: 'POST',
            data: fd, processData: false, contentType: false, dataType: 'json',
            success: function(res) { onDone(res.success ? null : (res.message || 'Error al subir foto')); },
            error:   function()    { onDone('Error de comunicación al subir foto'); }
        });
    };
})();
</script>
<?php $scripts = ob_get_clean(); ?>
