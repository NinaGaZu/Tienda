<?php
/**
 * Procesa las reseñas de productos
 * Recibe datos vía POST y los valida
 */

// Incluir la función de validación
require_once 'reviews.php';

// Configurar respuesta JSON
header('Content-Type: application/json');

// Verificar que sea método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

// Recuperar datos del formulario
$productoId = isset($_POST['producto_id']) ? $_POST['producto_id'] : '';
$usuario = isset($_POST['usuario']) ? $_POST['usuario'] : '';
$calificacion = isset($_POST['calificacion']) ? $_POST['calificacion'] : '';
$comentario = isset($_POST['comentario']) ? $_POST['comentario'] : '';

// Validar que todos los campos estén presentes
if (empty($productoId) || empty($usuario) || empty($calificacion) || empty($comentario)) {
    echo json_encode(['success' => false, 'message' => 'Todos los campos son obligatorios']);
    exit;
}

// Llamar a la función guardarResena() que ya creaste
$resultado = guardarResena($productoId, $usuario, $calificacion, $comentario);

// Verificar si la función retornó un error o éxito
if (is_string($resultado) && strpos($resultado, 'Error') === 0) {
    // Es un mensaje de error
    echo json_encode(['success' => false, 'message' => $resultado]);
} else {
    // Éxito - la reseña se guardó
    // En un caso real, aquí guardarías en base de datos
    
    // Guardar en archivo JSON (simulación de base de datos)
    $archivoResenas = 'resenas.json';
    
    // Leer reseñas existentes
    $resenasExistentes = [];
    if (file_exists($archivoResenas)) {
        $jsonContent = file_get_contents($archivoResenas);
        $resenasExistentes = json_decode($jsonContent, true) ?: [];
    }
    
    // Agregar nueva reseña
    $nuevaResena = [
        'producto_id' => $productoId,
        'usuario' => htmlspecialchars($usuario),
        'calificacion' => (int)$calificacion,
        'comentario' => htmlspecialchars($comentario),
        'fecha' => date('Y-m-d H:i:s')
    ];
    
    $resenasExistentes[] = $nuevaResena;
    
    // Guardar en archivo
    file_put_contents($archivoResenas, json_encode($resenasExistentes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    
    // Retornar éxito
    echo json_encode([
        'success' => true, 
        'message' => 'Reseña guardada exitosamente',
        'resena' => $nuevaResena
    ]);
}
?>