document.addEventListener("DOMContentLoaded", () => {

    // ================= 1. INICIALIZAR NOTYF =================
    // Configuración para que salga arriba a la derecha
    const notyf = new Notyf({
        position: { x: 'right', y: 'top' },
        duration: 4000, // Dura 4 segundos
        ripple: true,
        dismissible: true
    });

    // Mensaje de bienvenida (Opcional, se ve bonito al cargar)
    // notyf.success('Bienvenido al sistema de almacén'); 

    // ================= DOM ELEMENTS =================
    const grid = document.getElementById("gridProductos");
    const buscador = document.getElementById("buscador");
    const kpiTotal = document.getElementById("kpiTotal");
    const kpiOperativo = document.getElementById("kpiOperativo");
    const kpiRevision = document.getElementById("kpiRevision");
    const kpiFuera = document.getElementById("kpiFuera");
    const filters = document.querySelectorAll(".filter");
    
    // Modal Elements
    const modal = document.getElementById("modalAgregar");
    const btnFab = document.querySelector(".fab");
    const btnCerrar = document.getElementById("btnCerrarModal");
    const formAgregar = document.getElementById("formAgregar");

    let productosGlobal = [];

    // ================= CARGA INICIAL =================
    fetch("/LeoAlmacen/backend/productos/productos.php")
        .then(res => res.json())
        .then(data => {
            productosGlobal = data;
            renderProductos(productosGlobal);
            actualizarKPIs(productosGlobal);
        })
        .catch(error => {
            console.error('Error:', error);
            notyf.error('Error al cargar los productos');
        });

    // ================= HELPER IMÁGENES =================
    function obtenerImagen(categoria) {
        if (!categoria) return "mockup.png";
        categoria = categoria.toLowerCase();

        if (categoria.includes("extintor")) return "extintor.png";
        if (categoria.includes("radio")) return "radio.png";
        if (categoria.includes("bocina")) return "bocinas.png";
        if (categoria.includes("laptop")) return "laptop.png";
        if (categoria.includes("camilla")) return "camilla.png";
        if (categoria.includes("botiquin") || categoria.includes("botiquín")) return "botiquin.png";

        return "mockup.png";
    }

    // ================= RENDERIZAR =================
    function renderProductos(data) {
        grid.innerHTML = "";

        if (data.length === 0) {
            grid.innerHTML = `<p style="grid-column: 1/-1; text-align: center; color: #999; margin-top: 20px;">No se encontraron productos.</p>`;
            return;
        }

        data.forEach(p => {
            let color = "#2e7d32"; 
            if (p.estado === "Revisión" || p.estado === "En revisión") color = "#f9a825";
            if (p.estado === "Fuera de servicio") color = "#c62828";

const img = (p.imagen && p.imagen !== "")
    ? p.imagen
    : obtenerImagen(p.categoria);

console.log(p);

const card = document.createElement('div');
card.className = 'card';
card.setAttribute('data-status', p.estado);
card.style.cursor = 'pointer';
card.setAttribute('data-id', p.id_producto);

card.innerHTML = `
    <div class="badge" style="color:${color}">
        <i class="ri-checkbox-blank-circle-fill" style="font-size:8px; vertical-align:middle;"></i> ${p.estado}
    </div>

    <div class="img">
        <img src="/LeoAlmacen/assets/img/${img}" alt="${p.nombre}" onerror="this.src='/LeoAlmacen/assets/img/error.png'">
    </div>

    <div class="info">
        <div class="title" title="${p.nombre}">${p.nombre}</div>
        <div class="stock">
           <i class="ri-stack-line"></i> Stock: <strong>${p.stock}</strong>
        </div>
    </div>
`;

card.addEventListener('click', () => {
    window.location.href = '/LeoAlmacen/pages/detail.php?id=' + p.id_producto;
});

grid.appendChild(card);

        });
    }

    // ================= KPIs =================
    function actualizarKPIs(data) {
        kpiTotal.textContent = data.length;
        kpiOperativo.textContent = data.filter(p => p.estado === "Operativo").length;
        kpiRevision.textContent = data.filter(p => p.estado === "Revisión" || p.estado === "En revisión").length;
        kpiFuera.textContent = data.filter(p => p.estado === "Fuera de servicio").length;
    }

    // ================= BUSCADOR & FILTROS =================
    buscador.addEventListener("keyup", () => {
        const texto = buscador.value.toLowerCase();
        const filtrados = productosGlobal.filter(p =>
            (p.nombre && p.nombre.toLowerCase().includes(texto)) ||
            (p.categoria && p.categoria.toLowerCase().includes(texto)) ||
            (p.codigo && p.codigo.toLowerCase().includes(texto))
        );
        renderProductos(filtrados);
    });

    filters.forEach(f => {
        f.addEventListener("click", () => {
            filters.forEach(x => x.classList.remove("active"));
            f.classList.add("active");
            const estadoFiltro = f.dataset.filter;

            if (estadoFiltro === "Todos") {
                renderProductos(productosGlobal);
            } else {
                if (estadoFiltro === "Revisión") {
                    renderProductos(productosGlobal.filter(p => p.estado === "Revisión" || p.estado === "En revisión"));
                } else {
                    renderProductos(productosGlobal.filter(p => p.estado === estadoFiltro));
                }
            }
        });
    });

    // ================= MODAL & GUARDAR =================
    btnFab.addEventListener("click", () => { modal.style.display = "flex"; });
    btnCerrar.addEventListener("click", () => { modal.style.display = "none"; });
    window.addEventListener("click", (e) => { if (e.target == modal) modal.style.display = "none"; });

    formAgregar.addEventListener("submit", (e) => {
        e.preventDefault();

        const formData = new FormData(formAgregar);

        fetch("/LeoAlmacen/backend/productos/agregar.php", {
            method: "POST",
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // CAMBIO AQUÍ: Usamos Notyf en vez de alert
                notyf.success("¡Equipo guardado correctamente!");
                
                modal.style.display = "none";
                formAgregar.reset();
                
                // Recargar datos
                fetch("/LeoAlmacen/backend/productos/productos.php")
                    .then(res => res.json())
                    .then(nuevosDatos => {
                        productosGlobal = nuevosDatos;
                        renderProductos(productosGlobal);
                        actualizarKPIs(productosGlobal);
                    });

            } else {
                // CAMBIO AQUÍ: Error con Notyf
                notyf.error(data.error || "Hubo un error al guardar");
            }
        })
        .catch(err => {
            console.error("Error:", err);
            notyf.error("Error de conexión con el servidor");
        });
    });

});