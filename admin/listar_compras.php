<?php
/**
 * Listar todas las compras registradas en la tabla COMPRA
 * IACC - Programación Web II - Semana 6
 */

require_once __DIR__ . '/../config/sesion.php';
require_once __DIR__ . '/../config/database.php';

$db = new Database();
$conexion = $db->getConnection();

// Consulta simple: traer todas las compras tal como pide el punto 3 del enunciado
$sql = "SELECT * FROM compra ORDER BY id_compra ASC";
$resultado = $conexion->query($sql);

if (!$resultado) {
    die("Error en la consulta: " . $conexion->error);
}

$compras = $resultado->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Compras Registradas - Tienda</title>
    <link rel="stylesheet" href="../styles_admin.css">
    <link rel="stylesheet" href="../styles.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <div class="container">
        <h2>🧾 Compras Registradas</h2>

        <p>
            <a href="registrar_compra.php" class="btn btn-primary">➕ Registrar nueva compra</a>
            <a href="consulta_avanzada.php" class="btn btn-info">📊 Ver Clientes Frecuentes</a>
        </p>

        <?php if (empty($compras)): ?>
            <p>Aún no hay compras registradas.</p>
        <?php else: ?>
            <table class="tabla">
                <thead>
                    <tr>
                        <th>ID Compra</th>
                        <th>ID Cliente</th>
                        <th>ID Producto</th>
                        <th>Cantidad</th>
                        <th>Total</th>
                        <th>Fecha</th>
                        <th>Método de Pago</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($compras as $compra): ?>
                        <tr>
                            <td><?php echo (int)$compra['id_compra']; ?></td>
                            <td><?php echo (int)$compra['id_cliente']; ?></td>
                            <td><?php echo (int)$compra['id_producto']; ?></td>
                            <td><?php echo (int)$compra['cantidad']; ?></td>
                            <td>$<?php echo number_format($compra['total'], 0, ',', '.'); ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($compra['fecha_compra'])); ?></td>
                            <td><?php echo htmlspecialchars($compra['metodo_pago'] ?? '—'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <p><strong>Total de compras registradas:</strong> <?php echo count($compras); ?></p>
        <?php endif; ?>
    </div>

    <?php include '../includes/footer.php'; ?>
</body>
</html>
<?php $db->close(); ?>