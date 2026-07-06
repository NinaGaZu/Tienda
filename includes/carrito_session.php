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
 * @param int $id ID del producto
 * @param string $nombre Nombre del producto
 * @param float $precio Precio unitario
 * @param int $cantidad Cantidad a agregar
 * @param int $stock Stock disponible
 * @return array ['success' => bool, 'message' => string]
 */
function agregarAlCarrito($id, $nombre, $precio, $cantidad = 1, $stock = 999) {
    inicializarCarrito();
    
    // Validar datos
    if (!is_numeric($id) || !is_numeric($precio) || !is_numeric($cantidad)) {
        return ['success' => false, 'message' => 'Datos inválidos'];
    }
    
    // Verificar stock
    $cantidadActual = isset($_SESSION['carrito'][$id]) ? $_SESSION['carrito'][$id]['cantidad'] : 0;
    if (($cantidadActual + $cantidad) > $stock) {
        return ['success' => false, 'message' => 'Stock insuficiente'];
    }
    
    // Si el producto ya existe, aumentar cantidad
    if (isset($_SESSION['carrito'][$id])) {
        $_SESSION['carrito'][$id]['cantidad'] += $cantidad;
        $mensaje = "Cantidad actualizada de {$nombre}";
    } else {
        // Agregar nuevo producto
        $_SESSION['carrito'][$id] = [
            'id' => (int)$id,
            'nombre' => htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8'),
            'precio' => (float)$precio,
            'cantidad' => (int)$cantidad,
            'stock' => (int)$stock, // Se guarda para validar futuras actualizaciones de cantidad
            'imagen' => '' // Se puede agregar después
        ];
        $mensaje = "{$nombre} agregado al carrito";
    }
    
    actualizarTotalCarrito();
    return ['success' => true, 'message' => $mensaje];
}

/**
 * Actualizar cantidad de un producto
 * @param int $id ID del producto
 * @param int $cantidad Nueva cantidad
 * @return array ['success' => bool, 'message' => string]
 */
function actualizarCantidad($id, $cantidad) {
    inicializarCarrito();

    if (!is_numeric($cantidad)) {
        return ['success' => false, 'message' => 'Cantidad inválida'];
    }

    if (!isset($_SESSION['carrito'][$id])) {
        return ['success' => false, 'message' => 'Producto no encontrado en el carrito'];
    }

    if ($cantidad <= 0) {
        eliminarDelCarrito($id);
        return ['success' => true, 'message' => 'Producto eliminado del carrito'];
    }

    // Validar contra el stock guardado al momento de agregar el producto
    $stockDisponible = $_SESSION['carrito'][$id]['stock'] ?? 999;
    if ($cantidad > $stockDisponible) {
        return ['success' => false, 'message' => "Solo hay {$stockDisponible} unidades disponibles"];
    }

    $_SESSION['carrito'][$id]['cantidad'] = (int)$cantidad;
    actualizarTotalCarrito();
    return ['success' => true, 'message' => 'Carrito actualizado'];
}

/**
 * Eliminar producto del carrito
 * @param int $id ID del producto
 * @return boolean
 */
function eliminarDelCarrito($id) {
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
 * @return float
 */
function obtenerTotalCarrito() {
    return isset($_SESSION['carrito_total']) ? $_SESSION['carrito_total'] : 0;
}

/**
 * Obtener cantidad total de items
 * @return int
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
 * @return boolean
 */
function carritoVacio() {
    return empty($_SESSION['carrito']);
}

/**
 * Obtener carrito completo
 * @return array
 */
function obtenerCarrito() {
    inicializarCarrito();
    return $_SESSION['carrito'];
}

/**
 * Calcular descuento (10% si supera $50000)
 * @return float
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
 * @param float $costoEnvio
 * @return float
 */
function calcularTotalFinal($costoEnvio = 3000) {
    $total = obtenerTotalCarrito();
    $descuento = calcularDescuento();
    return $total + $costoEnvio - $descuento;
}

/**
 * Obtener información completa del carrito para checkout
 * @param float $costoEnvio
 * @return array
 */
function obtenerInfoCompletaCarrito($costoEnvio = 3000) {
    return [
        'productos' => obtenerCarrito(),
        'subtotal' => obtenerTotalCarrito(),
        'descuento' => calcularDescuento(),
        'envio' => $costoEnvio,
        'total' => calcularTotalFinal($costoEnvio),
        'items' => obtenerCantidadItems()
    ];
}
?>