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
                    // actualizarCantidad puede devolver true/false o un array con 'message' y 'success'
                    if (is_array($resultado)) {
                        setNotification($resultado['message'], $resultado['success'] ? 'success' : 'error');
                    } else {
                        // Resultado booleano: usar mensajes por defecto
                        if ($resultado) {
                            setNotification('Cantidad actualizada correctamente.', 'success');
                        } else {
                            setNotification('No se pudo actualizar la cantidad del producto.', 'error');
                        }
                    }
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

<!-- En carrito.php, reemplaza la sección del carrito con: -->
<div class="container">
    <h1 class="page-title">
        <span class="title-icon">🛒</span>
        Carrito de Compras
    </h1>
    
    <?php if (carritoVacio()): ?>
        <!-- Carrito vacío mejorado -->
        <div class="empty-cart">
            <div class="empty-cart-icon">🛒</div>
            <h2>Tu carrito está vacío</h2>
            <p>¡Agrega algunos productos para comenzar!</p>
            <a href="index.php#productos" class="btn btn-primary btn-lg">
                <span>🛍️</span> Ver Productos
            </a>
        </div>
    <?php else: ?>
        <div class="cart-wrapper">
            <!-- Productos del carrito -->
            <div class="cart-items-section">
                <h2 class="section-title">Productos en tu carrito</h2>
                
                <form method="POST" action="carrito.php" class="cart-form">
                    <input type="hidden" name="csrf_token" value="<?php echo getCsrfToken(); ?>">
                    
                    <div class="cart-products">
                        <?php foreach (obtenerCarrito() as $producto): ?>
                            <div class="cart-product-card">
                                <div class="product-image-container">
                                    <img src="https://via.placeholder.com/120x120?text=Producto" 
                                         alt="<?php echo htmlspecialchars($producto['nombre']); ?>"
                                         class="product-image">
                                </div>
                                
                                <div class="product-details">
                                    <h3 class="product-name"><?php echo htmlspecialchars($producto['nombre']); ?></h3>
                                    <p class="product-price-unit">$<?php echo number_format($producto['precio'], 0, ',', '.'); ?> c/u</p>
                                </div>
                                
                                <div class="product-quantity">
                                    <label for="qty-<?php echo $producto['id']; ?>">Cantidad:</label>
                                    <div class="quantity-control">
                                        <button type="button" class="qty-btn qty-minus" onclick="updateQuantity(<?php echo $producto['id']; ?>, -1)">−</button>
                                        <input type="number" 
                                               id="qty-<?php echo $producto['id']; ?>"
                                               name="cantidad" 
                                               value="<?php echo $producto['cantidad']; ?>" 
                                               min="1" 
                                               max="10"
                                               class="quantity-input"
                                               onchange="updateQuantity(<?php echo $producto['id']; ?>, 0)">
                                        <button type="button" class="qty-btn qty-plus" onclick="updateQuantity(<?php echo $producto['id']; ?>, 1)">+</button>
                                    </div>
                                    <input type="hidden" name="producto_id" value="<?php echo $producto['id']; ?>">
                                </div>
                                
                                <div class="product-subtotal">
                                    <span class="subtotal-label">Subtotal:</span>
                                    <span class="subtotal-amount">$<?php echo number_format($producto['precio'] * $producto['cantidad'], 0, ',', '.'); ?></span>
                                </div>
                                
                                <div class="product-actions">
                                    <button type="submit" 
                                            name="accion" 
                                            value="eliminar"
                                            class="btn-remove"
                                            onclick="return confirm('¿Eliminar este producto?')"
                                            title="Eliminar producto">
                                        🗑️
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </form>
            </div>

            <!-- Resumen del pedido -->
            <aside class="cart-summary">
                <div class="summary-card">
                    <h2 class="summary-title">Resumen del Pedido</h2>
                    
                    <div class="summary-details">
                        <div class="summary-row">
                            <span>Subtotal:</span>
                            <span class="summary-value">$<?php echo number_format($total, 0, ',', '.'); ?></span>
                        </div>
                        
                        <?php if ($descuento > 0): ?>
                            <div class="summary-row discount">
                                <span>Descuento (10%):</span>
                                <span class="summary-value text-success">-$<?php echo number_format($descuento, 0, ',', '.'); ?></span>
                            </div>
                        <?php endif; ?>
                        
                        <div class="summary-row">
                            <span>Envío:</span>
                            <span class="summary-value">$<?php echo number_format($costoEnvio, 0, ',', '.'); ?></span>
                        </div>
                        
                        <div class="summary-divider"></div>
                        
                        <div class="summary-row total">
                            <strong>TOTAL:</strong>
                            <strong class="total-amount">$<?php echo number_format($totalFinal, 0, ',', '.'); ?></strong>
                        </div>
                        
                        <?php if ($descuento > 0): ?>
                            <div class="savings-badge">
                                ✨ Ahorraste $<?php echo number_format($descuento, 0, ',', '.'); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="summary-actions">
                        <form method="POST" action="carrito.php" class="form-inline">
                            <input type="hidden" name="csrf_token" value="<?php echo getCsrfToken(); ?>">
                            <input type="hidden" name="accion" value="vaciar">
                            <button type="submit" class="btn btn-secondary btn-block" 
                                    onclick="return confirm('¿Vaciar carrito completamente?')">
                                🗑️ Vaciar Carrito
                            </button>
                        </form>
                        
                        <a href="checkout.php" class="btn btn-primary btn-block btn-lg btn-checkout">
                            💳 Proceder al Pago
                        </a>
                        
                        <a href="index.php#productos" class="btn btn-link btn-block text-center">
                            ← Continuar comprando
                        </a>
                    </div>
                    
                    <div class="secure-badge">
                        <span>🔒</span> Pago 100% seguro y encriptado
                    </div>
                </div>
            </aside>
        </div>
    <?php endif; ?>
</div>

<!-- JavaScript para actualizar cantidad -->
<script>
function updateQuantity(productId, change) {
    const input = document.getElementById('qty-' + productId);
    let newValue = parseInt(input.value) + change;
    
    if (change === 0) {
        newValue = parseInt(input.value);
    }
    
    if (newValue < 1) newValue = 1;
    if (newValue > 10) newValue = 10;
    
    if (change !== 0) {
        input.value = newValue;
        // Aquí podrías hacer un fetch para actualizar sin recargar
        setTimeout(() => {
            input.form.querySelector('button[type="submit"][name="accion"][value="actualizar"]').click();
        }, 300);
    }
}
</script>

<?php require_once 'includes/footer.php'; ?>