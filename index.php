<?php
/**
 * Página principal de la tienda
 * IACC - Programación Web II - Semana 5
 * Autor: Gianina Gaete
 */

require_once 'config/sesion.php';
require_once 'includes/carrito_session.php';
require_once 'config/database.php';

$pageTitle = "Inicio";
require_once 'includes/header.php';

// Usar la clase Database correctamente
$db = new Database();
$conn = $db->getConnection();

//Consulta COMPLETA con todas las columnas necesarias
$sql = "SELECT 
            id_producto AS id, 
            nombre, 
            precio, 
            stock, 
            descripcion, 
            imagen, 
            categoria 
        FROM PRODUCTO
        WHERE stock > 0"; // Solo productos con stock disponible

$resultado = $conn->query($sql);

if (!$resultado) {
    die("Error en consulta: " . $conn->error);
}

$productos = $resultado->fetch_all(MYSQLI_ASSOC);

// Normalizar tipos numéricos una sola vez
foreach ($productos as &$p) {
    $p['id'] = (int)$p['id'];
    $p['stock'] = (int)$p['stock'];
    $p['precio'] = (float)$p['precio'];
}
unset($p);

// Procesar agregar al carrito
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['agregar_carrito'])) {
    //Validar token CSRF (Seguridad)
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        setNotification("Error de seguridad. Intente nuevamente.", "error");
    } else {
        $id = (int)$_POST['producto_id'];
        $producto = null;
        
        //Buscar el producto en el array
        foreach ($productos as $p) {
            if ($p['id'] === $id) {
                $producto = $p;
                break;
            }
        }
        
        if ($producto && isset($producto['stock']) && $producto['stock'] > 0) {
            $resultado = agregarAlCarrito(
                $producto['id'], 
                $producto['nombre'], 
                $producto['precio'], 
                1, 
                $producto['stock'],
                $producto['imagen']
            );
            
            if ($resultado['success']) {
                setNotification($resultado['message'], "success");
            } else {
                setNotification($resultado['message'], "error");
            }
        } else {
            setNotification("Producto no disponible o sin stock", "error");
        }
    }
    
    // Redirigir para evitar reenvío del formulario
    header("Location: index.php");
    exit;
}
// Obtener notificación para mostrar
$notification = getNotification();
?>

<section class="hero">
    <div class="container">
        <h2>Bienvenido a nuestra Tienda Online</h2>
        <p>Descubre los mejores productos al mejor precio</p>
        <a href="#productos" class="btn btn-primary">Ver Productos</a>
    </div>
</section>

<section id="productos" class="productos-section">
    <div class="container">
        <h2 class="section-title">Nuestros Productos</h2>
        
        <div class="productos-grid">
            <?php foreach ($productos as $producto): ?>
                <div class="product-card">
                    <div class="product-image">
                        <img src="<?php echo htmlspecialchars($producto['imagen'] ?? ''); ?>" 
                             alt="<?php echo htmlspecialchars($producto['nombre']); ?>">
                    </div>
                    <div class="product-info">
                        <span class="product-category"><?php echo htmlspecialchars($producto['categoria']); ?></span>
                        <h3 class="product-name"><?php echo htmlspecialchars($producto['nombre']); ?></h3>
                        <p class="product-description"><?php echo htmlspecialchars($producto['descripcion']); ?></p>
                        <div class="product-price">
                            <span class="price">$<?php echo number_format($producto['precio'], 0, ',', '.'); ?></span>
                        </div>
                        <div class="product-stock">
                            Stock: <?php echo $producto['stock']; ?> unidades
                        </div>
                        
                        <?php if ($producto['stock'] > 0): ?>
                            <form method="POST" action="index.php" class="product-form">
                                <input type="hidden" name="csrf_token" value="<?php echo getCsrfToken(); ?>">
                                <input type="hidden" name="producto_id" value="<?php echo $producto['id']; ?>">
                                <button type="submit" name="agregar_carrito" class="btn btn-add-cart">
                                    🛒 Agregar al Carrito
                                </button>
                            </form>
                        <?php else: ?>
                            <button disabled class="btn btn-disabled">Agotado</button>
                        <?php endif; ?>

                        <a href="reviews.php?id=<?php echo $producto['id']; ?>" class="btn btn-reviews">
                            ⭐ Ver Reseñas
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>