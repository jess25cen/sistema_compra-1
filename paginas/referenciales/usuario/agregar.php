<div class="container-fluid card" style="padding: 30px;">
    <div class="row g-3">
        <div class="col-md-12">
            <h3 id="usuario_form_titulo">Nuevo Usuario</h3>
        </div>
        <div class="col-md-12">
            <hr>
        </div>
        <input type="hidden" id="id_usuario" value="0">
        <div class="col-md-6">
            <label for="usuario_nombre_usuario" class="form-label">Nombre Usuario *</label>
            <input type="text" class="form-control" id="usuario_nombre_usuario" placeholder="Nombre del usuario">
        </div>
        <div class="col-md-6">
            <label for="usuario_nickname" class="form-label">Nickname *</label>
            <input type="text" class="form-control" id="usuario_nickname" placeholder="Nombre de usuario (login)">
        </div>
        <div class="col-md-6">
            <label for="usuario_password" class="form-label">Contraseña</label>
            <input type="password" class="form-control" id="usuario_password" placeholder="Contraseña (déjalo vacío para no cambiar)">
            <small class="form-text text-muted">Requerida para nuevo usuario, opcional para editar</small>
        </div>
        <div class="col-md-6">
            <label for="usuario_id_rol" class="form-label">Rol</label>
            <input type="number" class="form-control" id="usuario_id_rol" placeholder="ID del rol" value="2">
        </div>
        <div class="col-md-6">
            <label for="usuario_limite_intentos" class="form-label">Límite de Intentos</label>
            <input type="number" class="form-control" id="usuario_limite_intentos" placeholder="Límite de intentos" value="3">
        </div>
        <div class="col-md-6">
            <label for="usuario_estado" class="form-label">Estado *</label>
            <select id="usuario_estado" class="form-select">
                <option value="ACTIVO">ACTIVO</option>
                <option value="INACTIVO">INACTIVO</option>
            </select>
        </div>
        <div class="col-md-12 text-end">
            <button class="btn btn-secondary" onclick="cancelarUsuario(); return false;">Cancelar</button>
            <button class="btn btn-primary" onclick="guardarUsuario(); return false;">Guardar</button>
        </div>
    </div>
</div>
