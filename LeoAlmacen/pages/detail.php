<?php
session_start();
require_once "../backend/conexion/conexion.php";

if (!isset($_SESSION['usuario'])) {
    header("Location: /LeoAlmacen/index.html");
    exit();
}
$nombre = isset($_SESSION['usuario']) ? $_SESSION['usuario'] : 'Usuario';

if (!isset($_GET['id'])) {
    die("Producto no especificado");
}

$id = intval($_GET['id']);

$stmt = $conexion->prepare("
  SELECT 
    p.*,
    e.nombre AS estado
  FROM productos p
  LEFT JOIN estados e ON p.id_estado = e.id_estado
  WHERE p.id_producto = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    die("Producto no encontrado");
}

$producto = $resultado->fetch_assoc();

// Obtener todas las imágenes del producto
$imagenes = [];

// Intentar obtener imágenes de la tabla nueva
try {
    $stmtImg = $conexion->prepare("
      SELECT id_imagen, imagen FROM producto_imagenes 
      WHERE id_producto = ? 
      ORDER BY orden ASC
    ");
    
    if ($stmtImg) {
        $stmtImg->bind_param("i", $id);
        $stmtImg->execute();
        $resultadoImg = $stmtImg->get_result();
        
        while ($row = $resultadoImg->fetch_assoc()) {
            $imagenes[] = $row;
        }
        $stmtImg->close();
    }
} catch (Exception $e) {
    // La tabla no existe, seguir con la antigua
}

// Si no hay imágenes en la tabla nueva, usar la antigua
if (empty($imagenes) && !empty($producto['imagen'])) {
    $imagenes[] = ['id_imagen' => 0, 'imagen' => $producto['imagen']];
}

// Si aún no hay imágenes, usar mockup
if (empty($imagenes)) {
    $imagenes[] = ['id_imagen' => 0, 'imagen' => 'mockup.png'];
}

// Obtener comentarios del producto
$comentarios = [];
try {
    $stmtComents = $conexion->prepare("
      SELECT id_comentario, usuario, comentario, created_at 
      FROM comentarios 
      WHERE id_producto = ? 
      ORDER BY created_at DESC
    ");
    
    if ($stmtComents) {
        $stmtComents->bind_param("i", $id);
        $stmtComents->execute();
        $resultadoComents = $stmtComents->get_result();
        
        while ($row = $resultadoComents->fetch_assoc()) {
            $comentarios[] = $row;
        }
        $stmtComents->close();
    }
} catch (Exception $e) {
    // La tabla de comentarios no existe
}

switch ($producto['id_estado']) {
    case 2:
        $estado = 'En revisión';
        $claseEstado = 'status-revision';
        break;

    case 3:
        $estado = 'Fuera de servicio';
        $claseEstado = 'status-fuera';
        break;

    default:
        $estado = 'Operativo';
        $claseEstado = 'status-operativo';
}
$stmt->close();
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Detalle del Producto</title>

<link rel="stylesheet" href="/LeoAlmacen/assets/css/dashboard.css">
<link rel="stylesheet" href="/LeoAlmacen/assets/css/detail.css">
<link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
</head>

<body class="detail-page">

<!-- ===== HEADER ===== -->
<header>
  <div class="logo">
    <img src="/LeoAlmacen/assets/img/emblema.png" alt="LogoCruzRoja" style="height:45px;"> 
    <div>
      <strong>ALMACÉN GENERAL</strong>
      <div style="font-size:12px; color:#ddd;">Cruz Roja Mexicana</div>
    </div>
  </div>

  <!-- 🔍 BUSCADOR -->
  <div class="search">
    <i class="ri-search-line"></i>
    <input type="text" placeholder="Buscar equipo, categoría o código..." disabled>
  </div>
<a href="carrito.php"
   style="
     margin-right:15px;
     color:#b00000;
     font-family: Arial, Helvetica, sans-serif;
     font-weight:600;
     text-decoration: underline;
     display:flex;
     align-items:center;
     gap:4px;
   ">
  <i class="ri-shopping-cart-line"></i>
  Préstamo
  <?php if (!empty($_SESSION['carrito_prestamo'])): ?>
    (<?= count($_SESSION['carrito_prestamo']) ?>)
  <?php endif; ?>
</a>

  <div class="user-profile">
      <div style="text-align:right; line-height:1.2;">
          <span style="display:block; font-size:12px; color:#999;">Bienvenido,</span>
          <strong style="color:#333;"><?php echo htmlspecialchars($nombre); ?></strong>
      </div>
      
      <a href="/LeoAlmacen/pages/login.html" class="btn-logout" title="Cerrar Sesión">
        <i class="ri-logout-box-r-line"></i>
      </a>
  </div>
</header>

<!-- ===== CONTENIDO ===== -->
<div class="product-detail">

  <div class="image-container">
    <div class="image-viewer">
      <img 
        id="mainImage"
        src="/LeoAlmacen/assets/img/<?= htmlspecialchars($imagenes[0]['imagen']) ?>" 
        alt="<?= htmlspecialchars($producto['nombre']) ?>"
      >
      <?php if (count($imagenes) > 1): ?>
      <button class="nav-btn prev" onclick="prevImage()">
        <i class="ri-chevron-left-line"></i>
      </button>
      <button class="nav-btn next" onclick="nextImage()">
        <i class="ri-chevron-right-line"></i>
      </button>
      <div class="image-counter">
        <span id="imageIndex">1</span> / <span id="imageTotal"><?= count($imagenes) ?></span>
      </div>
      <?php endif; ?>
    </div>

    <?php if (count($imagenes) > 1): ?>
    <div class="image-thumbnails">
      <?php foreach ($imagenes as $idx => $img): ?>
      <img 
        src="/LeoAlmacen/assets/img/<?= htmlspecialchars($img['imagen']) ?>" 
        alt="Imagen <?= $idx + 1 ?>"
        class="thumbnail <?= $idx === 0 ? 'active' : '' ?>"
        onclick="goToImage(<?= $idx ?>)"
      >
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>

  <div class="product-info">
    <h1><?= htmlspecialchars($producto['nombre']) ?></h1>

    <p><strong>Código:</strong> <?= htmlspecialchars($producto['codigo']) ?></p>
    <p><strong>Stock:</strong> <?= $producto['stock'] ?></p>
    <p><strong>Ubicación:</strong> <?= htmlspecialchars($producto['ubicacion']) ?></p>

<span class="detail-status <?= $claseEstado ?>">
  <?= htmlspecialchars($estado) ?>
</span>


    <p style="margin-top:16px">
      <?= htmlspecialchars($producto['descripcion']) ?>
    </p>

   <div class="actions">
  <a href="dashboard.php" class="btn">
    <i class="ri-arrow-left-line"></i> Volver
  </a>

  <a href="prestamo.php?id=<?= $id ?>" class="btn" style="margin-left:10px;">
    <i class="ri-shopping-cart-line"></i> Agregar al préstamo
  </a>
</div>


    <!-- COMENTARIOS -->
    <div style="margin-top: 30px; padding-top: 20px; border-top: 2px solid #e0e0e0;">
      <h3 style="margin-bottom: 15px; color: #333;">Comentarios</h3>

      <!-- Formulario para agregar comentario -->
      <div style="margin-bottom: 20px; background: #f9f9f9; padding: 15px; border-radius: 8px;">
        <textarea 
          id="comentarioInput" 
          placeholder="Agrega un comentario..." 
          maxlength="500"
          style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-family: Arial; resize: vertical; min-height: 80px;"
        ></textarea>
        <button id="btnAgregarComentario" style="margin-top: 10px; padding: 10px 20px; background: #b00000; color: #fff; border: none; border-radius: 4px; cursor: pointer; font-weight: 600;">
          Comentar
        </button>
      </div>

      <!-- Lista de comentarios -->
      <div id="comentariosList" style="max-height: 400px; overflow-y: auto;">
        <?php if (count($comentarios) > 0): ?>
          <?php foreach ($comentarios as $com): ?>
            <div style="background: #f9f9f9; padding: 12px; margin-bottom: 10px; border-radius: 4px; border-left: 3px solid #b00000;">
              <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                <strong style="color: #333;"><?= htmlspecialchars($com['usuario']) ?></strong>
                <span style="font-size: 12px; color: #999;">
                  <?= date('d/m/Y H:i', strtotime($com['created_at'])) ?>
                </span>
              </div>
              <p style="margin: 0; color: #555; line-height: 1.5;">
                <?= htmlspecialchars($com['comentario']) ?>
              </p>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
        <?php endif; ?>
      </div>
    </div>
  </div>

</div>

<script>
  const imagenes = <?= json_encode($imagenes) ?>;
  let currentIndex = 0;

  function updateImage() {
    document.getElementById('mainImage').src = '/LeoAlmacen/assets/img/' + imagenes[currentIndex].imagen;
    document.getElementById('imageIndex').textContent = currentIndex + 1;
    
    // Actualizar thumbnails
    document.querySelectorAll('.thumbnail').forEach((thumb, idx) => {
      thumb.classList.toggle('active', idx === currentIndex);
    });
  }

  function nextImage() {
    currentIndex = (currentIndex + 1) % imagenes.length;
    updateImage();
  }

  function prevImage() {
    currentIndex = (currentIndex - 1 + imagenes.length) % imagenes.length;
    updateImage();
  }

  function goToImage(index) {
    currentIndex = index;
    updateImage();
  }

  // MANEJO DE COMENTARIOS
  document.getElementById('btnAgregarComentario').addEventListener('click', () => {
    const comentarioInput = document.getElementById('comentarioInput');
    const comentario = comentarioInput.value.trim();

    if (!comentario) {
      alert('El comentario no puede estar vacío');
      return;
    }

    const formData = new FormData();
    formData.append('id_producto', <?= $id ?>);
    formData.append('comentario', comentario);

    fetch('/LeoAlmacen/backend/productos/guardar_comentario.php', {
      method: 'POST',
      body: formData
    })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        alert('Comentario guardado!');
        comentarioInput.value = '';
        location.reload(); // Recargar para ver el nuevo comentario
      } else {
        alert('Error: ' + data.error);
      }
    })
    .catch(err => {
      console.error('Error:', err);
      alert('Error al guardar el comentario');
    });
  });
</script>

</body>
</html>
