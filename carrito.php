<?php
/**
 * Página del carrito de compras
 * IACC - Programación Web II - Semana 5
 * Autor: Gianina Gaete
 */

$pageTitle = "Carrito de Compras";
require_once 'includes/header.php';
require_once 'includes/carrito_session.php';

// Inicializar carrito
inicializarCarrito();

// Procesar acciones del carrito
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        setNotification("Error de seguridad. Intente nuevamente.", "error");
    } else {
        if (isset($_POST['accion'])) {
            $id = (int)$_POST['producto_id'];
            
            switch ($_POST['accion']) {
                case 'actualizar':
                    $cantidad = (int)$_POST['cantidad'];
                    $resultado = actualizarCantidad($id, $cantidad);
                    setNotification($resultado['message'], $resultado['success'] ? 'success' : 'error');
                    break;
                    
                case 'eliminar':
                    if (eliminarDelCarrito($id)) {
                        setNotification("Producto eliminado del carrito", "success");
                    }
                    break;
                    
                case 'vaciar':
                    vaciarCarrito();
                    setNotification("Carrito vaciado", "success");
                    break;
            }
        }
    }
    
    header("Location: carrito.php");
    exit;
}

// Calcular totales
$total = obtenerTotalCarrito();
$descuento = calcularDescuento();
$costoEnvio = 3000; // Envío normal
$totalFinal = calcularTotalFinal($costoEnvio);
?>

<div class="container">
    <h1 class="page-title">🛒 Carrito de Compras</h1>
    
    <?php if (carritoVacio()): ?>
        <div class="carrito-vacio">
            <div class="empty-cart-icon">🛒</div>
            <h2>Tu carrito está vacío</h2>
            <p>¡Agrega algunos productos para comenzar!</p>
            <a href="index.php#productos" class="btn btn-primary">Ver Productos</a>
        </div>
    <?php else: ?>
        <div class="carrito-container">
            <section class="carrito-items">
                <h2>Productos en tu carrito</h2>
                
                <form method="POST" action="carrito.php" class="carrito-form">
                    <input type="hidden" name="csrf_token" value="<?php echo getCsrfToken(); ?>">
                    
                    <div class="carrito-products-list">
                        <?php foreach (obtenerCarrito() as $producto): ?>
                            <div class="carrito-item">
                                <div class="item-info">
                                    <h3><?php echo $producto['nombre']; ?></h3>
                                    <p class="item-price">$<?php echo number_format($producto['precio'], 0, ',', '.'); ?> c/u</p>
                                </div>
                                
                                <div class="item-quantity">
                                    <label for="qty-<?php echo $producto['id']; ?>">Cantidad:</label>
                                    <input type="number" 
                                           id="qty-<?php echo $producto['id']; ?>"
                                           name="cantidad" 
                                           value="<?php echo $producto['cantidad']; ?>" 
                                           min="1" 
                                           max="10"
                                           class="quantity-input">
                                    <button type="submit" 
                                            name="accion" 
                                            value="actualizar"
                                            class="btn btn-small btn-update">
                                            Actualizar
                                    </button>
                                    <input type="hidden" name="producto_id" value="<?php echo $producto['id']; ?>">
                                </div>
                                
                                <div class="item-subtotal">
                                    <span>Subtotal:</span>
                                    <strong>$<?php echo number_format($producto['precio'] * $producto['cantidad'], 0, ',', '.'); ?></strong>
                                </div>
                                
                                <div class="item-actions">
                                    <button type="submit" 
                                            name="accion" 
                                            value="eliminar"
                                            class="btn btn-small btn-delete"
                                            onclick="return confirm('¿Eliminar este producto?')">
                                            🗑️ Eliminar
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </form>
            </section>

            <aside class="carrito-resumen">
                <h2>Resumen del Pedido</h2>
                
                <div class="resumen-details">
                    <div class="resumen-row">
                        <span>Subtotal:</span>
                        <span>$<?php echo number_format($total, 0, ',', '.'); ?></span>
                    </div>
                    
                    <?php if ($descuento > 0): ?>
                        <div class="resumen-row descuento">
                            <span>Descuento (10%):</span>
                            <span>-$<?php echo number_format($descuento, 0, ',', '.'); ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <div class="resumen-row">
                        <span>Envío:</span>
                        <span>$<?php echo number_format($costoEnvio, 0, ',', '.'); ?></span>
                    </div>
                    
                    <div class="resumen-row total">
                        <strong>TOTAL:</strong>
                        <strong>$<?php echo number_format($totalFinal, 0, ',', '.'); ?></strong>
                    </div>
                </div>
                
                <div class="resumen-actions">
                    <form method="POST" action="carrito.php" style="display: inline;">
                        <input type="hidden" name="csrf_token" value="<?php echo getCsrfToken(); ?>">
                        <input type="hidden" name="accion" value="vaciar">
                        <button type="submit" class="btn btn-secondary btn-block" 
                                onclick="return confirm('¿Vaciar carrito completamente?')">
                            🗑️ Vaciar Carrito
                        </button>
                    </form>
                    
                    <a href="checkout.php" class="btn btn-primary btn-block btn-large">
                        💳 Proceder al Pago
                    </a>
                    
                    <a href="index.php#productos" class="btn btn-link">
                        ← Continuar comprando
                    </a>
                </div>
            </aside>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>