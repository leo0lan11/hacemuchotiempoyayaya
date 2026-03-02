<?php
session_start();
require_once "../backend/conexion/conexion.php";

if (!isset($_GET['id'])) {
    die("Solicitud no válida");
}

$id = (int)$_GET['id'];

/* Obtener solicitud */
$stmt = $conexion->prepare(
    "SELECT * FROM solicitudes WHERE id_solicitud = ?"
);
$stmt->bind_param("i", $id);
$stmt->execute();
$solicitud = $stmt->get_result()->fetch_assoc();

/* Obtener detalle */
$stmtDetalle = $conexion->prepare(
    "SELECT nombre_equipo, cantidad
     FROM solicitud_detalle
     WHERE id_solicitud = ?"
);
$stmtDetalle->bind_param("i", $id);
$stmtDetalle->execute();
$detalles = $stmtDetalle->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Vale de Préstamo</title>

<style>
@page { size: letter; margin: 10mm; }

body{
  font-family: Arial, sans-serif;
  font-size:10px;
  color:#000;
}

.section{
  margin-bottom:10px;
}

.header{
  text-align:center;
  margin-bottom:5px;
}

.header h2{
  margin:0;
  font-size:12px;
}

.header strong{
  font-size:10px;
}

.row{
  margin:3px 0;
  clear:both;
}

.label{
  font-weight:bold;
}

.line{
  border-bottom:1px solid #000;
  display:inline-block;
  min-width:180px;
  height:11px;
  vertical-align:bottom;
}

table{
  width:100%;
  border-collapse:collapse;
  margin-top:5px;
  font-size:9px;
}

th, td{
  border:1px solid #000;
  padding:2px;
  text-align:center;
}

.note{
  text-align:right;
  font-size:8px;
  margin-top:3px;
}

.signatures{
  margin-top:10px;
  display:flex;
  justify-content:space-between;
}

.sign{
  width:30%;
  text-align:center;
}

.sign .line{
  margin-top:15px;
  width:100%;
}

.obs{
  margin-top:6px;
}

.obs .line{
  width:100%;
  margin-top:3px;
}

.cut-line{
  border-top:1px dashed #000;
  margin:8px 0;
}

.print-btn{
  text-align:center;
  margin-top:10px;
}

@media print{
  .print-btn{ display:none; }
}
</style>
</head>

<body>

<?php for($copia=1;$copia<=2;$copia++): ?>

<div class="section">

<div class="header">
  <h2>VALE DE PRÉSTAMO O SALIDA TEMPORAL DE BIENES</h2>
  <strong>EQUIPO MÉDICO, EQUIPO DE CÓMPUTO, DIDÁCTICO, MOBILIARIO</strong>
</div>

<div class="row">
  <span class="label">Nombre del Solicitante:</span>
  <span class="line"><?= htmlspecialchars($solicitud['usuario']) ?></span>

  <span style="float:right">
    <span class="label">Fecha de solicitud:</span>
    <span class="line"><?= date("d/m/Y") ?></span>
  </span>
</div>

<div class="row">
  <span class="label">Motivo del préstamo:</span>
  <span class="line" style="width:60%"></span>
</div>

<div class="row">
  <span class="label">Área donde se encontrará el bien:</span>
  <span class="line" style="width:45%"></span>

  <span style="float:right">
    <span class="label">Fecha devolución:</span>
    <span class="line" style="width:100px"></span>
  </span>
</div>

<div class="row">
  <span class="label">Responsable del resguardo:</span>
  <span class="line" style="width:65%"></span>
</div>

<div class="row">
  <span class="label">Área a la que pertenece:</span>
  <span class="line" style="width:60%"></span>
</div>

<div class="note">
  Nota: El ID de Activo deberá ser validado por Activo Fijo.
</div>

<table>
<tr>
  <th>Cantidad</th>
  <th>Descripción</th>
  <th>Marca</th>
  <th>Modelo</th>
  <th>No. Serie</th>
  <th>ID Activo</th>
</tr>

<?php foreach ($detalles as $d): ?>
<tr>
  <td><?= $d['cantidad'] ?></td>
  <td><?= htmlspecialchars($d['nombre_equipo']) ?></td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
</tr>
<?php endforeach; ?>

<?php for($i=0;$i<2;$i++): ?>
<tr>
  <td>&nbsp;</td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
</tr>
<?php endfor; ?>

</table>

<div class="signatures">
  <div class="sign">
    ENTREGA
    <div class="line"></div>
    NOMBRE Y FIRMA
  </div>

  <div class="sign">
    RECIBE
    <div class="line"></div>
    NOMBRE Y FIRMA
  </div>

  <div class="sign">
    AUTORIZA
    <div class="line"></div>
    NOMBRE Y FIRMA
  </div>
</div>

<div class="obs">
  <strong>OBSERVACIONES:</strong>
  <div class="line"></div>
</div>

</div>

<?php if($copia==1): ?>
<div class="cut-line"></div>
<?php endif; ?>

<?php endfor; ?>

<div class="print-btn">
  <button onclick="window.print()">🖨 Imprimir</button>
</div>

</body>
</html>
