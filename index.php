<?php
/**
 * Página principal de la tienda
 * IACC - Programación Web II - Semana 5
 * Autor: Gianina Gaete
 */

require_once 'config/sesion.php';
require_once 'includes/carrito_session.php';
$pageTitle = "Inicio";
require_once 'includes/header.php';

// Lista de productos 
$productos = [
    [
        'id' => 1, 
        'nombre' => 'Laptop Dell Inspiron', 
        'precio' => 324990, 
        'categoria' => 'Tecnología', 
        'stock' => 10,
        'descripcion' => 'Laptop Dell con procesador Intel i5, 8GB RAM, 256GB SSD',
        'imagen' => 'https://www.blackmoreit.com/cdn/shop/files/20240920_155238.jpg?v=1726845700&width=1500'
    ],
    [
        'id' => 2, 
        'nombre' => 'Mouse Inalámbrico Logitech', 
        'precio' => 4990, 
        'categoria' => 'Tecnología', 
        'stock' => 25,
        'descripcion' => 'Mouse inalámbrico con sensor óptico de alta precisión',
        'imagen' => 'https://siman.vtexassets.com/arquivos/ids/1884407/103144367.jpg?v=637725866328570000'
    ],
    [
        'id' => 3, 
        'nombre' => 'Teclado Mecánico RGB', 
        'precio' => 18990, 
        'categoria' => 'Tecnología', 
        'stock' => 15,
        'descripcion' => 'Teclado mecánico con iluminación RGB personalizable',
        'imagen' => 'https://guiasopensource.net/wp-content/uploads/teclado-mecanico-hardware-libre-1.webp'
    ],
    [
        'id' => 4, 
        'nombre' => 'Monitor 24 pulgadas', 
        'precio' => 74990, 
        'categoria' => 'Tecnología', 
        'stock' => 8,
        'descripcion' => 'Monitor Full HD 1920x1080, 75Hz, HDMI',
        'imagen' => 'https://dicom.mx/wp-content/uploads/2022/05/MNLG-30-2.jpg'
    ],
    [
        'id' => 5, 
        'nombre' => 'Silla Ergonómica', 
        'precio' => 49990, 
        'categoria' => 'Oficina', 
        'stock' => 12,
        'descripcion' => 'Silla ergonómica con soporte lumbar ajustable',
        'imagen' => 'https://m.media-amazon.com/images/I/81Lgluuy9WL._AC_.jpg'
    ],
    [
        'id' => 6, 
        'nombre' => 'Escritorio Moderno', 
        'precio' => 59990, 
        'categoria' => 'Oficina', 
        'stock' => 5,
        'descripcion' => 'Escritorio de madera con cajones, 120x60cm',
        'imagen' => 'https://tse4.mm.bing.net/th/id/OIP.uJJuvFVWl4yvTezDsRMM8wHaHa?r=0&cb=thfvnextfalcon4&rs=1&pid=ImgDetMain&o=7&rm=3'
    ]
];

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
        
        if ($producto && $producto['stock'] > 0) {
            $resultado = agregarAlCarrito(
                $producto['id'], 
                $producto['nombre'], 
                $producto['precio'], 
                1, 
                $producto['stock']
            );
            
            if ($resultado['success']) {
                setNotification($resultado['message'], "success");
            } else {
                setNotification($resultado['message'], "error");
            }
        } else {
            setNotification("Producto no disponible", "error");
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
                        <img src="<?php echo htmlspecialchars($producto['imagen']); ?>" 
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