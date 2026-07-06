<?php
require_once __DIR__ . '/../config/sesion.php';
require_once __DIR__ . '/carrito_session.php';

if (!function_exists('obtenerCantidadItems')) {
    function obtenerCantidadItems() {
        if (empty($_SESSION['carrito']) || !is_array($_SESSION['carrito'])) {
            return 0;
        }

        $cantidad = 0;
        foreach ($_SESSION['carrito'] as $item) {
            if (is_array($item) && isset($item['cantidad'])) {
                $cantidad += (int) $item['cantidad'];
            } else {
                $cantidad++;
            }
        }

        return $cantidad;
    }
}

$notification = $_SESSION['notification'] ?? null;
unset($_SESSION['notification']);

$cantidadCarrito = obtenerCantidadItems() ?? 0;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Tienda'; ?> - Tienda Online</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header class="main-header">
        <div class="container header-content">
            <div class="logo">
                <h1><a href="index.php">🛍️ Tienda</a></h1>
            </div>
            <nav class="main-nav">
                <ul>
                    <li><a href="index.php">Inicio</a></li>
                    <li><a href="index.php#productos">Productos</a></li>
                    <li>
                        <a href="carrito.php" class="cart-link">
                            🛒 Carrito
                            <?php if ($cantidadCarrito > 0): ?>
                                <span class="cart-count"><?php echo $cantidadCarrito; ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </header>

    <?php if ($notification): ?>
        <div class="notification notification-<?php echo htmlspecialchars($notification['type']); ?>">
            <div class="container">
                <?php echo htmlspecialchars($notification['message']); ?>
                <button class="notification-close" onclick="this.parentElement.parentElement.remove()">×</button>
            </div>
        </div>
    <?php endif; ?>

    <main class="main-content">