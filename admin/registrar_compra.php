<?php
/**
 * Registrar una compra (tabla COMPRA)
 * Usa los datos ya registrados en PRODUCTO y CLIENTE
 * IACC - Programación Web II - Semana 6
 */

require_once __DIR__ . '/../config/sesion.php';
require_once __DIR__ . '/../config/database.php';

$db = new Database();
$conexion = $db->getConnection();

$errores = [];
$exito = null;

// --- Procesar el formulario si fue enviado ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_producto = (int)($_POST['id_producto'] ?? 0);
    $id_cliente  = (int)($_POST['id_cliente'] ?? 0);
    $cantidad    = (int)($_POST['cantidad'] ?? 0);
    $metodo_pago = trim($_POST['metodo_pago'] ?? '');

    if ($id_producto <= 0) {
        $errores[] = "Debe seleccionar un producto.";
    }
    if ($id_cliente <= 0) {
        $errores[] = "Debe seleccionar un cliente.";
    }
    if ($cantidad <= 0) {
        $errores[] = "La cantidad debe ser mayor a 0.";
    }

    // Buscar el producto para tomar su precio y validar stock
    $producto = null;
    if (empty($errores)) {
        $stmt = $conexion->prepare("SELECT precio, stock, nombre FROM producto WHERE id_producto = ?");
        $stmt->bind_param("i", $id_producto);
        $stmt->execute();
        $producto = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$producto) {
            $errores[] = "El producto seleccionado no existe.";
        } elseif ($cantidad > (int)$producto['stock']) {
            $errores[] = "Stock insuficiente. Solo hay {$producto['stock']} unidades de '{$producto['nombre']}'.";
        }
    }

    if (empty($errores)) {
        $total = $producto['precio'] * $cantidad;
        $fecha = date('Y-m-d H:i:s');

        // Insertar la compra
        $stmt = $conexion->prepare(
            "INSERT INTO compra (cantidad, total, fecha_compra, id_producto, id_cliente, metodo_pago)
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        $stmt->bind_param("idsiis", $cantidad, $total, $fecha, $id_producto, $id_cliente, $metodo_pago);

        if ($stmt->execute()) {
            // Descontar el stock del producto
            $nuevoStock = (int)$producto['stock'] - $cantidad;
            $stmtStock = $conexion->prepare("UPDATE producto SET stock = ? WHERE id_producto = ?");
            $stmtStock->bind_param("ii", $nuevoStock, $id_producto);
            $stmtStock->execute();
            $stmtStock->close();

            $exito = "Compra registrada correctamente. Total: $" . number_format($total, 0, ',', '.');
        } else {
            $errores[] = "Error al registrar la compra: " . $conexion->error;
        }
        $stmt->close();
    }
}

// --- Cargar listas para los <select> del formulario ---
$productos = $conexion->query("SELECT id_producto, nombre, precio, stock FROM producto ORDER BY nombre ASC")
                       ->fetch_all(MYSQLI_ASSOC);
$clientes = $conexion->query("SELECT id_cliente, nombre, email FROM cliente ORDER BY nombre ASC")
                      ->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Compra - Tienda</title>
    <link rel="stylesheet" href="../styles_admin.css">
    <link rel="stylesheet" href="../styles.css">
    <script>
        function validarFormularioCompra() {
            const producto = document.getElementById('id_producto').value;
            const cliente = document.getElementById('id_cliente').value;
            const cantidad = document.getElementById('cantidad').value;

            if (producto === '') {
                alert('Debe seleccionar un producto');
                return false;
            }
            if (cliente === '') {
                alert('Debe seleccionar un cliente');
                return false;
            }
            if (cantidad === '' || parseInt(cantidad) <= 0) {
                alert('La cantidad debe ser mayor a 0');
                return false;
            }
            return true;
        }
    </script>
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <div class="container">
        <h2>🧾 Registrar Compra</h2>

        <?php if ($exito): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($exito); ?></div>
        <?php endif; ?>

        <?php if (!empty($errores)): ?>
            <div class="alert alert-error">
                <?php foreach ($errores as $error): ?>
                    <p><?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (empty($productos) || empty($clientes)): ?>
            <div class="alert alert-warning">
                <p>Necesitas al menos un producto y un cliente registrados antes de poder generar una compra.</p>
                <p>
                    <a href="productos_form.php" class="btn btn-primary">➕ Registrar Producto</a>
                    <a href="clientes_form.php" class="btn btn-primary">➕ Registrar Cliente</a>
                </p>
            </div>
        <?php else: ?>
            <form action="registrar_compra.php" method="post" onsubmit="return validarFormularioCompra()" class="formulario">

                <div class="form-group">
                    <label for="id_producto">Producto: *</label>
                    <select id="id_producto" name="id_producto" required>
                        <option value="">-- Seleccione un producto --</option>
                        <?php foreach ($productos as $p): ?>
                            <option value="<?php echo $p['id_producto']; ?>">
                                <?php echo htmlspecialchars($p['nombre']); ?>
                                — $<?php echo number_format($p['precio'], 0, ',', '.'); ?>
                                (stock: <?php echo $p['stock']; ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="id_cliente">Cliente: *</label>
                    <select id="id_cliente" name="id_cliente" required>
                        <option value="">-- Seleccione un cliente --</option>
                        <?php foreach ($clientes as $c): ?>
                            <option value="<?php echo $c['id_cliente']; ?>">
                                <?php echo htmlspecialchars($c['nombre']); ?> (<?php echo htmlspecialchars($c['email']); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="cantidad">Cantidad: *</label>
                    <input type="number" id="cantidad" name="cantidad" min="1" required value="1">
                </div>

                <div class="form-group">
                    <label for="metodo_pago">Método de Pago:</label>
                    <select id="metodo_pago" name="metodo_pago">
                        <option value="tarjeta">Tarjeta de Crédito/Débito</option>
                        <option value="transferencia">Transferencia Bancaria</option>
                        <option value="paypal">PayPal</option>
                    </select>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">💾 Registrar Compra</button>
                    <a href="listar_compras.php" class="btn btn-info">📋 Ver Compras</a>
                </div>
            </form>
        <?php endif; ?>
    </div>

    <?php include '../includes/footer.php'; ?>
</body>
</html>
<?php $db->close(); ?>