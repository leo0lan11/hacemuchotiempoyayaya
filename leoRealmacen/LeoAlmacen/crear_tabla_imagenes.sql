-- Crear tabla para múltiples imágenes por producto
CREATE TABLE IF NOT EXISTS producto_imagenes (
  id_imagen INT AUTO_INCREMENT PRIMARY KEY,
  id_producto INT NOT NULL,
  imagen VARCHAR(255) NOT NULL,
  orden INT DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (id_producto) REFERENCES productos(id_producto) ON DELETE CASCADE,
  INDEX idx_producto (id_producto)
);

-- Si ya tienes imágenes en la tabla productos, ejecuta esto para migrarlas:
-- INSERT INTO producto_imagenes (id_producto, imagen, orden)
-- SELECT id_producto, imagen, 0 FROM productos WHERE imagen IS NOT NULL AND imagen != '';
