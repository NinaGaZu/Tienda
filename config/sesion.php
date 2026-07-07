<?php
/**
 * Configuración centralizada de sesiones seguras
 * IACC - Programación Web II - Semana 5
 * Autor: Gianina Gaete
 */

// Detectar si la conexión es HTTPS (soporta también proxies/balanceadores comunes)
$esHttps = (
    (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
    (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') ||
    ($_SERVER['SERVER_PORT'] ?? '') == 443
);

// Configuración de tiempo de vida de la sesión en el servidor (debe ser >= al lifetime de la cookie)
// Evita que la sesión sea recolectada como basura antes de que expire la cookie del navegador
ini_set('session.gc_maxlifetime', 3600);

// Configurar parámetros de cookie segura ANTES de iniciar sesión
session_set_cookie_params([
    'lifetime' => 3600,
    'path' => '/',
    'domain' => $_SERVER['HTTP_HOST'],
    'secure' => $esHttps,   // true en HTTPS (obligatorio en producción); false solo permite pruebas en localhost sin HTTPS
    'httponly' => true,    // Prevenir XSS
    'samesite' => 'Strict' // Prevenir CSRF
]);

// Iniciar sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Generar token CSRF si no existe
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Control de última actividad (30 minutos de inactividad)
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 1800)) {
    session_unset();
    session_destroy();
    session_start();
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$_SESSION['LAST_ACTIVITY'] = time();

// Regenerar ID de sesión cada 30 minutos
if (!isset($_SESSION['CREATED'])) {
    $_SESSION['CREATED'] = time();
} else if (time() - $_SESSION['CREATED'] > 1800) {
    session_regenerate_id(true);
    $_SESSION['CREATED'] = time();
}

// Regenerar al iniciar sesión o durante el checkout
if (isset($_POST['login']) || isset($_POST['finalizar_compra'])) {
    session_regenerate_id(true);
}

/**
 * Función para obtener token CSRF
 * @return string
 */
function getCsrfToken() {
    return $_SESSION['csrf_token'];
}

/**
 * Función para validar token CSRF
 * @param string $token
 * @return boolean
 */
function validateCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Función para mostrar mensajes de notificación
 * @param string $mensaje
 * @param string $tipo (success, error, warning)
 */
function setNotification($mensaje, $tipo = 'success') {
    $_SESSION['notification'] = [
        'message' => $mensaje,
        'type' => $tipo
    ];
}

/**
 * Función para obtener y limpiar notificación
 * @return array|null
 */
function getNotification() {
    if (isset($_SESSION['notification'])) {
        $notification = $_SESSION['notification'];
        unset($_SESSION['notification']);
        return $notification;
    }
    return null;
}
?>