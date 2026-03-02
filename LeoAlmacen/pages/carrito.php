<?php
session_start();
require_once "../backend/conexion/conexion.php";

/* =========================
   CONFIGURACIÓN
========================= */
$rutaImagenes = "/LeoAlmacen/assets/img/"; // carpeta donde están las imágenes

/* =========================
   CARRITO (solo IDs)
========================= */
$ids = $_SESSION['carrito_prestamo'] ?? [];
$productos = [];

if (!empty($ids)) {
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $types = str_repeat('i', count($ids));

    $sql = "
        SELECT id_producto, nombre, stock, id_estado, imagen
        FROM productos
        WHERE id_producto IN ($placeholders)
    ";

    $stmt = $conexion->prepare($sql);
    $stmt->bind_param($types, ...$ids);
    $stmt->execute();
    $productos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

/* =========================
   MAPEO DE ESTADOS
========================= */
$estados = [
    1 => ['texto' => 'Operativo',        'clase' => 'Operativo'],
    2 => ['texto' => 'En revisión',       'clase' => 'Revision'],
    3 => ['texto' => 'Fuera de servicio', 'clase' => 'Fuera']
];
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Carrito | Almacén General</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<style>
:root{
  --red:#b00000;
  --green:#2e7d32;
  --orange:#f9a825;
  --gray:#616161;
  --bg:#f6f7f9;
  --card:#ffffff;
  --text:#1f1f1f;
  --muted:#6b6b6b;
  --border:#e3e3e3;
}
*{box-sizing:border-box;font-family:Arial,Helvetica,sans-serif}
body{margin:0;background:var(--bg);color:var(--text)}

header{
  background:#fff;
  border-bottom:1px solid var(--border);
  padding:14px 24px;
  display:flex;
  align-items:center;
  justify-content:space-between;
}
header h1{margin:0;font-size:20px;color:var(--red)}

.container{max-width:1200px;margin:30px auto;padding:0 20px}
h2{margin-bottom:16px}

.grid{
  display:grid;
  grid-template-columns:repeat(auto-fill,minmax(260px,1fr));
  gap:20px;
}

.card{
  background:var(--card);
  border-radius:16px;
  box-shadow:0 8px 20px rgba(0,0,0,.06);
  padding:16px;
  display:flex;
  flex-direction:column;
  position:relative;
}

.badge{
  position:absolute;
  top:14px;
  right:14px;
  padding:4px 10px;
  border-radius:12px;
  font-size:12px;
  color:#fff;
}
.badge.Operativo{background:var(--green)}
.badge.Revision{background:var(--yellow)}
.badge.Fuera{background:var(--red)}

.card img{
  width:100%;
  height:160px;
  object-fit:contain;
  background:#fafafa;
  border-radius:12px;
}

.card h3{margin:12px 0 6px;font-size:16px}
.card p{margin:4px 0;color:var(--muted);font-size:14px}

.actions{
  margin-top:auto;
  display:flex;
  justify-content:flex-end;
}

.remove{
  background:var(--red);
  color:#fff;
  border:none;
  padding:8px 14px;
  border-radius:10px;
  cursor:pointer;
}

.footer{
  margin-top:30px;
  display:flex;
  justify-content:flex-end;
}

.confirm{
  background:var(--red);
  color:#fff;
  border:none;
  padding:14px 22px;
  border-radius:14px;
  font-size:16px;
  cursor:pointer;
}

.empty{
  background:#fff;
  padding:40px;
  border-radius:16px;
  text-align:center;
  color:#777;
}
</style>
</head>

<body>

<header>
  <h1>🛒 Carrito de Solicitud</h1>
  <a href="dashboard.php" style="text-decoration:none;color:#555">← Volver</a>
</header>

<div class="container">
  <h2>Equipos seleccionados</h2>

<?php if (empty($productos)): ?>
  <div class="empty">
    <h3>El carrito está vacío</h3>
    <p>Agrega productos desde el dashboard</p>
  </div>
<?php else: ?>

  <div class="grid">
    <?php foreach ($productos as $item): 
        $estado = $estados[$item['id_estado']] ?? [
            'texto' => 'Desconocido',
            'clase' => 'Fuera'
        ];

        $imagen = !empty($item['imagen'])
            ? $rutaImagenes . $item['imagen']
            : $rutaImagenes . "no-image.png";
    ?>
      <div class="card">
        <span class="badge <?= $estado['clase'] ?>">
          <?= $estado['texto'] ?>
        </span>

       <img src="<?= $imagen ?>"
     onerror="this.onerror=null; this.src='/LeoAlmacen/assets/img/error.png';">


        <h3><?= htmlspecialchars($item['nombre']) ?></h3>
        <p>Stock disponible: <?= (int)$item['stock'] ?></p>

        <div class="actions">
          <form action="quitarcarrito.php" method="POST">
            <input type="hidden" name="id" value="<?= (int)$item['id_producto'] ?>">
            <button class="remove">Quitar</button>
          </form>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <div class="footer">
    <form action="guardar_solicitud.php" method="POST">
      <button class="confirm">Confirmar solicitud</button>
    </form>
  </div>

<?php endif; ?>
</div>

</body>
</html>
