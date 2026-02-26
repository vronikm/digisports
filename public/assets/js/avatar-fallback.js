/**
 * DigiSports - Avatar Fallback Manager
 * 
 * Maneja el fallback automático de avatares cuando falla
 * la carga desde CDN externo (ui-avatars.com)
 * 
 * @author Senior Developer
 * @version 2.1.0
 */

document.addEventListener('DOMContentLoaded', function() {
    // Avatar por defecto codificado en base64 (PNG 32x32 con icono de usuario)
    // Indigo (#6366F1) con icono blanco
    const fallbackBase64 = 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAzMiAzMiIgd2lkdGg9IjMyIiBoZWlnaHQ9IjMyIj48Y2lyY2xlIGN4PSIxNiIgY3k9IjE2IiByPSIxNiIgZmlsbD0iIzYzNjZGMSIvPjxjaXJjbGUgY3g9IjE2IiBjeT0iMTAiIHI9IjQiIGZpbGw9IiNmZmZmZmYiLz48cGF0aCBkPSJNIDYgMjUgUSA2IDE4IDE2IDE4IFEgMjYgMTggMjYgMjUiIGZpbGw9IiNmZmZmZmYiLz48L3N2Zz4=';
    
    // Detectar la URL base correctamente
    const currentUrl = window.location.href;
    const baseUrl = window.location.origin;
    
    // Determinar la carpeta base del proyecto
    const pathParts = currentUrl.split('/');
    let projectBase = '';
    
    // Buscar la posición de 'digisports' en la URL
    const digisportsIndex = pathParts.indexOf('digisports');
    if (digisportsIndex !== -1) {
        projectBase = pathParts.slice(0, digisportsIndex + 1).join('/');
    } else {
        projectBase = pathParts.slice(0, 4).join('/');
    }
    
    // Construir la URL del fallback SVG (por si el base64 no funciona)
    const fallbackSvgUrl = baseUrl + projectBase + '/public/assets/images/avatar-default.svg';
    
    // Interceptar todas las imágenes con atributo data-fallback-avatar
    const avatarImages = document.querySelectorAll('img[data-fallback-avatar="true"]');
    
    avatarImages.forEach(img => {
        let attemptCount = 0;
        const maxAttempts = 3;
        
        // Evento cuando la imagen falla de cargar
        img.addEventListener('error', function() {
            attemptCount++;
            
            console.log('Avatar fallo para: ' + this.src + ' (intento ' + attemptCount + ')');
            
            if (attemptCount === 1) {
                // Primer intento fallido: usar base64 SVG
                this.src = fallbackBase64;
            } 
            else if (attemptCount === 2) {
                // Segundo intento fallido: intentar con el SVG local
                this.src = fallbackSvgUrl;
            }
            else if (attemptCount >= maxAttempts) {
                // Todos los intentos fallidos: usar estilo con color
                this.style.display = 'inline-flex';
                this.style.alignItems = 'center';
                this.style.justifyContent = 'center';
                this.style.backgroundColor = '#6366F1';
                this.style.color = '#fff';
                this.style.fontSize = '16px';
                this.textContent = '👤';
                // Prevenir más intentos de carga
                return;
            }
        }, { once: false });
    });
    
    console.log('✓ Avatar Fallback Manager inicializado. Imágenes monitoreadas: ' + avatarImages.length);
});

/**
 * Función auxiliar para generar URL del avatar de ui-avatars
 * @param {string} identifier - Nombre o email del usuario
 * @returns {string} URL del avatar
 */
function getAvatarUrl(identifier = 'Usuario') {
    return 'https://ui-avatars.com/api/?name=' + encodeURIComponent(identifier) + 
           '&background=6366F1&color=fff&size=32';
}

