<?php
// Vista para crear un nuevo tenant
?>
<div class="container mt-4">
	<h2 class="mb-4"><i class="fas fa-building"></i> Nuevo Tenant</h2>
	<form method="post" action="">
		<div class="row">
			<div class="col-md-6">
				<div class="form-group">
					<label for="ruc">RUC *</label>
					<input type="text" class="form-control" id="ruc" name="ruc" required maxlength="13">
				</div>
				<div class="form-group">
					<label for="razon_social">Razón Social *</label>
					<input type="text" class="form-control" id="razon_social" name="razon_social" required>
				</div>
				<div class="form-group">
					<label for="nombre_comercial">Nombre Comercial</label>
					<input type="text" class="form-control" id="nombre_comercial" name="nombre_comercial">
				</div>
				<div class="form-group">
					<label for="tipo_empresa">Tipo de Empresa</label>
					<input type="text" class="form-control" id="tipo_empresa" name="tipo_empresa">
				</div>
				<div class="form-group">
					<label for="direccion">Dirección</label>
					<input type="text" class="form-control" id="direccion" name="direccion">
				</div>
				<div class="form-group">
					<label for="telefono">Teléfono</label>
					<input type="text" class="form-control" id="telefono" name="telefono">
				</div>
				<div class="form-group">
					<label for="celular">Celular</label>
					<input type="text" class="form-control" id="celular" name="celular">
				</div>
				<div class="form-group">
					<label for="email">Email *</label>
					<input type="email" class="form-control" id="email" name="email" required>
				</div>
				<div class="form-group">
					<label for="sitio_web">Sitio Web</label>
					<input type="text" class="form-control" id="sitio_web" name="sitio_web">
				</div>
			</div>
			<div class="col-md-6">
				<div class="form-group">
					<label for="representante_nombre">Nombre Representante</label>
					<input type="text" class="form-control" id="representante_nombre" name="representante_nombre">
				</div>
				<div class="form-group">
					<label for="representante_identificacion">Identificación Representante</label>
					<input type="text" class="form-control" id="representante_identificacion" name="representante_identificacion">
				</div>
				<div class="form-group">
					<label for="representante_email">Email Representante</label>
					<input type="email" class="form-control" id="representante_email" name="representante_email">
				</div>
				<div class="form-group">
					<label for="representante_telefono">Teléfono Representante</label>
					<input type="text" class="form-control" id="representante_telefono" name="representante_telefono">
				</div>
				<div class="form-group">
					<label for="plan_id">Plan *</label>
					<input type="number" class="form-control" id="plan_id" name="plan_id" required>
				</div>
				<div class="form-group">
					<label for="fecha_inicio">Fecha Inicio *</label>
					<input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" required>
				</div>
				<div class="form-group">
					<label for="fecha_vencimiento">Fecha Vencimiento *</label>
					<input type="date" class="form-control" id="fecha_vencimiento" name="fecha_vencimiento" required>
				</div>
				<div class="form-group">
					<label for="usuarios_permitidos">Usuarios Permitidos</label>
					<input type="number" class="form-control" id="usuarios_permitidos" name="usuarios_permitidos" min="1" value="5">
				</div>
				<div class="form-group">
					<label for="sedes_permitidas">Sedes Permitidas</label>
					<input type="number" class="form-control" id="sedes_permitidas" name="sedes_permitidas" min="1" value="1">
				</div>
				<div class="form-group">
					<label for="monto_mensual">Monto Mensual</label>
					<input type="number" step="0.01" class="form-control" id="monto_mensual" name="monto_mensual">
				</div>
				<div class="form-group">
					<label for="color_primario">Color Primario</label>
					<input type="color" class="form-control" id="color_primario" name="color_primario" value="#007bff">
				</div>
				<div class="form-group">
					<label for="color_secundario">Color Secundario</label>
					<input type="color" class="form-control" id="color_secundario" name="color_secundario" value="#6c757d">
				</div>
				<div class="form-group">
					<label for="estado">Estado</label>
					<select class="form-control" id="estado" name="estado">
						<option value="A">Activo</option>
						<option value="I">Inactivo</option>
					</select>
				</div>
			</div>
		</div>
		<div class="mt-4">
			<button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Guardar Tenant</button>
			<a href="<?= url('seguridad', 'tenant', 'index') ?>" class="btn btn-secondary">Cancelar</a>
		</div>
	</form>
</div>