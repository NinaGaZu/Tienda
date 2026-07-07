/**
 * Script para mantener la sesión activa
 * IACC - Programación Web II - Semana 5
 * Autor: Gianina Gaete
 */

/**
 * Mantiene la sesión activa enviando peticiones periódicas al servidor
 */
function keepSessionAlive() {
    // Verificar que existan los elementos necesarios
    const mainContent = document.querySelector('main');
    
    if (!mainContent) {
        console.log('No se encontró el elemento <main>, keep-alive no es necesario');
        return;
    }
    
    // Enviar petición cada 15 minutos (900000 ms)
    const interval = setInterval(function() {
        fetch('keep_alive.php')
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data && data.success) {
                    console.log('[' + new Date().toLocaleTimeString() + '] Sesión renovada correctamente');
                }
            })
            .catch(error => {
                // Silenciar errores en producción
                // console.error('Error en keep-alive:', error);
            });
    }, 900000); // 15 minutos
    
    // Guardar el intervalo para poder detenerlo si es necesario
    window.sessionKeepAliveInterval = interval;
    
    console.log('Keep-alive iniciado: sesión se renovará cada 15 minutos');
}

// Iniciar keep-alive cuando el DOM esté cargado
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function() {
        // Solo iniciar en páginas donde el usuario pueda estar activo
        if (document.querySelector('main')) {
            keepSessionAlive();
        }
    });
} else {
    // El DOM ya está cargado
    if (document.querySelector('main')) {
        keepSessionAlive();
    }
}

// Detener keep-alive al cerrar la página (opcional)
window.addEventListener('beforeunload', function() {
    if (window.sessionKeepAliveInterval) {
        clearInterval(window.sessionKeepAliveInterval);
    }
});