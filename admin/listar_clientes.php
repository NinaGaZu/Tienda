<?php
/**
 * Lista todos los clientes registrados en la tabla CLIENTE
 */

require_once __DIR__ . '/../config/sesion.php';
require_once __DIR__ . '/../config/database.php';

$db = new Database();
$conexion = $db->getConnection();

// Consulta simple: traer todos los clientes
$sql = "SELECT id_cliente, nombre, email, direccion, telefono FROM cliente ORDER BY id_cliente ASC";
$resultado = $conexion->query($sql);

if (!$resultado) {
    die("Error en la consulta: " . $conexion->error);
}

$clientes = $resultado->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clientes Registrados - Tienda</title>
    <link rel="stylesheet" href="../styles_admin.css">
    <link rel="stylesheet" href="../styles.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <div class="container">
        <h2>📋 Clientes Registrados</h2>

        <p>
            <a href="clientes_form.php" class="btn btn-primary">➕ Registrar nuevo cliente</a>
        </p>

        <?php if (empty($clientes)): ?>
            <p>Aún no hay clientes registrados.</p>
        <?php else: ?>
            <table class="tabla">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Dirección</th>
                        <th>Teléfono</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($clientes as $cliente): ?>
                        <tr>
                            <td><?php echo (int)$cliente['id_cliente']; ?></td>
                            <td><?php echo htmlspecialchars($cliente['nombre']); ?></td>
                            <td><?php echo htmlspecialchars($cliente['email']); ?></td>
                            <td><?php echo htmlspecialchars($cliente['direccion']); ?></td>
                            <td><?php echo htmlspecialchars($cliente['telefono'] ?? '—'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <p><strong>Total de clientes:</strong> <?php echo count($clientes); ?></p>
        <?php endif; ?>
    </div>

    <?php include '../includes/footer.php'; ?>
</body>
</html>
<?php $db->close(); ?>