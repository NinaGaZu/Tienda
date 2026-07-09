<?php
/**
 * Listar todos los productos
 */
require_once '../config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$db = new Database();
$conn = $db->getConnection();

// Consulta simple SELECT
$sql = "SELECT * FROM PRODUCTO ORDER BY nombre ASC";
$resultado = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Listado de Productos</title>
    <link rel="stylesheet" href="../styles_admin.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <div class="container">
        <h2>📦 Productos Registrados</h2>

        <?php if (isset($_SESSION['exito'])): ?>
            <div class="alert alert-success">
                <?php echo $_SESSION['exito']; unset($_SESSION['exito']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['errores'])): ?>
            <div class="alert alert-error">
                <?php foreach ($_SESSION['errores'] as $error): ?>
                    <p><?php echo $error; ?></p>
                <?php endforeach; ?>
                <?php unset($_SESSION['errores']); ?>
            </div>
        <?php endif; ?>

        <?php if ($resultado->num_rows > 0): ?>
            <table class="tabla">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Precio</th>
                        <th>Stock</th>
                        <th>Categoría</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($fila = $resultado->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $fila['id_producto']; ?></td>
                            <td><?php echo htmlspecialchars($fila['nombre']); ?></td>
                            <td>$<?php echo number_format($fila['precio'], 0, ',', '.'); ?></td>
                            <td><?php echo $fila['stock']; ?></td>
                            <td><?php echo htmlspecialchars($fila['categoria']); ?></td>
                            <td>
                                <a href="editar_producto.php?id=<?php echo $fila['id_producto']; ?>" 
                                   class="btn btn-sm btn-warning">✏️</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <p><strong>Total:</strong> <?php echo $resultado->num_rows; ?> productos</p>
        <?php else: ?>
            <p class="alert alert-info">No hay productos registrados</p>
        <?php endif; ?>

        <div class="form-actions">
            <a href="productos_form.html" class="btn btn-primary">➕ Nuevo Producto</a>
            <a href="../index.php" class="btn btn-secondary">🏠 Volver al Inicio</a>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>
</body>
</html>

<?php $db->close(); ?>