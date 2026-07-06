<?php
/**
 * Página de confirmación de pedido
 * IACC - Programación Web II - Semana 5
 * Autor: [Tu Nombre]
 */

$pageTitle = "Pedido Confirmado";
require_once 'includes/header.php';

// Verificar si existe información del pedido
if (!isset($_SESSION['pedido'])) {
    setNotification("No hay información del pedido", "error");
    header("Location: index.php");
    exit;
}

$pedido = $_SESSION['pedido'];
?>

<div class="container">
    <div class="confirmacion-container">
        <div class="confirmacion-header">
            <div class="success-icon">✅</div>
            <h1>¡Pedido Confirmado!</h1>
            <p class="success-message">Gracias por tu compra. Tu pedido ha sido procesado exitosamente.</p>
        </div>
        
        <div class="confirmacion-info">
            <div class="info-section">
                <h2>Información del Pedido</h2>
                <div class="info-grid">
                    <div class="info-item">
                        <strong>Número de Pedido:</strong>
                        <span><?php echo htmlspecialchars($pedido['id_pedido']); ?></span>
                    </div>
                    <div class="info-item">
                        <strong>Fecha:</strong>
                        <span><?php echo date('d/m/Y H:i', strtotime($pedido['fecha'])); ?></span>
                    </div>
                    <div class="info-item">
                        <strong>Total Pagado:</strong>
                        <span class="total-amount">$<?php echo number_format($pedido['total'], 0, ',', '.'); ?></span>
                    </div>
                    <div class="info-item">
                        <strong>Método de Pago:</strong>
                        <span><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $pedido['metodo_pago']))); ?></span>
                    </div>
                </div>
            </div>
            
            <div class="info-section">
                <h2>Datos de Envío</h2>
                <div class="info-grid">
                    <div class="info-item full-width">
                        <strong>Nombre:</strong>
                        <span><?php echo htmlspecialchars($pedido['nombre']); ?></span>
                    </div>
                    <div class="info-item full-width">
                        <strong>Email:</strong>
                        <span><?php echo htmlspecialchars($pedido['email']); ?></span>
                    </div>
                    <div class="info-item full-width">
                        <strong>Teléfono:</strong>
                        <span><?php echo htmlspecialchars($pedido['telefono']); ?></span>
                    </div>
                    <div class="info-item full-width">
                        <strong>Dirección:</strong>
                        <span><?php echo htmlspecialchars($pedido['direccion']); ?></span>
                    </div>
                    <div class="info-item full-width">
                        <strong>Ciudad:</strong>
                        <span><?php echo htmlspecialchars($pedido['ciudad']); ?></span>
                    </div>
                </div>
            </div>
            
            <div class="info-section">
                <h2>Productos Comprados</h2>
                <div class="productos-pedido">
                    <?php foreach ($pedido['productos'] as $producto): ?>
                        <div class="producto-item">
                            <span class="producto-nombre"><?php echo htmlspecialchars($producto['nombre']); ?></span>
                            <span class="producto-cantidad">x<?php echo $producto['cantidad']; ?></span>
                            <span class="producto-precio">
                                $<?php echo number_format($producto['precio'] * $producto['cantidad'], 0, ',', '.'); ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="resumen-final">
                    <div class="resumen-line">
                        <span>Subtotal:</span>
                        <span>$<?php echo number_format($pedido['subtotal'], 0, ',', '.'); ?></span>
                    </div>
                    <?php if ($pedido['descuento'] > 0): ?>
                        <div class="resumen-line descuento">
                            <span>Descuento:</span>
                            <span>-$<?php echo number_format($pedido['descuento'], 0, ',', '.'); ?></span>
                        </div>
                    <?php endif; ?>
                    <div class="resumen-line">
                        <span>Envío:</span>
                        <span>$<?php echo number_format($pedido['envio'], 0, ',', '.'); ?></span>
                    </div>
                    <div class="resumen-line total">
                        <strong>TOTAL:</strong>
                        <strong>$<?php echo number_format($pedido['total'], 0, ',', '.'); ?></strong>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="confirmacion-actions">
            <a href="index.php" class="btn btn-primary">
                🏠 Volver al Inicio
            </a>
            <button onclick="window.print()" class="btn btn-secondary">
                🖨️ Imprimir Confirmación
            </button>
        </div>
        
        <div class="confirmacion-note">
            <p>📧 Se ha enviado un correo de confirmación a <strong><?php echo htmlspecialchars($pedido['email']); ?></strong></p>
            <p>Guarda tu número de pedido: <strong><?php echo htmlspecialchars($pedido['id_pedido']); ?></strong></p>
        </div>
    </div>
</div>

<?php 
// Limpiar el pedido de la sesión después de mostrarlo
unset($_SESSION['pedido']);
require_once 'includes/footer.php'; 
?>