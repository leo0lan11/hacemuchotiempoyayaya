-- Crear tabla para comentarios de productos
CREATE TABLE IF NOT EXISTS comentarios (
  id_comentario INT AUTO_INCREMENT PRIMARY KEY,
  id_producto INT NOT NULL,
  usuario VARCHAR(100) NOT NULL,
  comentario TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (id_producto) REFERENCES productos(id_producto) ON DELETE CASCADE,
  INDEX idx_producto (id_producto)
);
