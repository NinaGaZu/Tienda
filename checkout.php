<?php
/**
 * Página de checkout - Proceso de pago
 * IACC - Programación Web II - Semana 5
 * Autor: [Tu Nombre]
 */

$pageTitle = "Finalizar Compra";
require_once 'includes/header.php';
require_once 'includes/carrito_session.php';

// Verificar que haya productos en el carrito
if (carritoVacio()) {
    setNotification("Tu carrito está vacío. Agrega productos antes de continuar.", "error");
    header("Location: carrito.php");
    exit;
}

// Regenerar ID de sesión antes del pago (seguridad crítica)
session_regenerate_id(true);

// Obtener información del carrito
$infoCarrito = obtenerInfoCompletaCarrito(3000);

// Procesar pago
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar CSRF
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = "Error de seguridad. Intente nuevamente.";
    } else {
        // Validar datos del formulario
        $nombre = htmlspecialchars(trim($_POST['nombre']));
        $email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);
        $telefono = htmlspecialchars(trim($_POST['telefono']));
        $direccion = htmlspecialchars(trim($_POST['direccion']));
        $ciudad = htmlspecialchars(trim($_POST['ciudad']));
        $metodo_pago = htmlspecialchars(trim($_POST['metodo_pago']));
        
        if (empty($nombre) || !$email || empty($telefono) || empty($direccion) || empty($ciudad) || empty($metodo_pago)) {
            $error = "Todos los campos son obligatorios";
        } else {
            // Guardar información del pedido en sesión
            $_SESSION['pedido'] = [
                'nombre' => $nombre,
                'email' => $email,
                'telefono' => $telefono,
                'direccion' => $direccion,
                'ciudad' => $ciudad,
                'metodo_pago' => $metodo_pago,
                'productos' => $infoCarrito['productos'],
                'subtotal' => $infoCarrito['subtotal'],
                'descuento' => $infoCarrito['descuento'],
                'envio' => $infoCarrito['envio'],
                'total' => $infoCarrito['total'],
                'fecha' => date('Y-m-d H:i:s'),
                'id_pedido' => 'PED-' . date('Ymd') . '-' . strtoupper(substr(md5(uniqid()), 0, 6))
            ];
            
            // Aquí se procesaría el pago real con pasarela de pago
            // Por ahora, solo simulamos el proceso exitoso
            
            // Vaciar carrito después de "comprar"
            vaciarCarrito();
            
            // Redirigir a confirmación
            header("Location: confirmacion.php");
            exit;
        }
    }
}
?>

<div class="container">
    <h1 class="checkout-title">
        <span class="title-icon">💳</span>
        Finalizar Compra
    </h1>
    
    <div class="checkout-wrapper">
        <!-- Formulario -->
        <div class="checkout-form-section">
            <h2>Información de Envío y Pago</h2>
            
            <form method="POST" action="checkout.php">
                <input type="hidden" name="csrf_token" value="<?php echo getCsrfToken(); ?>">
                
                <!-- Datos Personales -->
                <div class="form-section">
                    <h3 class="form-section-title"> Datos Personales</h3>
                    
                    <div class="form-group">
                        <label for="nombre">Nombre Completo: <span class="required">*</span></label>
                        <input type="text" id="nombre" name="nombre" required placeholder="Ej: Juan Pérez">
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="email">Correo Electrónico: <span class="required">*</span></label>
                            <input type="email" id="email" name="email" required placeholder="ejemplo@correo.com">
                        </div>
                        
                        <div class="form-group">
                            <label for="telefono">Teléfono: <span class="required">*</span></label>
                            <input type="tel" id="telefono" name="telefono" required placeholder="+56 9 1234 5678">
                        </div>
                    </div>
                </div>
                
                <!-- Dirección de Envío -->
                <div class="form-section">
                    <h3 class="form-section-title">📍 Dirección de Envío</h3>
                    
                    <div class="form-group">
                        <label for="direccion">Dirección: <span class="required">*</span></label>
                        <textarea id="direccion" name="direccion" required placeholder="Calle, número, departamento, etc."></textarea>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="ciudad">Ciudad: <span class="required">*</span></label>
                            <input type="text" id="ciudad" name="ciudad" required placeholder="Ej: Santiago">
                        </div>
                        
                        <div class="form-group">
                            <label for="metodo_pago">Método de Pago: <span class="required">*</span></label>
                            <select id="metodo_pago" name="metodo_pago" required>
                                <option value="">-- Seleccione --</option>
                                <option value="tarjeta">Tarjeta de Crédito/Débito</option>
                                <option value="transferencia">Transferencia Bancaria</option>
                                <option value="paypal">PayPal</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <!-- Términos -->
                <div class="terms-group">
                    <input type="checkbox" id="terminos" name="terminos" required>
                    <label for="terminos">
                        Acepto los <a href="terminos.php">términos y condiciones</a> y la <a href="privacidad.php">política de privacidad</a> *
                    </label>
                </div>
                
                <!-- Botones -->
                <button type="submit" class="btn-pay">
                    🔒 Pagar $<?php echo number_format($infoCarrito['total'], 0, ',', '.'); ?>
                </button>
                
                <a href="carrito.php" class="btn-back">← Volver al carrito</a>
            </form>
        </div>
        
        <!-- Resumen del Pedido -->
        <aside class="checkout-summary">
            <div class="summary-card">
                <h2 class="summary-title">📋 Resumen del Pedido</h2>
                
                <!-- Lista de productos -->
                <div class="summary-products-list">
                    <?php foreach ($infoCarrito['productos'] as $producto): ?>
                        <div class="summary-product-item">
                            <div class="summary-product-info">
                                <div class="summary-product-name">
                                    <?php echo htmlspecialchars($producto['nombre']); ?>
                                </div>
                                <div class="summary-product-qty">
                                    x<?php echo $producto['cantidad']; ?>
                                </div>
                            </div>
                            <div class="summary-product-price">
                                $<?php echo number_format($producto['precio'] * $producto['cantidad'], 0, ',', '.'); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Totales -->
                <div class="summary-details">
                    <div class="summary-row">
                        <span>Subtotal:</span>
                        <span class="summary-value">$<?php echo number_format($infoCarrito['subtotal'], 0, ',', '.'); ?></span>
                    </div>
                    
                    <?php if ($infoCarrito['descuento'] > 0): ?>
                        <div class="summary-row discount">
                            <span>Descuento (10%):</span>
                            <span class="summary-value text-success">-$<?php echo number_format($infoCarrito['descuento'], 0, ',', '.'); ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <div class="summary-row">
                        <span>Envío:</span>
                        <span class="summary-value">$<?php echo number_format($infoCarrito['envio'], 0, ',', '.'); ?></span>
                    </div>
                    
                    <div class="summary-divider"></div>
                    
                    <div class="summary-row total">
                        <span>TOTAL:</span>
                        <span class="total-amount">$<?php echo number_format($infoCarrito['total'], 0, ',', '.'); ?></span>
                    </div>
                    
                    <?php if ($infoCarrito['descuento'] > 0): ?>
                        <div class="savings-badge">
                            ✨ Ahorraste $<?php echo number_format($infoCarrito['descuento'], 0, ',', '.'); ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Badge de seguridad -->
                <div class="secure-checkout-badge">
                    <div class="lock-icon">🔒</div>
                    <p>Pago 100% seguro</p>
                    <small>Sus datos están protegidos con encriptación SSL</small>
                </div>
            </div>
        </aside>
    </div>
</div>
<?php require_once 'includes/footer.php'; ?>