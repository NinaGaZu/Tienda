<?php
/**
 * Funciones para manejo de reseñas
 */
function guardarResena(int $productoId, string $usuario, int $calificacion, string $comentario) {
    // Validar datos
    $validacion = validarResena($calificacion, $usuario, $comentario);
    if ($validacion !== true) {
        return $validacion; // Retorna mensaje de error
    }
    
    // Crear array con la reseña
    $nuevaResena = [
        'producto_id' => $productoId,
        'usuario' => htmlspecialchars(trim($usuario)),
        'calificacion' => (int)$calificacion,
        'comentario' => htmlspecialchars(trim($comentario)),
        'fecha' => date('Y-m-d H:i:s')
    ];
    
    // Leer reseñas existentes
    $archivo = 'resenas.json';
    $resenas = file_exists($archivo) ? json_decode(file_get_contents($archivo), true) : [];
    
    // Agregar nueva reseña
    $resenas[] = $nuevaResena;
    
    // Guardar en archivo JSON
    file_put_contents($archivo, json_encode($resenas, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    
    return true;
}
    
// FUNCIÓN DE VALIDACIÓN
function validarResena(int $calificacion, string $usuario, string $comentario) {
    if ($calificacion < 1 || $calificacion > 5) {
        return "Error: La calificación debe estar entre 1 y 5 estrellas";
    }
    if (empty($usuario) || empty($comentario)) {
        return "Error: Todos los campos son obligatorios";
    }
    return true;
}


function mostrarEstrellas(int $calificacion): string {
    $estrellas = "";
    for ($i = 1; $i <= 5; $i++) {
        if ($i <= $calificacion) {
            $estrellas .= "★";
        } else {
            $estrellas .= "☆";
        }
    }
    return $estrellas;
}
?>