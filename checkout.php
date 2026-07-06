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
    <h1 class="page-title">💳 Finalizar Compra</h1>
    
    <?php if (isset($error)): ?>
        <div class="notification notification-error">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>
    
    <div class="checkout-container">
        <section class="checkout-form-section">
            <h2>Información de Envío y Pago</h2>
            
            <form method="POST" action="checkout.php" class="checkout-form">
                <input type="hidden" name="csrf_token" value="<?php echo getCsrfToken(); ?>">
                
                <fieldset>
                    <legend>Datos Personales</legend>
                    
                    <div class="form-group">
                        <label for="nombre">Nombre Completo: *</label>
                        <input type="text" id="nombre" name="nombre" 
                               value="<?php echo $_POST['nombre'] ?? ''; ?>" 
                               required 
                               pattern="[A-Za-zÁáÉéÍíÓóÚúÑñ\s]{3,50}"
                               placeholder="Ej: Juan Pérez">
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="email">Correo Electrónico: *</label>
                            <input type="email" id="email" name="email" 
                                   value="<?php echo $_POST['email'] ?? ''; ?>" 
                                   required 
                                   placeholder="ejemplo@correo.com">
                        </div>
                        
                        <div class="form-group">
                            <label for="telefono">Teléfono: *</label>
                            <input type="tel" id="telefono" name="telefono" 
                                   value="<?php echo $_POST['telefono'] ?? ''; ?>" 
                                   required 
                                   pattern="[0-9+\s]{8,15}"
                                   placeholder="+56 9 1234 5678">
                        </div>
                    </div>
                </fieldset>
                
                <fieldset>
                    <legend>Dirección de Envío</legend>
                    
                    <div class="form-group">
                        <label for="direccion">Dirección: *</label>
                        <textarea id="direccion" name="direccion" rows="3" 
                                  required 
                                  placeholder="Calle, número, departamento, etc."><?php echo $_POST['direccion'] ?? ''; ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="ciudad">Ciudad: *</label>
                        <input type="text" id="ciudad" name="ciudad" 
                               value="<?php echo $_POST['ciudad'] ?? ''; ?>" 
                               required 
                               placeholder="Ej: Santiago">
                    </div>
                </fieldset>
                
                <fieldset>
                    <legend>Método de Pago</legend>
                    
                    <div class="form-group">
                        <label for="metodo_pago">Seleccione método: *</label>
                        <select id="metodo_pago" name="metodo_pago" required>
                            <option value="">-- Seleccione --</option>
                            <option value="tarjeta_credito" <?php echo (($_POST['metodo_pago'] ?? '') === 'tarjeta_credito') ? 'selected' : ''; ?>>
                                💳 Tarjeta de Crédito
                            </option>
                            <option value="tarjeta_debito" <?php echo (($_POST['metodo_pago'] ?? '') === 'tarjeta_debito') ? 'selected' : ''; ?>>
                                💳 Tarjeta de Débito
                            </option>
                            <option value="transferencia" <?php echo (($_POST['metodo_pago'] ?? '') === 'transferencia') ? 'selected' : ''; ?>>
                                🏦 Transferencia Bancaria
                            </option>
                            <option value="efectivo" <?php echo (($_POST['metodo_pago'] ?? '') === 'efectivo') ? 'selected' : ''; ?>>
                                💵 Pago Contra Entrega
                            </option>
                        </select>
                    </div>
                </fieldset>
                
                <div class="form-group checkbox-group">
                    <label>
                        <input type="checkbox" required>
                        Acepto los <a href="#" target="_blank">términos y condiciones</a> y la 
                        <a href="#" target="_blank">política de privacidad</a> *
                    </label>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary btn-large btn-block">
                        🔒 Pagar $<?php echo number_format($infoCarrito['total'], 0, ',', '.'); ?>
                    </button>
                    
                    <a href="carrito.php" class="btn btn-secondary btn-block">
                        ← Volver al carrito
                    </a>
                </div>
            </form>
        </section>
        
        <aside class="checkout-resumen">
            <h2>Resumen del Pedido</h2>
            
            <div class="resumen-pedido">
                <?php foreach ($infoCarrito['productos'] as $producto): ?>
                    <div class="resumen-producto-item">
                        <div class="producto-info">
                            <span class="producto-nombre"><?php echo $producto['nombre']; ?></span>
                            <span class="producto-cantidad">x<?php echo $producto['cantidad']; ?></span>
                        </div>
                        <span class="producto-precio">
                            $<?php echo number_format($producto['precio'] * $producto['cantidad'], 0, ',', '.'); ?>
                        </span>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="resumen-totales">
                <div class="total-row">
                    <span>Subtotal:</span>
                    <span>$<?php echo number_format($infoCarrito['subtotal'], 0, ',', '.'); ?></span>
                </div>
                
                <?php if ($infoCarrito['descuento'] > 0): ?>
                    <div class="total-row descuento">
                        <span>Descuento (10%):</span>
                        <span>-$<?php echo number_format($infoCarrito['descuento'], 0, ',', '.'); ?></span>
                    </div>
                <?php endif; ?>
                
                <div class="total-row">
                    <span>Envío:</span>
                    <span>$<?php echo number_format($infoCarrito['envio'], 0, ',', '.'); ?></span>
                </div>
                
                <div class="total-row total-final">
                    <strong>TOTAL:</strong>
                    <strong>$<?php echo number_format($infoCarrito['total'], 0, ',', '.'); ?></strong>
                </div>
            </div>
            
            <div class="checkout-security">
                <p>🔒 Pago 100% seguro</p>
                <p>Sus datos están protegidos con encriptación SSL</p>
            </div>
        </aside>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>