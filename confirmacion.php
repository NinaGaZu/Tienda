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
                <span class="info-value order-number">PED-20260707-609E4F</span>
            </div>
            
            <div class="info-item">
                <span class="info-label">Fecha</span>
                <span class="info-value">07/07/2026 05:21</span>
            </div>
            
            <div class="info-item">
                <span class="info-label">Total Pagado</span>
                <span class="info-value total-amount">$7.990</span>
            </div>
            
            <div class="info-item">
                <span class="info-label">Método de Pago</span>
                <span class="info-value">💳 Tarjeta</span>
            </div>
        </div>

        <!-- Datos de envío -->
        <div class="shipping-section">
            <h3>📍 Datos de Envío</h3>
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">Nombre</span>
                    <span class="info-value">Juan Perez</span>
                </div>
                
                <div class="info-item">
                    <span class="info-label">Email</span>
                    <span class="info-value">nombre@gmail.com</span>
                </div>
                
                <div class="info-item">
                    <span class="info-label">Teléfono</span>
                    <span class="info-value">+56912345678</span>
                </div>
                
                <div class="info-item">
                    <span class="info-label">Dirección</span>
                    <span class="info-value">Gomez 369, Los Angeles</span>
                </div>
            </div>
        </div>

        <!-- Productos comprados -->
        <div class="products-section">
            <h3>️ Productos Comprados</h3>
            
            <div class="order-product-item">
                <div>
                    <span class="order-product-name">Mouse Inalámbrico Logitech</span>
                    <span class="order-product-qty">x1</span>
                </div>
                <span class="order-product-price">$4.990</span>
            </div>
            
            <!-- Totales -->
            <div class="order-totals">
                <div class="order-total-row">
                    <span class="label">Subtotal:</span>
                    <span class="value">$4.990</span>
                </div>
                <div class="order-total-row">
                    <span class="label">Envío:</span>
                    <span class="value">$3.000</span>
                </div>
                <div class="order-total-row final">
                    <span class="label">TOTAL:</span>
                    <span class="value">$7.990</span>
                </div>
            </div>
        </div>

        <!-- Aviso de correo -->
        <div class="email-notice">
            <span class="mail-icon">📧</span>
            <span>Se ha enviado un correo de confirmación a <strong>nombre@gmail.com</strong></span>
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