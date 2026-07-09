<?php
/**
 * Procesamiento de datos de productos
 */
require_once '../config/database.php';

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar método de solicitud
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recibir y sanitizar datos
    $nombre = trim($_POST['nombre'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $precio = floatval($_POST['precio'] ?? 0);
    $stock = intval($_POST['stock'] ?? 0);
    $categoria = trim($_POST['categoria'] ?? '');
    $imagen = trim($_POST['imagen'] ?? '');

    // Validaciones del lado del servidor
    $errores = [];

    if (empty($nombre) || strlen($nombre) < 3) {
        $errores[] = "El nombre debe tener al menos 3 caracteres";
    }

    if (empty($descripcion)) {
        $errores[] = "La descripción es obligatoria";
    }

    if ($precio <= 0) {
        $errores[] = "El precio debe ser mayor a 0";
    }

    if ($stock < 0) {
        $errores[] = "El stock no puede ser negativo";
    }

    if (empty($categoria)) {
        $errores[] = "La categoría es obligatoria";
    }

    // Si hay errores, redirigir con mensaje
    if (!empty($errores)) {
        $_SESSION['errores'] = $errores;
        header("Location: productos_form.html");
        exit;
    }

    try {
        // Crear conexión
        $db = new Database();
        $conn = $db->getConnection();

        // Imagen por defecto si está vacía
        if (empty($imagen)) {
            $imagen = 'https://via.placeholder.com/400x300?text=Sin+Imagen';
        }

        // Preparar consulta SQL con prepared statement
        $sql = "INSERT INTO PRODUCTO (nombre, descripcion, precio, stock, categoria, imagen) 
                VALUES (?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssdiss", $nombre, $descripcion, $precio, $stock, $categoria, $imagen);

        // Ejecutar y verificar
        if ($stmt->execute()) {
            $_SESSION['exito'] = "Producto '{$nombre}' registrado correctamente";
            header("Location: listar_productos.php");
        } else {
            throw new Exception("Error al registrar: " . $conn->error);
        }

        // Cerrar recursos
        $stmt->close();
        $db->close();

    } catch (Exception $e) {
        $_SESSION['errores'] = ["Error del servidor: " . $e->getMessage()];
        header("Location: productos_form.html");
        exit;
    }
} else {
    // Si no es POST, redirigir
    header("Location: ../index.php");
    exit;
}
?>