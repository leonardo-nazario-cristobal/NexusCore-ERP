-- =====================================================
-- 1. LIMPIEZA
-- =====================================================
DROP TABLE IF EXISTS movimientos_inventario CASCADE;
DROP TABLE IF EXISTS detalle_venta CASCADE;
DROP TABLE IF EXISTS ventas CASCADE;
DROP TABLE IF EXISTS detalle_compra CASCADE;
DROP TABLE IF EXISTS compras CASCADE;
DROP TABLE IF EXISTS productos CASCADE;
DROP TABLE IF EXISTS proveedores CASCADE;
DROP TABLE IF EXISTS categorias CASCADE;
DROP TABLE IF EXISTS usuarios CASCADE;

DROP TYPE IF EXISTS tipo_rol CASCADE;
DROP TYPE IF EXISTS tipo_movimiento CASCADE;

-- =====================================================
-- 2. ENUMS
-- =====================================================
CREATE TYPE tipo_rol AS ENUM ('admin', 'cajero', 'compras', 'ventas');
CREATE TYPE tipo_movimiento AS ENUM ('entrada', 'salida', 'ajuste');

-- =====================================================
-- 3. TABLA: USUARIOS
-- =====================================================
CREATE TABLE usuarios (
    id SERIAL PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    correo VARCHAR(120) UNIQUE NOT NULL 
        CHECK (correo ~* '^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$'),
    password TEXT NOT NULL 
        CHECK (length(password) >= 60),
    rol tipo_rol NOT NULL DEFAULT 'cajero',
    activo BOOLEAN NOT NULL DEFAULT TRUE,
    creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- =====================================================
-- 4. TABLA: CATEGORIAS
-- =====================================================
CREATE TABLE categorias (
    id SERIAL PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL UNIQUE,
    descripcion TEXT,
    creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- =====================================================
-- 5. TABLA: PROVEEDORES
-- =====================================================
CREATE TABLE proveedores (
    id SERIAL PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    telefono VARCHAR(30),
    correo VARCHAR(120) 
        CHECK (
            correo IS NULL OR 
            correo ~* '^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$'
        ),
    creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- =====================================================
-- 6. TABLA: PRODUCTOS
-- =====================================================
CREATE TABLE productos (
    id SERIAL PRIMARY KEY,
    codigo_barras VARCHAR(50) UNIQUE,
    nombre VARCHAR(150) NOT NULL UNIQUE,
    descripcion TEXT,
    precio NUMERIC(10,2) NOT NULL CHECK (precio > 0),
    stock INT NOT NULL DEFAULT 0 CHECK (stock >= 0),
    stock_minimo INT NOT NULL DEFAULT 5 CHECK (stock_minimo >= 0),
    id_categoria INT,
    activo BOOLEAN NOT NULL DEFAULT TRUE,
    creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_producto_categoria 
        FOREIGN KEY (id_categoria) 
        REFERENCES categorias(id) 
        ON DELETE SET NULL
);

-- =====================================================
-- 7. TABLA: COMPRAS
-- =====================================================
CREATE TABLE compras (
    id SERIAL PRIMARY KEY,
    fecha TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    total NUMERIC(10,2) NOT NULL DEFAULT 0 CHECK (total >= 0),
    id_proveedor INT NOT NULL,
    creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_compra_proveedor 
        FOREIGN KEY (id_proveedor) 
        REFERENCES proveedores(id)
);

-- =====================================================
-- 8. TABLA: DETALLE_COMPRA
-- =====================================================
CREATE TABLE detalle_compra (
    id SERIAL PRIMARY KEY,
    id_compra INT NOT NULL,
    id_producto INT NOT NULL,
    cantidad INT NOT NULL CHECK (cantidad > 0),
    costo_unitario NUMERIC(10,2) NOT NULL CHECK (costo_unitario >= 0),

    CONSTRAINT fk_detalle_compra_maestro 
        FOREIGN KEY (id_compra) 
        REFERENCES compras(id) 
        ON DELETE CASCADE,

    CONSTRAINT fk_detalle_compra_producto 
        FOREIGN KEY (id_producto) 
        REFERENCES productos(id)
);

-- =====================================================
-- 9. TABLA: VENTAS
-- =====================================================
CREATE TABLE ventas (
    id SERIAL PRIMARY KEY,
    fecha TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    total NUMERIC(10,2) NOT NULL CHECK (total >= 0),
    metodo_pago VARCHAR(50) NOT NULL DEFAULT 'efectivo',
    id_usuario INT NOT NULL,
    creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_venta_usuario 
        FOREIGN KEY (id_usuario) 
        REFERENCES usuarios(id)
);

-- =====================================================
-- 10. TABLA: DETALLE_VENTA
-- =====================================================
CREATE TABLE detalle_venta (
    id SERIAL PRIMARY KEY,
    id_venta INT NOT NULL,
    id_producto INT NOT NULL,
    cantidad INT NOT NULL CHECK (cantidad > 0),
    precio_unitario NUMERIC(10,2) NOT NULL CHECK (precio_unitario >= 0),

    subtotal NUMERIC(10,2) 
        GENERATED ALWAYS AS (cantidad * precio_unitario) STORED,

    CONSTRAINT fk_detalle_venta_maestro 
        FOREIGN KEY (id_venta) 
        REFERENCES ventas(id) 
        ON DELETE CASCADE,

    CONSTRAINT fk_detalle_venta_producto 
        FOREIGN KEY (id_producto) 
        REFERENCES productos(id)
);

-- =====================================================
-- 11. TABLA: MOVIMIENTOS INVENTARIO
-- =====================================================
CREATE TABLE movimientos_inventario (
    id SERIAL PRIMARY KEY,
    id_producto INT NOT NULL,
    tipo tipo_movimiento NOT NULL,
    cantidad INT NOT NULL CHECK (cantidad > 0),
    motivo TEXT,
    id_usuario INT,
    fecha TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_mov_producto 
        FOREIGN KEY (id_producto) 
        REFERENCES productos(id) 
        ON DELETE CASCADE,

    CONSTRAINT fk_mov_usuario 
        FOREIGN KEY (id_usuario) 
        REFERENCES usuarios(id) 
        ON DELETE SET NULL
);

-- =====================================================
-- 12. INDICES PROFESIONALES
-- =====================================================
CREATE INDEX idx_usuario_correo ON usuarios(correo);
CREATE INDEX idx_producto_categoria ON productos(id_categoria);
CREATE INDEX idx_producto_codigo ON productos(codigo_barras);
CREATE INDEX idx_producto_nombre ON productos(nombre);
CREATE INDEX idx_mov_producto ON movimientos_inventario(id_producto);
CREATE INDEX idx_venta_fecha ON ventas(fecha);
CREATE INDEX idx_compra_fecha ON compras(fecha);
CREATE INDEX idx_detalle_compra_compra ON detalle_compra(id_compra);
CREATE INDEX idx_detalle_compra_producto ON detalle_compra(id_producto);
CREATE INDEX idx_venta_usuario ON ventas(id_usuario);
CREATE INDEX idx_detalle_venta_venta ON detalle_venta(id_venta);
CREATE INDEX idx_detalle_venta_producto ON detalle_venta(id_producto);
CREATE INDEX idx_compra_proveedor ON compras(id_proveedor);
CREATE INDEX idx_mov_fecha ON movimientos_inventario(fecha);
CREATE INDEX idx_mov_usuario ON movimientos_inventario(id_usuario);