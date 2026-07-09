<?php
/**
 * Gestión del carrito de compras usando sesiones PHP
 * IACC - Programación Web II - Semana 5
 * Autor: Gianina Gaete
 */

require_once __DIR__ . '/../config/sesion.php';

/**
 * Inicializar carrito en sesión si no existe
 */
function inicializarCarrito() {
    if (!isset($_SESSION['carrito'])) {
        $_SESSION['carrito'] = [];
    }
    if (!isset($_SESSION['carrito_total'])) {
        $_SESSION['carrito_total'] = 0;
    }
}

/**
 * Agregar producto al carrito
 * @param int|string $id Identificador del producto
 * @param string $nombre Nombre del producto
 * @param float $precio Precio del producto
 * @param int $cantidad Cantidad a agregar
 * @param int $stock Stock disponible en base de datos
 * @return array Resultado de la operación
 */
function agregarAlCarrito(int|string $id, string $nombre, float $precio, int $cantidad = 1, int $stock = 999, string $imagen = '') {
    inicializarCarrito();
    
    // Validar que los datos sean numéricos
    if (!is_numeric($id) || !is_numeric($precio) || !is_numeric($cantidad)) {
        return ['success' => false, 'message' => 'Datos inválidos del producto'];
    }
    
    // Validar que el stock sea válido (mayor a 0)
    // Esto previene errores si la base de datos envía un valor NULL o 0
    if ($stock === null || (int)$stock <= 0) {
        return ['success' => false, 'message' => 'El producto no tiene stock disponible'];
    }
    
    // Calcular cantidad actual en el carrito
    $cantidadActual = isset($_SESSION['carrito'][$id]) ? (int)$_SESSION['carrito'][$id]['cantidad'] : 0;
    
    // Validar que no se exceda el stock
    if (($cantidadActual + $cantidad) > (int)$stock) {
        return ['success' => false, 'message' => "Stock insuficiente. Solo quedan {$stock} unidades disponibles."];
    }
    
    // Si el producto ya existe en el carrito, actualizamos la cantidad
    if (isset($_SESSION['carrito'][$id])) {
        $_SESSION['carrito'][$id]['cantidad'] += $cantidad;
        $mensaje = "Se actualizó la cantidad de '{$nombre}' en el carrito";
    } else {
        // Si no existe, lo agregamos como nuevo
        $_SESSION['carrito'][$id] = [
            'id' => (int)$id,
            'nombre' => htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8'),
            'precio' => (float)$precio,
            'cantidad' => (int)$cantidad,
            'imagen' => $imagen
        ];
        $mensaje = "'{$nombre}' agregado al carrito exitosamente";
    }
    
    actualizarTotalCarrito();
    return ['success' => true, 'message' => $mensaje];
}

/**
 * Actualizar cantidad de un producto
 */
function actualizarCantidad(int|string $id, int $cantidad) {
    inicializarCarrito();
    
    if (!isset($_SESSION['carrito'][$id])) {
        return false;
    }
    
    if ($cantidad <= 0) {
        eliminarDelCarrito($id);
        return true;
    }
    
    $_SESSION['carrito'][$id]['cantidad'] = (int)$cantidad;
    actualizarTotalCarrito();
    return true;
}

/**
 * Eliminar producto del carrito
 */
function eliminarDelCarrito(int|string $id) {
    inicializarCarrito();
    
    if (!isset($_SESSION['carrito'][$id])) {
        return false;
    }
    
    unset($_SESSION['carrito'][$id]);
    actualizarTotalCarrito();
    return true;
}

/**
 * Vaciar carrito completamente
 */
function vaciarCarrito() {
    $_SESSION['carrito'] = [];
    $_SESSION['carrito_total'] = 0;
}

/**
 * Actualizar total del carrito
 */
function actualizarTotalCarrito() {
    $total = 0;
    foreach ($_SESSION['carrito'] as $producto) {
        $total += $producto['precio'] * $producto['cantidad'];
    }
    $_SESSION['carrito_total'] = $total;
}

/**
 * Obtener total del carrito
 */
function obtenerTotalCarrito() {
    return isset($_SESSION['carrito_total']) ? $_SESSION['carrito_total'] : 0;
}

/**
 * Obtener cantidad total de items
 */
function obtenerCantidadItems() {
    if (!isset($_SESSION['carrito'])) {
        return 0;
    }
    
    $cantidad = 0;
    foreach ($_SESSION['carrito'] as $producto) {
        $cantidad += $producto['cantidad'];
    }
    return $cantidad;
}

/**
 * Verificar si el carrito está vacío
 */
function carritoVacio() {
    return empty($_SESSION['carrito']);
}

/**
 * Obtener carrito completo
 */
function obtenerCarrito() {
    inicializarCarrito();
    return $_SESSION['carrito'];
}

/**
 * Calcular descuento (10% si supera $50000)
 */
function calcularDescuento() {
    $total = obtenerTotalCarrito();
    if ($total > 50000) {
        return $total * 0.10;
    }
    return 0;
}

/**
 * Calcular total con descuento y envío
 */
function calcularTotalFinal($costoEnvio = 3000) {
    $total = obtenerTotalCarrito();
    $descuento = calcularDescuento();
    return $total + $costoEnvio - $descuento;
}

/**
 * Obtener información completa del carrito para checkout
 * @param float $costoEnvio Costo de envío
 * @return array Información completa del carrito
 */
function obtenerInfoCompletaCarrito($costoEnvio = 3000) {
    inicializarCarrito();
    
    $carrito = obtenerCarrito();
    $subtotal = obtenerTotalCarrito();
    $descuento = calcularDescuento();
    $totalFinal = calcularTotalFinal($costoEnvio);
    $cantidadItems = obtenerCantidadItems();
    
    return [
        'productos' => $carrito,
        'subtotal' => $subtotal,
        'descuento' => $descuento,
        'envio' => $costoEnvio,
        'total' => $totalFinal,
        'cantidad_items' => $cantidadItems,
        'vacio' => carritoVacio()
    ];
}
?>