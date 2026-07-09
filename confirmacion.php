<?php
/**
 * Página de confirmación de pedido
 * IACC - Programación Web II - Semana 6
 * Autor: Gianina Gaete
 */

require_once 'config/sesion.php';
$pageTitle = "Pedido Confirmado";
require_once 'includes/header.php';

// Verificar si existe información del pedido
if (!isset($_SESSION['pedido'])) {
    setNotification("No hay información del pedido", "error");
    header("Location: index.php");
    exit;
}

$pedido = $_SESSION['pedido'];

// Formatear fecha
$fecha_formateada = date('d/m/Y H:i', strtotime($pedido['fecha']));

// Mapear método de pago
$metodos_pago = [
    'tarjeta' => '💳 Tarjeta de Crédito/Débito',
    'transferencia' => '🏦 Transferencia Bancaria',
    'paypal' => '🅿️ PayPal'
];
$metodo_pago_texto = $metodos_pago[$pedido['metodo_pago']] ?? $pedido['metodo_pago'];
?>

<div class="container">
    <div class="confirmation-container">
    <!-- Tarjeta de éxito -->
    <div class="confirmation-card">
        <div class="success-icon-wrapper">
            <span class="check-icon">✓</span>
        </div>
        <h1>¡Pedido Confirmado!</h1>
        <p class="success-message">
            Gracias por tu compra. Tu pedido ha sido procesado exitosamente.
        </p>
    </div>

    <!-- Información del pedido -->
    <div class="order-info-card">
        <h2>📦 Información del Pedido</h2>
        
        <div class="info-grid">
            <div class="info-item">
                <span class="info-label">Número de Pedido</span>
                <span class="info-value order-number"><?php echo htmlspecialchars($pedido['id_pedido']); ?></span>
            </div>
            
            <div class="info-item">
                <span class="info-label">Fecha</span>
                <span class="info-value"><?php echo htmlspecialchars($fecha_formateada); ?></span>
            </div>
            
            <div class="info-item">
                <span class="info-label">Total Pagado</span>
                <span class="info-value total-amount">$<?php echo number_format($pedido['total'], 0, ',', '.'); ?></span>
            </div>
            
            <div class="info-item">
                <span class="info-label">Método de Pago</span>
                <span class="info-value"><?php echo htmlspecialchars($metodo_pago_texto); ?></span>
            </div>
        </div>

        <!-- Datos de envío -->
        <div class="shipping-section">
            <h3>📍 Datos de Envío</h3>
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">Nombre</span>
                    <span class="info-value"><?php echo htmlspecialchars($pedido['nombre']); ?></span>
                </div>
                
                <div class="info-item">
                    <span class="info-label">Email</span>
                    <span class="info-value"><?php echo htmlspecialchars($pedido['email']); ?></span>
                </div>
                
                <div class="info-item">
                    <span class="info-label">Teléfono</span>
                    <span class="info-value"><?php echo htmlspecialchars($pedido['telefono']); ?></span>
                </div>
                
                <div class="info-item">
                    <span class="info-label">Dirección</span>
                    <span class="info-value"><?php echo htmlspecialchars($pedido['direccion']); ?></span>
                </div>
            </div>
        </div>

        <!-- Productos comprados -->
        <div class="products-section">
            <h3>Productos Comprados</h3>

            <?php foreach ($pedido['productos'] as $producto): ?>
                <div class="order-product-item">
                    <div>
                        <span class="order-product-name">
                            <?php echo htmlspecialchars($producto['nombre']); ?></span>
                        <span class="order-product-qty">x<?php echo $producto['cantidad']; ?></span>
                    </div>
                    <span class="order-product-price">$<?php echo number_format($producto['precio'], 0, ',', '.'); ?></span>
                </div>
            <?php endforeach; ?>
            
            <!-- Totales -->
            <div class="order-totals">
                <div class="order-total-row">
                    <span class="label">Subtotal:</span>
                    <span class="value">$<?php echo number_format($pedido['subtotal'], 0, ',', '.'); ?></span>
                </div>
                <div class="order-total-row">
                    <span class="label">Envío:</span>
                    <span class="value">$<?php echo number_format($pedido['costo_envio'], 0, ',', '.'); ?></span>
                </div>
                <div class="order-total-row final">
                    <span class="label">TOTAL:</span>
                    <span class="value">$<?php echo number_format($pedido['total'], 0, ',', '.'); ?></span>
                </div>
            </div>
        </div>

        <!-- Aviso de correo -->
        <div class="email-notice">
            <span class="mail-icon">📧</span>
            <span>Se ha enviado un correo de confirmación a <strong><?php echo htmlspecialchars($pedido['email']); ?></strong></span>
        </div>
    </div>

    <!-- Botones de acción -->
    <div class="confirmation-actions">
        <a href="index.php" class="btn-home">
            🏠 Volver al Inicio
        </a>
        <button onclick="window.print()" class="btn-print">
            🖨️ Imprimir Confirmación
        </button>
    </div>
</div>
</div>

<?php 
// Limpiar el pedido de la sesión después de mostrarlo
unset($_SESSION['pedido']);
require_once 'includes/footer.php'; 
?>