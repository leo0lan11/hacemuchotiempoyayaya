<?php
// pages/dashboard.php
session_start();



if (!isset($_SESSION['usuario'])) {
    header("Location: /LeoAlmacen/pages/login.html");
    exit();
}
$nombre = $_SESSION['usuario'];
$rol = isset($_SESSION['rol']) ? $_SESSION['rol'] : 'usuario';
$esAdmin = ($rol === 'admin');
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Almacén</title>
  
  <link rel="stylesheet" href="/LeoAlmacen/assets/css/dashboard.css">
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.css">
</head>
<body>

<header>
  <div class="logo">
    <img src="/LeoAlmacen/assets/img/emblema.png" alt="LogoCruzRoja" style="height:45px;"> 
    <div>
      <strong>ALMACÉN GENERAL</strong>
      <div style="font-size:12px; color:#666;">Cruz Roja Mexicana</div>
    </div>
  </div>

  <div class="search">
    <i class="ri-search-line"></i>
    <input id="buscador" placeholder="Buscar equipo, categoría o código...">
  </div>

  <!-- 🛒 CARRITO -->
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


<main>
    <section class="kpis">
      <div class="kpi">
        <h3>Total Equipos</h3>
        <strong id="kpiTotal">0</strong>
      </div>

      <div class="kpi">
        <h3 style="color:#2e7d32">Operativos</h3>
        <strong id="kpiOperativo" style="color:#2e7d32">0</strong>
      </div>

      <div class="kpi">
        <h3 style="color:#f9a825">En Revisión</h3>
        <strong id="kpiRevision" style="color:#f9a825">0</strong>
      </div>

      <div class="kpi">
        <h3 style="color:#c62828">Fuera de Servicio</h3>
        <strong id="kpiFuera" style="color:#c62828">0</strong>
      </div>
    </section>

    <section class="filters">
      <div class="filter active" data-filter="Todos">Todos</div>
      <div class="filter" data-filter="Operativo">Operativos</div>
      <div class="filter" data-filter="Revisión">En revisión</div>
      <div class="filter" data-filter="Fuera de servicio">Bajas</div>
    </section>

    <section class="grid" id="gridProductos">
        </section>
</main>

<?php if ($esAdmin): ?>
<button class="fab">
    <i class="ri-add-line"></i>
</button>
<?php endif; ?>

<div id="modalAgregar" class="modal-overlay" style="display:none;">
  <div class="modal-content">
    <div class="modal-header">
      <h2><i class="ri-archive-line"></i> Nuevo Equipo</h2>
      <button id="btnCerrarModal" class="close-btn"><i class="ri-close-line"></i></button>
    </div>
    
    <form id="formAgregar">
      <div class="form-group">
        <label>Nombre del equipo</label>
        <input type="text" name="nombre" required placeholder="Ej. Extintor PQS 5kg">
      </div>

      <div class="form-group">
        <label>Código (Inventario)</label>
        <input type="text" name="codigo" required placeholder="Ej. EXT-001">
      </div>

      <div class="row" style="display:flex; gap:15px;">
        <div class="form-group" style="flex:1;">
          <label>Categoría</label>
          <select name="id_categoria" required>
            <option value="1">Extintores</option>
            <option value="2">Equipo médico</option>
            <option value="3">Herramientas</option>
          </select>
        </div>
        <div class="form-group" style="flex:1;">
          <label>Stock</label>
          <input type="number" name="stock" required min="1" value="1">
        </div>
      </div>

      <div class="form-group">
        <label>Ubicación</label>
        <input type="text" name="ubicacion" placeholder="Ej. Pasillo 3, Estante A">
      </div>

      <button type="submit" class="btn-save">Guardar Equipo</button>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.js"></script>

<script src="/LeoAlmacen/assets/js/dashboard.js"></script>
</body>
</html>