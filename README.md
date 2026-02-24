# Sistema de Gestión Comercial - Backend

API REST desarrollada en PHP con PostgreSQL para la gestión de inventario,
compras, ventas y control de movimientos en un sistema tipo mini súper o tienda comercial.

---

## Características

- Autenticación con JWT
- Gestión de usuarios con roles (admin, cajero, compras, ventas)
- Gestión de productos y categorías
- Gestión de proveedores
- Registro de compras con actualización automática de stock
- Registro de ventas con control de inventario
- Movimientos de inventario (entrada, salida, ajuste)
- Control de stock mínimo
- Validaciones estrictas y tipado fuerte
- Optimización con índices en base de datos

---

## Tecnologías Utilizadas

- PHP 8+
- PostgreSQL
- PDO
- JWT
- Arquitectura MVC básica
- SQL con restricciones y claves foráneas

---

## Estructura del Proyecto

<pre>
├── 📁 backend
│   ├── 📁 config
│   │   ├── 🐘 database.php
│   │   └── 🐘 env.php
│   ├── 📁 controllers
│   │   ├── 🐘 AuthController.php
│   │   ├── 🐘 CategoriaController.php
│   │   ├── 🐘 CompraController.php
│   │   ├── 🐘 HealthController.php
│   │   ├── 🐘 MovimientoInventarioController.php
│   │   ├── 🐘 ProductoController.php
│   │   ├── 🐘 ProveedorController.php
│   │   ├── 🐘 UserController.php
│   │   └── 🐘 VentaController.php
│   ├── 📁 models
│   │   ├── 🐘 Categoria.php
│   │   ├── 🐘 Compra.php
│   │   ├── 🐘 MovimientoInventario.php
│   │   ├── 🐘 Producto.php
│   │   ├── 🐘 Proveedor.php
│   │   ├── 🐘 Usuario.php
│   │   └── 🐘 Venta.php
│   ├── 📁 routes
│   │   └── 🐘 api.php
│   ├── 📁 utils
│   │   ├── 🐘 authMiddleware.php
│   │   ├── 🐘 response.php
│   │   └── 🐘 roleMiddleware.php
│   └── 🐘 index.php
├── 📁 frontend
│   ├── 📁 assets
│   │   ├── 📁 fonts
│   │   │   ├── 📄 Montserrat-Bold.ttf
│   │   │   ├── 📄 Montserrat-Light.ttf
│   │   │   └── 📄 Montserrat-Regular.ttf
│   │   ├── 📁 icons
│   │   └── 📁 img
│   ├── 📁 css
│   │   ├── 📁 base
│   │   │   ├── 🎨 fonts.css
│   │   │   ├── 🎨 reset.css
│   │   │   └── 🎨 variables.css
│   │   ├── 📁 layout
│   │   │   ├── 🎨 footer.css
│   │   │   ├── 🎨 header.css
│   │   │   └── 🎨 sidebar.css
│   │   ├── 📁 pages
│   │   │   ├── 🎨 categorias.css
│   │   │   ├── 🎨 compras.css
│   │   │   ├── 🎨 dashboard.css
│   │   │   ├── 🎨 login.css
│   │   │   ├── 🎨 productos.css
│   │   │   ├── 🎨 proveedores.css
│   │   │   ├── 🎨 register.css
│   │   │   └── 🎨 ventas.css
│   │   └── 🎨 main.css
│   ├── 📁 js
│   │   ├── 📄 api.js
│   │   ├── 📄 auth.js
│   │   ├── 📄 categorias.js
│   │   ├── 📄 compras.js
│   │   ├── 📄 productos.js
│   │   ├── 📄 proveedores.js
│   │   └── 📄 ventas.js
│   ├── 📁 pages
│   │   ├── 🌐 categorias.html
│   │   ├── 🌐 compras.html
│   │   ├── 🌐 dashboard.html
│   │   ├── 🌐 login.html
│   │   ├── 🌐 productos.html
│   │   ├── 🌐 proveedores.html
│   │   ├── 🌐 register.html
│   │   └── 🌐 ventas.html
│   └── 🌐 index.html
├── 📁 sql
│   └── 📄 schema.sql
├── ⚙️ .gitignore
├── 📝 README.md
└── 🐘 index.php
</pre>

---

## Base de Datos

El sistema incluye:

- Relaciones con claves foráneas
- Restricciones CHECK
- ENUMs personalizados
- Índices optimizados
- Subtotales generados automáticamente en detalle_venta

---

## Roles del Sistema

- **admin** → Control total
- **cajero** → Gestión de ventas
- **compras** → Registro de compras
- **ventas** → Gestión de ventas y consultas

---

## Instalación

1. Clonar repositorio
2. Crear base de datos en PostgreSQL
3. Ejecutar el script SQL incluido
4. Configurar conexión en `database.php`
5. Iniciar servidor PHP:

---

## Endpoints Principales

### Autenticación

- POST /login

### Productos

- GET /productos
- POST /productos
- PUT /productos/{id}
- DELETE /productos/{id}

### Compras

- POST /compras
- GET /compras

### Ventas

- POST /ventas
- GET /ventas

### Movimientos de Inventario

- POST /movimientos
- GET /movimientos

---

## Estado del Proyecto

✔ Backend funcional  
✔ Relaciones y transacciones seguras  
✔ Control de inventario consistente  
✔ Optimización de rendimiento con índices  
✔ Validaciones robustas

Proyecto listo para pruebas y demostración académica o implementación en pequeña escala.

---

## Autor

Nazario Crstobál Julio Leonardo

---
