<?php
// obtener_resenas.php
header('Content-Type: application/json');

$productoId = isset($_GET['producto_id']) ? $_GET['producto_id'] : '';

if (empty($productoId)) {
    echo json_encode([]);
    exit;
}

$archivoResenas = 'resenas.json';
$resenas = [];

if (file_exists($archivoResenas)) {
    $jsonContent = file_get_contents($archivoResenas);
    $todasResenas = json_decode($jsonContent, true) ?: [];
    
    // Filtrar reseñas por producto
    foreach ($todasResenas as $resena) {
        if ($resena['producto_id'] == $productoId) {
            $resenas[] = $resena;
        }
    }
}

echo json_encode($resenas);
?>