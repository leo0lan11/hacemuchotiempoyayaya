<?php
session_start();
require_once "../backend/conexion/conexion.php";

$rutaImagenes = "/LeoAlmacen/assets/img/";

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

$estados = [
    1 => ['texto' => 'Operativo', 'clase' => 'Operativo'],
    2 => ['texto' => 'En revisión', 'clase' => 'Revision'],
    3 => ['texto' => 'Fuera de servicio', 'clase' => 'Fuera']
];
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Carrito | Almacén General</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="/LeoAlmacen/assets/css/dashboard.css">
<link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">

<style>
:root{
  --red:#b00000;
  --green:#2e7d32;
  --yellow:#f9a825;
  --bg:#f5f7fa;
  --card:#ffffff;
  --text:#1f1f1f;
  --muted:#666;
  --shadow:0 4px 6px -1px rgba(0,0,0,.1), 0 2px 4px -1px rgba(0,0,0,.06);
  --radius:12px;
}

*{box-sizing:border-box}
body{margin:0;background:var(--bg);color:var(--text);padding-bottom:30px}

header{
  background:var(--card);
  box-shadow:var(--shadow);
  padding:15px 40px;
  display:flex;
  align-items:center;
  justify-content:space-between;
  position:sticky;
  top:0;
  z-index:100;
}

.logo{display:flex;align-items:center;gap:12px}
.logo strong{color:var(--red);font-size:18px;letter-spacing:.5px}
.title-page{font-size:12px;color:#666}

.header-actions{display:flex;align-items:center;gap:16px}
.cart-counter{background:#fef2f2;color:var(--red);border-radius:20px;padding:6px 12px;font-size:13px;font-weight:600}
.back{
  text-decoration:none;
  color:#444;
  font-weight:500;
  display:flex;
  align-items:center;
  gap:6px;
}
.back:hover{color:var(--red)}

.container{max-width:1200px;margin:30px auto;padding:0 20px}
h2{margin-bottom:8px}
.subtitle{color:var(--muted);margin-bottom:24px}

.summary{
  margin-bottom:20px;
  background:#fff;
  border-radius:var(--radius);
  box-shadow:var(--shadow);
  padding:18px;
  display:flex;
  justify-content:space-between;
  align-items:center;
}

.grid{
  display:grid;
  grid-template-columns:repeat(auto-fill,minmax(240px,1fr));
  gap:24px;
}

.card{
  background:var(--card);
  border-radius:var(--radius);
  box-shadow:var(--shadow);
  border:1px solid transparent;
  display:flex;
  flex-direction:column;
  position:relative;
  overflow:hidden;
  transition:all .25s ease;
}
.card:hover{transform:translateY(-4px);border-color:#ececec}

.badge{
  position:absolute;
  top:12px;
  right:12px;
  padding:4px 10px;
  border-radius:20px;
  font-size:11px;
  font-weight:700;
  color:#fff;
}
.badge.Operativo{background:var(--green)}
.badge.Revision{background:var(--yellow)}
.badge.Fuera{background:var(--red)}

.img-wrap{
  height:190px;
  display:flex;
  align-items:center;
  justify-content:center;
  padding:16px;
  background:#fff;
}
.card img{width:100%;height:100%;object-fit:contain}

.info{padding:14px 16px 16px}
.card h3{margin:0 0 6px;font-size:16px}
.card p{margin:4px 0;color:var(--muted);font-size:14px}

.actions{
  margin-top:12px;
  display:flex;
  justify-content:space-between;
  align-items:center;
  padding-top:10px;
  border-top:1px solid #f1f1f1;
}
.label-id{font-size:12px;color:#9b9b9b}

.remove{
  background:#fff;
  color:var(--red);
  border:1px solid #f3b6b6;
  padding:8px 12px;
  border-radius:8px;
  cursor:pointer;
  font-weight:600;
}
.remove:hover{background:#fff1f1}

.footer{margin-top:30px;display:flex;justify-content:flex-end}
.confirm{
  background:var(--red);
  color:#fff;
  border:none;
  padding:14px 24px;
  border-radius:10px;
  font-size:16px;
  cursor:pointer;
  font-weight:600;
}
.confirm:hover{background:#940000}

.empty{
  background:#fff;
  padding:40px;
  border-radius:16px;
  text-align:center;
  color:#777;
  box-shadow:var(--shadow);
}

@media (max-width:768px){
  header{padding:14px 16px}
  .logo img{height:38px}
  .header-actions{gap:10px}
  .cart-counter{display:none}
  .summary{flex-direction:column;align-items:flex-start;gap:6px}
}
</style>
</head>

<body>
<header>
  <div class="logo">
    <img src="/LeoAlmacen/assets/img/emblema.png" alt="Logo Cruz Roja" style="height:45px;">
    <div>
      <strong>ALMACÉN GENERAL</strong>
      <div class="title-page">Carrito de préstamo</div>
    </div>
  </div>

  <div class="header-actions">
    <div class="cart-counter"><i class="ri-shopping-cart-line"></i> <?= count($productos) ?> equipo(s)</div>
    <a href="dashboard.php" class="back"><i class="ri-arrow-left-line"></i> Volver</a>
  </div>
</header>

<div class="container">
  <h2>Equipos seleccionados</h2>
  <p class="subtitle">Revisa tus artículos antes de confirmar la solicitud.</p>

<?php if (empty($productos)): ?>
  <div class="empty">
    <h3>El carrito está vacío</h3>
    <p>Agrega productos desde el dashboard</p>
  </div>
<?php else: ?>

  <div class="summary">
    <div><strong>Total en carrito:</strong> <?= count($productos) ?> equipo(s)</div>
    <small style="color:#666;">Puedes quitar elementos o confirmar tu solicitud.</small>
  </div>

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
        <span class="badge <?= $estado['clase'] ?>"><?= $estado['texto'] ?></span>

        <div class="img-wrap">
          <img src="<?= $imagen ?>" onerror="this.onerror=null; this.src='/LeoAlmacen/assets/img/error.png';">
        </div>

        <div class="info">
          <h3><?= htmlspecialchars($item['nombre']) ?></h3>
          <p>Stock disponible: <?= (int)$item['stock'] ?></p>

          <div class="actions">
            <span class="label-id">#<?= (int)$item['id_producto'] ?></span>
            <form action="quitarcarrito.php" method="POST">
              <input type="hidden" name="id" value="<?= (int)$item['id_producto'] ?>">
              <button class="remove" type="submit"><i class="ri-delete-bin-line"></i> Quitar</button>
            </form>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <div class="footer">
    <form action="guardar_solicitud.php" method="POST">
      <button class="confirm" type="submit">Confirmar solicitud</button>
    </form>
  </div>

<?php endif; ?>
</div>

</body>
</html>
