<?php
/**
 * Editar un producto existente
 * IACC - Programación Web II - Semana 6
 */

require_once __DIR__ . '/../config/sesion.php';
require_once __DIR__ . '/../config/database.php';

$db = new Database();
$conexion = $db->getConnection();

$errores = [];
$producto = null;

// --- Si el formulario fue enviado (POST), procesamos la actualización ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_producto = (int)($_POST['id_producto'] ?? 0);
    $nombre      = trim($_POST['nombre'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $precio      = $_POST['precio'] ?? '';
    $stock       = $_POST['stock'] ?? '';
    $categoria   = trim($_POST['categoria'] ?? '');
    $imagen      = trim($_POST['imagen'] ?? '');

    // Validaciones en servidor
    if ($id_producto <= 0) {
        $errores[] = "Producto inválido.";
    }
    if (mb_strlen($nombre) < 3) {
        $errores[] = "El nombre debe tener al menos 3 caracteres.";
    }
    if (!is_numeric($precio) || (float)$precio <= 0) {
        $errores[] = "El precio debe ser un número mayor a 0.";
    }
    if (!is_numeric($stock) || (int)$stock < 0) {
        $errores[] = "El stock debe ser un número igual o mayor a 0.";
    }

    if (empty($errores)) {
        $stmt = $conexion->prepare(
            "UPDATE producto 
             SET nombre = ?, descripcion = ?, precio = ?, stock = ?, categoria = ?, imagen = ?
             WHERE id_producto = ?"
        );
        $precioFloat = (float)$precio;
        $stockInt = (int)$stock;
        $stmt->bind_param(
            "ssdissi",
            $nombre,
            $descripcion,
            $precioFloat,
            $stockInt,
            $categoria,
            $imagen,
            $id_producto
        );

        if ($stmt->execute()) {
            $stmt->close();
            $db->close();
            $_SESSION['exito'] = "Producto actualizado correctamente.";
            header("Location: listar_productos.php");
            exit;
        } else {
            $errores[] = "Error al actualizar: " . $conexion->error;
        }
        $stmt->close();
    }

    // Si hubo errores, reconstruimos $producto con lo que el usuario escribió
    // para no perder los datos ingresados al volver a mostrar el formulario
    $producto = [
        'id_producto' => $id_producto,
        'nombre' => $nombre,
        'descripcion' => $descripcion,
        'precio' => $precio,
        'stock' => $stock,
        'categoria' => $categoria,
        'imagen' => $imagen
    ];
} else {
    // --- Si llegamos por GET, buscamos el producto por id ---
    $id = (int)($_GET['id'] ?? 0);

    if ($id <= 0) {
        die("ID de producto no válido.");
    }

    $stmt = $conexion->prepare("SELECT * FROM producto WHERE id_producto = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $producto = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$producto) {
        die("Producto no encontrado.");
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Producto - Tienda</title>
    <link rel="stylesheet" href="../styles_admin.css">
    <link rel="stylesheet" href="../styles.css">
    <script>
        function validarFormularioProducto() {
            const nombre = document.getElementById('nombre').value.trim();
            const precio = document.getElementById('precio').value;
            const stock = document.getElementById('stock').value;

            if (nombre.length < 3) {
                alert('El nombre debe tener al menos 3 caracteres');
                return false;
            }
            if (precio === '' || parseFloat(precio) <= 0) {
                alert('El precio debe ser mayor a 0');
                return false;
            }
            if (stock === '' || parseInt(stock) < 0) {
                alert('El stock no puede ser negativo');
                return false;
            }
            return true;
        }
    </script>
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <div class="container">
        <h2>✏️ Editar Producto</h2>

        <?php if (!empty($errores)): ?>
            <div class="alert alert-error">
                <?php foreach ($errores as $error): ?>
                    <p><?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form action="editar_producto.php" method="post" onsubmit="return validarFormularioProducto()" class="formulario">
            <input type="hidden" name="id_producto" value="<?php echo (int)$producto['id_producto']; ?>">

            <div class="form-group">
                <label for="nombre">Nombre: *</label>
                <input type="text" id="nombre" name="nombre" required minlength="3"
                       value="<?php echo htmlspecialchars($producto['nombre']); ?>">
            </div>

            <div class="form-group">
                <label for="descripcion">Descripción:</label>
                <textarea id="descripcion" name="descripcion" rows="3"><?php echo htmlspecialchars($producto['descripcion'] ?? ''); ?></textarea>
            </div>

            <div class="form-group">
                <label for="precio">Precio: *</label>
                <input type="number" id="precio" name="precio" step="1" min="1" required
                       value="<?php echo htmlspecialchars($producto['precio']); ?>">
            </div>

            <div class="form-group">
                <label for="stock">Stock: *</label>
                <input type="number" id="stock" name="stock" step="1" min="0" required
                       value="<?php echo htmlspecialchars($producto['stock']); ?>">
            </div>

            <div class="form-group">
                <label for="categoria">Categoría:</label>
                <input type="text" id="categoria" name="categoria"
                       value="<?php echo htmlspecialchars($producto['categoria'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label for="imagen">URL de la imagen:</label>
                <input type="text" id="imagen" name="imagen"
                       value="<?php echo htmlspecialchars($producto['imagen'] ?? ''); ?>">
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">💾 Guardar Cambios</button>
                <a href="listar_productos.php" class="btn btn-secondary">← Cancelar</a>
            </div>
        </form>
    </div>

    <?php include '../includes/footer.php'; ?>
</body>
</html>