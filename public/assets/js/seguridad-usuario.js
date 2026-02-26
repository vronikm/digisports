/**
 * DigiSports - Seguridad Módulo
 * Script para mejorar UX en formulario de usuarios (crear/editar)
 * 
 * Funcionalidades:
 * - Confirmación antes de guardar cambios
 * - Toast de éxito/error después de guardar
 * - Detección automática de mensajes flash
 * 
 * @author Senior Developer
 * @version 1.0.0
 */

document.addEventListener('DOMContentLoaded', function() {
    
    const formEditarUsuario = document.getElementById('formEditarUsuario');
    const inputPassword = document.getElementById('inputPassword');
    const inputPasswordConfirm = document.getElementById('inputPasswordConfirm');
    const passwordMatchMessage = document.getElementById('passwordMatchMessage');
    const inputEmail = document.getElementById('inputEmail');
    const emailMessage = document.getElementById('emailMessage');
    const inputIdentificacion = document.getElementById('inputIdentificacion');
    const identificacionMessage = document.getElementById('identificacionMessage');
    
    // ==========================================
    // 1. MOSTRAR TOAST SI HAY MENSAJE FLASH
    // ==========================================
    function mostrarToastFlash() {
        // Buscar atributos data-flash en el body o contenedor
        const flashType = document.body.getAttribute('data-flash-type');
        const flashMessage = document.body.getAttribute('data-flash-message');
        
        if (flashType && flashMessage) {
            mostrarToast(flashMessage, flashType);
        }
    }
    
    // ==========================================
    // 1.5 VALIDACIÓN DE REQUISITOS DE CONTRASEÑA
    // ==========================================
    function validarRequisitosContrasena(password) {
        const requisitos = {
            length: password.length >= 8,
            uppercase: /[A-Z]/.test(password),
            number: /[0-9]/.test(password),
            symbol: /[!@#$%^&*()_\-+=\[\]{};:'",.<>?/\\|`~]/.test(password)
        };
        return requisitos;
    }
    
    function actualizarVisualesRequisitos() {
        if (!inputPassword) return;
        
        const password = inputPassword.value;
        const requisitos = validarRequisitosContrasena(password);
        
        // Actualizar visualmente cada requisito
        const updates = {
            'req-length': requisitos.length,
            'req-uppercase': requisitos.uppercase,
            'req-number': requisitos.number,
            'req-symbol': requisitos.symbol
        };
        
        Object.entries(updates).forEach(([id, cumple]) => {
            const elemento = document.getElementById(id);
            if (elemento) {
                const icono = elemento.querySelector('i');
                const texto = elemento.querySelector('span');
                if (cumple) {
                    icono.classList.remove('fa-times', 'text-danger');
                    icono.classList.add('fa-check', 'text-success');
                    texto.classList.remove('text-muted');
                    texto.classList.add('text-success');
                } else {
                    icono.classList.remove('fa-check', 'text-success');
                    icono.classList.add('fa-times', 'text-danger');
                    texto.classList.remove('text-success');
                    texto.classList.add('text-muted');
                }
            }
        });
    }
    
    function validarCoincidenciaContrasenas() {
        if (!inputPassword || !inputPasswordConfirm || !passwordMatchMessage) return true;
        
        const password = inputPassword.value;
        const passwordConfirm = inputPasswordConfirm.value;
        
        // Si ambos están vacíos, es válido (cuando es edición)
        if (!password && !passwordConfirm) return true;
        
        // Si uno está vacío y otro no, es inválido
        if ((password && !passwordConfirm) || (!password && passwordConfirm)) {
            passwordMatchMessage.style.display = 'block';
            passwordMatchMessage.className = 'text-danger small font-weight-bold d-block mt-2';
            passwordMatchMessage.innerHTML = '<i class="fas fa-exclamation-circle mr-1"></i>Las contraseñas deben coincidir';
            return false;
        }
        
        // Si ambos tienen valores, compararlos
        if (password && passwordConfirm) {
            if (password === passwordConfirm) {
                passwordMatchMessage.style.display = 'block';
                passwordMatchMessage.className = 'text-success small font-weight-bold d-block mt-2';
                passwordMatchMessage.innerHTML = '<i class="fas fa-check-circle mr-1"></i>Las contraseñas coinciden';
                return true;
            } else {
                passwordMatchMessage.style.display = 'block';
                passwordMatchMessage.className = 'text-danger small font-weight-bold d-block mt-2';
                passwordMatchMessage.innerHTML = '<i class="fas fa-exclamation-circle mr-1"></i>Las contraseñas no coinciden';
                return false;
            }
        }
        
        passwordMatchMessage.style.display = 'none';
        return true;
    }
    
    function todosLosRequisitosCompletos() {
        if (!inputPassword) return true; // Si no existe campo de password, es válido
        
        const password = inputPassword.value;
        
        // Si está vacío, es válido (edición sin cambiar password)
        if (!password) return true;
        
        const requisitos = validarRequisitosContrasena(password);
        return requisitos.length && requisitos.uppercase && requisitos.number && requisitos.symbol;
    }
    
    // Event listeners para validación en tiempo real
    if (inputPassword) {
        inputPassword.addEventListener('input', function() {
            actualizarVisualesRequisitos();
            validarCoincidenciaContrasenas();
        });
    }
    
    if (inputPasswordConfirm) {
        inputPasswordConfirm.addEventListener('input', function() {
            validarCoincidenciaContrasenas();
        });
    }
    
    // ==========================================
    // EMAIL VALIDATION
    // ==========================================
    function validarEmail(email) {
        // Regex para validar email (RFC 5322 simplificada)
        const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return regex.test(email);
    }
    
    function actualizarVisualesEmail() {
        if (!inputEmail || !emailMessage) return;
        
        const email = inputEmail.value.trim();
        
        // Si está vacío, no mostrar mensaje
        if (!email) {
            emailMessage.style.display = 'none';
            inputEmail.classList.remove('is-invalid', 'is-valid');
            return;
        }
        
        // Validar email
        if (validarEmail(email)) {
            emailMessage.style.display = 'block';
            emailMessage.className = 'text-success small font-weight-bold d-block mt-2';
            emailMessage.innerHTML = '<i class="fas fa-check-circle mr-1"></i>Email válido';
            inputEmail.classList.remove('is-invalid');
            inputEmail.classList.add('is-valid');
        } else {
            emailMessage.style.display = 'block';
            emailMessage.className = 'text-danger small font-weight-bold d-block mt-2';
            emailMessage.innerHTML = '<i class="fas fa-exclamation-circle mr-1"></i>Email inválido';
            inputEmail.classList.remove('is-valid');
            inputEmail.classList.add('is-invalid');
        }
    }
    
    if (inputEmail) {
        inputEmail.addEventListener('blur', actualizarVisualesEmail);
        inputEmail.addEventListener('input', actualizarVisualesEmail);
    }
    
    // ==========================================
    // CÉDULA ECUATORIANA VALIDATION
    // ==========================================
    function validarCedulaEcuatoriana(cedula) {
        // Limpiar espacios
        cedula = cedula.trim().replace(/\s/g, '');
        
        // Debe tener exactamente 10 dígitos
        if (!/^\d{10}$/.test(cedula)) {
            return false;
        }
        
        // Primer dígito (provincia) debe estar entre 00 y 24
        const provincia = parseInt(cedula.substring(0, 2), 10);
        if (provincia > 24) {
            return false;
        }
        
        // Tercer dígito debe ser 0, 1 o 2
        const tercerDigito = parseInt(cedula.charAt(2), 10);
        if (tercerDigito > 2) {
            return false;
        }
        
        // Calcular dígito verificador
        const coeficientes = [2, 1, 2, 1, 2, 1, 2, 1, 2];
        let suma = 0;
        
        for (let i = 0; i < 9; i++) {
            let digito = parseInt(cedula.charAt(i), 10) * coeficientes[i];
            
            // Si el resultado tiene 2 dígitos, restar 9
            if (digito > 9) {
                digito = digito - 9;
            }
            
            suma += digito;
        }
        
        // Calcular dígito verificador
        const digitoVerificadorCalculado = (10 - (suma % 10)) % 10;
        const digitoVerificadorReal = parseInt(cedula.charAt(9), 10);
        
        return digitoVerificadorCalculado === digitoVerificadorReal;
    }
    
    function actualizarVisualesCedula() {
        if (!inputIdentificacion || !identificacionMessage) return;
        
        const cedula = inputIdentificacion.value.trim();
        
        // Si está vacío, no mostrar mensaje (es opcional)
        if (!cedula) {
            identificacionMessage.style.display = 'none';
            inputIdentificacion.classList.remove('is-invalid', 'is-valid');
            return;
        }
        
        // Si tiene menos de 10 caracteres, mostrar que está incompleta
        if (cedula.length < 10 && /^\d*$/.test(cedula)) {
            identificacionMessage.style.display = 'block';
            identificacionMessage.className = 'text-warning small d-block mt-2';
            identificacionMessage.innerHTML = '<i class="fas fa-info-circle mr-1"></i>Incompleta (' + cedula.length + '/10)';
            inputIdentificacion.classList.remove('is-valid', 'is-invalid');
            return;
        }
        
        // Validar cédula
        if (validarCedulaEcuatoriana(cedula)) {
            identificacionMessage.style.display = 'block';
            identificacionMessage.className = 'text-success small font-weight-bold d-block mt-2';
            identificacionMessage.innerHTML = '<i class="fas fa-check-circle mr-1"></i>Cédula válida';
            inputIdentificacion.classList.remove('is-invalid');
            inputIdentificacion.classList.add('is-valid');
        } else {
            identificacionMessage.style.display = 'block';
            identificacionMessage.className = 'text-danger small font-weight-bold d-block mt-2';
            identificacionMessage.innerHTML = '<i class="fas fa-exclamation-circle mr-1"></i>Cédula inválida';
            inputIdentificacion.classList.remove('is-valid');
            inputIdentificacion.classList.add('is-invalid');
        }
    }
    
    if (inputIdentificacion) {
        inputIdentificacion.addEventListener('blur', actualizarVisualesCedula);
        inputIdentificacion.addEventListener('input', actualizarVisualesCedula);
    }
    
    // ==========================================
    // 2. FUNCIÓN PARA MOSTRAR TOAST
    // ==========================================
    function mostrarToast(mensaje, tipo = 'success') {
        // Validar que SweetAlert2 esté disponible
        if (typeof Swal === 'undefined') {
            console.warn('SweetAlert2 no disponible. Usando alert fallback.');
            alert(mensaje);
            return;
        }
        
        const iconMap = {
            'success': 'success',
            'error': 'error',
            'warning': 'warning',
            'info': 'info'
        };
        
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer);
                toast.addEventListener('mouseleave', Swal.resumeTimer);
            }
        });
        
        Toast.fire({
            icon: iconMap[tipo] || 'info',
            title: mensaje
        });
    }
    
    // ==========================================
    // 3. INTERCEPTAR SUBMIT DEL FORMULARIO
    // ==========================================
    if (formEditarUsuario) {
        formEditarUsuario.addEventListener('submit', function(e) {
            e.preventDefault(); // Evitar submit automático
            
            // Validar que el formulario sea válido antes de mostrar el cuadro
            if (!formEditarUsuario.checkValidity()) {
                // Si HTML5 validation falla, mostrar error
                formEditarUsuario.reportValidity();
                return false;
            }
            
            // Validar requisitos de contraseña si hay valor
            if (inputPassword && inputPassword.value) {
                if (!todosLosRequisitosCompletos()) {
                    mostrarToastGlobal('La contraseña no cumple con todos los requisitos requeridos', 'error');
                    inputPassword.focus();
                    return false;
                }
            }
            
            // Validar coincidencia de contraseñas
            if (!validarCoincidenciaContrasenas()) {
                const password = inputPassword.value;
                const passwordConfirm = inputPasswordConfirm.value;
                if (password || passwordConfirm) {
                    mostrarToastGlobal('Las contraseñas ingresadas no coinciden', 'error');
                    inputPasswordConfirm.focus();
                    return false;
                }
            }
            
            // Validar email si está presente
            if (inputEmail && inputEmail.value.trim()) {
                if (!validarEmail(inputEmail.value.trim())) {
                    mostrarToastGlobal('Por favor ingrese un email válido', 'error');
                    inputEmail.focus();
                    return false;
                }
            }
            
            // Validar cédula si está presente
            if (inputIdentificacion && inputIdentificacion.value.trim()) {
                if (!validarCedulaEcuatoriana(inputIdentificacion.value.trim())) {
                    mostrarToastGlobal('La cédula ecuatoriana ingresada no es válida', 'error');
                    inputIdentificacion.focus();
                    return false;
                }
            }
            
            // Obtener información del formulario
            const esEdicion = document.querySelector('input[name="usu_usuario_id"]') !== null;
            const accion = esEdicion ? 'guardar los cambios' : 'crear el usuario';
            const titulo = esEdicion ? '¿Editar usuario?' : '¿Crear usuario?';
            const descripcion = esEdicion 
                ? '¿Está seguro de guardar los cambios realizados en este usuario?' 
                : '¿Está seguro de crear este nuevo usuario?';
            
            // Mostrar confirmación con SweetAlert2
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: titulo,
                    html: descripcion,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: '<i class="fas fa-check mr-2"></i>' + (esEdicion ? 'Sí, actualizar' : 'Sí, crear'),
                    cancelButtonText: '<i class="fas fa-times mr-2"></i>Cancelar',
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: (modal) => {
                        // Agregar estilos personalizados si es necesario
                        const confirmBtn = modal.querySelector('.swal2-confirm');
                        const cancelBtn = modal.querySelector('.swal2-cancel');
                        
                        if (confirmBtn) {
                            confirmBtn.innerHTML = '<i class="fas fa-check mr-2"></i>' + (esEdicion ? 'Sí, actualizar' : 'Sí, crear');
                            confirmBtn.style.minWidth = '120px';
                        }
                        if (cancelBtn) {
                            cancelBtn.innerHTML = '<i class="fas fa-times mr-2"></i>Cancelar';
                            cancelBtn.style.minWidth = '120px';
                        }
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Usuario confirmó, enviar formulario
                        formEditarUsuario.submit();
                    }
                    // Si cancela, simplemente cierra el SweetAlert sin enviar
                });
            } else {
                // Fallback si SweetAlert2 no está disponible
                if (confirm('¿Está seguro de ' + accion + '?')) {
                    formEditarUsuario.submit();
                }
            }
        });
    }
    
    // ==========================================
    // 4. MOSTRAR TOAST AL CARGAR LA PÁGINA
    // ==========================================
    mostrarToastFlash();
    
    // ==========================================
    // 5. DETECTAR FORMULARIOS DE CREAR/EDITAR
    // ==========================================
    const todosLosFormularios = document.querySelectorAll('form[id*="formulario"], form#formEditarUsuario');
    
    todosLosFormularios.forEach(form => {
        // Los eventos se registran automáticamente arriba
    });
    
    // ==========================================
    // 6. MANEJO DE ERRORES DE VALIDACIÓN
    // ==========================================
    document.querySelectorAll('input[required], select[required], textarea[required]').forEach(campo => {
        campo.addEventListener('invalid', function(event) {
            event.preventDefault();
            // SweetAlert2 muestra el mensaje de validación HTML5
        });
    });
    
    // ==========================================
    // 7. MANEJO DE RESET PASSWORD CON SWEETALERT2
    // ==========================================
    document.querySelectorAll('[data-action="reset-password"]').forEach(boton => {
        boton.addEventListener('click', function(e) {
            e.preventDefault();
            
            const userId = this.getAttribute('data-user-id');
            const href = this.getAttribute('href');
            
            // Mostrar confirmación con SweetAlert2
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: '¿Restablecer contraseña?',
                    text: 'Se generará una nueva contraseña para este usuario. ¿Desea continuar?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Confirmar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Hacer petición AJAX al servidor
                        resetPasswordAjax(href);
                    }
                });
            } else {
                // Fallback si SweetAlert2 no está disponible
                if (confirm('¿Restablecer contraseña?')) {
                    window.location.href = href;
                }
            }
        });
    });
    
    // Función auxiliar para ejecutar reset password vía AJAX
    function resetPasswordAjax(url) {
        // Agregar parámetro para indicar AJAX
        const urlFinal = url + (url.indexOf('?') > -1 ? '&' : '?') + 'ajax=1';
        
        fetch(urlFinal, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({})
        })
        .then(response => {
            // Verificar el status HTTP
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            // Mostrar toast de éxito
            if (data.success) {
                mostrarToastGlobal(data.message || 'Contraseña restablecida correctamente', 'success');
                // Redirigir después de 1.5 segundos
                setTimeout(() => {
                    window.location.href = data.redirect || window.location.href.split('?')[0];
                }, 1500);
            } else {
                mostrarToastGlobal(data.message || 'Error al resetear contraseña', 'error');
            }
        })
        .catch(error => {
            console.error('Error en AJAX:', error);
            mostrarToastGlobal('Error al procesar la solicitud: ' + error.message, 'error');
        });
    }
    
    // ==========================================
    // 8. MANEJO DE ELIMINACIÓN CON SWEETALERT2
    // ==========================================
    document.querySelectorAll('[data-action="delete-user"]').forEach(boton => {
        boton.addEventListener('click', function(e) {
            e.preventDefault();
            
            const userId = this.getAttribute('data-user-id');
            const href = this.getAttribute('href');
            
            // Mostrar confirmación con SweetAlert2
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: '¿Eliminar usuario?',
                    text: 'Esta acción no se puede deshacer. ¿Desea continuar?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Hacer petición AJAX al servidor
                        deleteUserAjax(href);
                    }
                });
            } else {
                // Fallback si SweetAlert2 no está disponible
                if (confirm('¿Eliminar usuario?\nEsta acción no se puede deshacer.')) {
                    window.location.href = href;
                }
            }
        });
    });
    
    // Función auxiliar para ejecutar eliminación vía AJAX
    function deleteUserAjax(url) {
        // Agregar parámetro para indicar AJAX
        const urlFinal = url + (url.indexOf('?') > -1 ? '&' : '?') + 'ajax=1';
        
        fetch(urlFinal, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({})
        })
        .then(response => {
            // Verificar el status HTTP
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            // Mostrar toast de éxito o error
            if (data.success) {
                mostrarToastGlobal(data.message || 'Usuario eliminado correctamente', 'success');
                // Redirigir después de 1.5 segundos
                setTimeout(() => {
                    window.location.href = data.redirect || window.location.href.split('?')[0];
                }, 1500);
            } else {
                mostrarToastGlobal(data.message || 'Error al eliminar usuario', 'error');
            }
        })
        .catch(error => {
            console.error('Error en AJAX:', error);
            mostrarToastGlobal('Error al procesar la solicitud: ' + error.message, 'error');
        });
    }
    
});

/**
 * Función auxiliar: Mostrar toast manualmente si es necesario
 * Uso: mostrarToastGlobal('Mensaje de éxito', 'success')
 */
function mostrarToastGlobal(mensaje, tipo = 'success') {
    if (typeof Swal !== 'undefined') {
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer);
                toast.addEventListener('mouseleave', Swal.resumeTimer);
            }
        });
        
        const iconMap = {
            'success': 'success',
            'error': 'error',
            'warning': 'warning',
            'info': 'info'
        };
        
        Toast.fire({
            icon: iconMap[tipo] || 'info',
            title: mensaje
        });
    }
}
