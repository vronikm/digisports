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
        <form id="formAlumnoFutbol" onsubmit="guardarAlumno(event)">
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
                            <div class="row">
                                <div class="col-md-5">
                                    <div class="form-group">
                                        <label>Nombres <span class="text-danger">*</span></label>
                                        <input type="text" name="nombres" class="form-control" required maxlength="150"
                                               value="<?= htmlspecialchars($alumno['alu_nombres'] ?? '') ?>"
                                               placeholder="Nombres del alumno">
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <div class="form-group">
                                        <label>Apellidos <span class="text-danger">*</span></label>
                                        <input type="text" name="apellidos" class="form-control" required maxlength="150"
                                               value="<?= htmlspecialchars($alumno['alu_apellidos'] ?? '') ?>"
                                               placeholder="Apellidos del alumno">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>Género</label>
                                        <select name="genero" class="form-control">
                                            <option value="">—</option>
                                            <option value="M" <?= ($alumno['alu_genero'] ?? '') === 'M' ? 'selected' : '' ?>>Masculino</option>
                                            <option value="F" <?= ($alumno['alu_genero'] ?? '') === 'F' ? 'selected' : '' ?>>Femenino</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Identificación / Cédula</label>
                                        <input type="text" name="identificacion" id="identificacion" class="form-control"
                                               maxlength="20" value="<?= htmlspecialchars($alumno['alu_identificacion'] ?? '') ?>"
                                               placeholder="Ej: 0912345678">
                                        <small id="id_feedback" class="form-text"></small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Fecha de Nacimiento <span class="text-danger">*</span></label>
                                        <input type="date" name="fecha_nacimiento" id="fecha_nacimiento" class="form-control" required
                                               value="<?= htmlspecialchars($alumno['alu_fecha_nacimiento'] ?? '') ?>">
                                        <small id="edad_info" class="form-text text-info"></small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Estado</label>
                                        <select name="estado" class="form-control">
                                            <option value="ACTIVO" <?= ($alumno['alu_estado'] ?? 'ACTIVO') === 'ACTIVO' ? 'selected' : '' ?>>Activo</option>
                                            <option value="INACTIVO" <?= ($alumno['alu_estado'] ?? '') === 'INACTIVO' ? 'selected' : '' ?>>Inactivo</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-5">
                                    <div class="form-group">
                                        <label>Email</label>
                                        <input type="email" name="email" class="form-control" maxlength="200"
                                               value="<?= htmlspecialchars($alumno['alu_email'] ?? '') ?>" placeholder="correo@ejemplo.com">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Teléfono</label>
                                        <input type="text" name="telefono" class="form-control" maxlength="20"
                                               value="<?= htmlspecialchars($alumno['alu_telefono'] ?? '') ?>" placeholder="09XX XXX XXX">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Dirección</label>
                                        <input type="text" name="direccion" class="form-control" maxlength="300"
                                               value="<?= htmlspecialchars($alumno['alu_direccion'] ?? '') ?>" placeholder="Ciudad, sector, calle...">
                                    </div>
                                </div>
                            </div>
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
                            <!-- Buscador por cédula -->
                            <div class="form-group">
                                <label>Cédula del Representante <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="text" id="rep_cedula" class="form-control" maxlength="13"
                                           value="<?= htmlspecialchars($rep['cli_identificacion'] ?? '') ?>"
                                           placeholder="Ingrese cédula o RUC y presione buscar">
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-warning" id="btnBuscarRep" onclick="buscarRepresentante()">
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
                            <div id="panelRepresentante" style="<?= !empty($rep) ? '' : 'display:none;' ?>">
                                <div class="alert alert-light border p-3 mb-3" id="repDatosCard">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h6 class="mb-0 text-dark">
                                            <i class="fas fa-user-check mr-1 text-success"></i>
                                            <span id="repNombreDisplay"><?= htmlspecialchars(trim(($rep['cli_nombres'] ?? '') . ' ' . ($rep['cli_apellidos'] ?? ''))) ?></span>
                                        </h6>
                                        <button type="button" class="btn btn-xs btn-outline-secondary" onclick="limpiarRepresentante()" title="Cambiar representante">
                                            <i class="fas fa-times"></i>
                                        </button>
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
                                <button type="button" class="btn btn-success btn-sm btn-block" onclick="crearRepresentante()">
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
<script>
$(function() {
    // Calcular edad al cambiar fecha de nacimiento
    $('#fecha_nacimiento').on('change', calcularEdad).trigger('change');

    // Buscar representante al presionar Enter en el campo de cédula
    $('#rep_cedula').on('keypress', function(e) {
        if (e.which === 13) { e.preventDefault(); buscarRepresentante(); }
    });
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

// Validación de cédula Ecuador
$('#identificacion').on('blur', function() {
    var val = $(this).val().trim();
    var fb = $('#id_feedback');
    if (val.length === 10 && /^\d{10}$/.test(val)) {
        var d = val.split('').map(Number);
        var prov = d[0] * 10 + d[1];
        if (prov < 1 || prov > 24) {
            fb.text('⚠ Provincia inválida').removeClass('text-success').addClass('text-danger');
            return;
        }
        var sum = 0;
        for (var i = 0; i < 9; i++) {
            var k = d[i] * (i % 2 === 0 ? 2 : 1);
            sum += k > 9 ? k - 9 : k;
        }
        var check = (10 - (sum % 10)) % 10;
        if (check === d[9]) {
            fb.text('✓ Cédula válida').removeClass('text-danger').addClass('text-success');
        } else {
            fb.text('✗ Cédula inválida').removeClass('text-success').addClass('text-danger');
        }
    } else {
        fb.text('');
    }
});

// ===================== REPRESENTANTE =====================

/**
 * Buscar representante por cédula/RUC en la tabla clientes
 */
function buscarRepresentante() {
    var cedula = $('#rep_cedula').val().trim();
    if (!cedula || cedula.length < 5) {
        $('#rep_feedback').text('Ingrese al menos 5 dígitos de la cédula/RUC').removeClass('text-success').addClass('text-warning');
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

    // Marcar consentimiento si ya lo dio
    if (data.cli_consentimiento_datos == 1) {
        $('#chkConsentimiento').prop('checked', true);
    }

    $('#panelNuevoRep').hide();
    $('#panelRepresentante').slideDown();
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
    var parentesco = $('#nuevo_rep_parentesco').val();

    if (!nombres || !apellidos) { Swal.fire('Error', 'Nombres y apellidos son obligatorios', 'warning'); return; }
    if (!telefono) { Swal.fire('Error', 'El teléfono es obligatorio', 'warning'); return; }
    if (!cedula) { Swal.fire('Error', 'La cédula es obligatoria', 'warning'); return; }
    if (!parentesco) { Swal.fire('Error', 'Seleccione el parentesco', 'warning'); return; }

    $.ajax({
        url: '<?= url('futbol', 'alumno', 'crearRepresentante') ?>',
        type: 'POST',
        data: {
            csrf_token: $('input[name="csrf_token"]').val(),
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
</script>
<?php $scripts = ob_get_clean(); ?>
