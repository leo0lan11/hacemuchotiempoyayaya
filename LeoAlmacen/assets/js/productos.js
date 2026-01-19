document.addEventListener("DOMContentLoaded", () => {
  fetch("/LeoAlmacen/backend/productos/productos.php")
    .then(res => res.json())
    .then(data => {
      const grid = document.querySelector(".grid");
      grid.innerHTML = "";

      data.forEach(p => {
        let color = "#2e7d32";
        if (p.estado === "Revisión") color = "#f9a825";
        if (p.estado === "Fuera de servicio") color = "#c62828";

        grid.innerHTML += `
          <div class="card">
            <div class="badge" style="color:${color};">
              ● ${p.estado.toUpperCase()}
            </div>
            <div class="img">
              <img src="leoalmacen/assets/img/extintor.png">
            </div>
            <div class="title">${p.nombre}</div>
            <div class="stock">Stock: ${p.stock} unidades</div>
          </div>
        `;
      });
    })
    .catch(err => console.error("Error:", err));
});
s