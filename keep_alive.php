<?php
/**
 * Script para mantener la sesión activa (AJAX keep-alive)
 * IACC - Programación Web II - Semana 5
 * Autor: [Tu Nombre]
 */

require_once 'config/sesion.php';

header('Content-Type: application/json');

// Actualizar timestamp de última actividad
$_SESSION['LAST_ACTIVITY'] = time();

// Regenerar ID de sesión periódicamente (cada 30 minutos)
if (!isset($_SESSION['CREATED'])) {
    $_SESSION['CREATED'] = time();
} else if (time() - $_SESSION['CREATED'] > 1800) {
    session_regenerate_id(true);
    $_SESSION['CREATED'] = time();
}

// Retornar respuesta JSON
echo json_encode([
    'success' => true,
    'message' => 'Sesión activa',
    'timestamp' => time(),
    'session_id' => session_id()
]);
?>