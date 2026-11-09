-- Creación de la Base de Datos
CREATE DATABASE "DB_Almacen"
    WITH
    OWNER = postgres
    ENCODING = 'UTF8'
    LOCALE_PROVIDER = 'libc'
    CONNECTION LIMIT = -1
    IS_TEMPLATE = False;

-- ESQUEMAS
CREATE SCHEMA InfoPersona;
CREATE SCHEMA InfoLogistica;


-- ESQUEMA: InfoPersona

-- ====== TABLA: Cliente =====
CREATE TABLE InfoPersona.Cliente (
	id_cliente BIGSERIAL NOT NULL,
	nombre_cliente VARCHAR(255) NOT NULL,
	telefono_cliente VARCHAR(10) NOT NULL,
	correo_electronico VARCHAR(255) NOT NULL,
	fecha_registro DATE DEFAULT CURRENT_DATE NOT NULL,
	estado VARCHAR(15) DEFAULT 'Activo' NOT NULL,

	CONSTRAINT pk_cliente PRIMARY KEY (id_cliente)
);

-- Correo electrónico y número único
ALTER TABLE InfoPersona.Cliente
	ADD CONSTRAINT uq_cliente_correo UNIQUE (correo_electronico),
	ADD CONSTRAINT uq_cliente_telefono UNIQUE (telefono_cliente);

-- Validaciones de datos
ALTER TABLE InfoPersona.Cliente
	ADD CONSTRAINT chk_cliente_estado CHECK (estado IN ('Activo', 'Inactivo')),
	ADD CONSTRAINT chk_cliente_telefono CHECK (telefono_cliente ~ '^[0-9]{10}$');


-- ====== TABLA: Domicilio Cliente =====
CREATE TABLE InfoPersona.Domicilio_Cliente (
	id_domicilio BIGSERIAL NOT NULL,
	id_cliente BIGINT NOT NULL,
	domicilio VARCHAR(200) NOT NULL,

	CONSTRAINT pk_domicilio PRIMARY KEY (id_domicilio),
	CONSTRAINT fk_domicilio_cliente FOREIGN KEY (id_cliente)
		REFERENCES InfoPersona.Cliente(id_cliente)
);


-- ===== TABLA: Proveedor =====
CREATE TABLE InfoPersona.Proveedor (
	id_proveedor BIGSERIAL NOT NULL,
	nombre_proveedor VARCHAR(200) NOT NULL,
	nombre_empresa VARCHAR(200) NOT NULL,
	telefono_proveedor VARCHAR(10) NOT NULL,
	correo_empresa VARCHAR(255) NOT NULL,

	CONSTRAINT pk_proveedor PRIMARY KEY (id_proveedor)
);

-- Correo electrónico y número único
ALTER TABLE InfoPersona.Proveedor
	ADD CONSTRAINT uq_proveedor_correo UNIQUE (correo_empresa),
	ADD CONSTRAINT uq_proveedor_telefono UNIQUE (telefono_proveedor);

-- Validaciones de datos
ALTER TABLE InfoPersona.Proveedor
	ADD CONSTRAINT chk_proveedor_telefono CHECK (telefono_proveedor ~ '^[0-9]{10}$');


-- ===== TABLA: Transportista =====
CREATE TABLE InfoPersona.Transportista (
	id_transportista BIGSERIAL NOT NULL,
	nombre_transportista VARCHAR(200) NOT NULL,
	telefono_transportista VARCHAR(10) NOT NULL,
	correo_transportista VARCHAR(255) NOT NULL,
	estado VARCHAR(15) DEFAULT 'Disponible' NOT NULL,
	fecha_contratacion DATE NOT NULL,

	CONSTRAINT pk_transportista PRIMARY KEY (id_transportista)
);

-- Correo electrónico y número único
ALTER TABLE InfoPersona.Transportista
	ADD CONSTRAINT uq_transportista_correo UNIQUE (correo_transportista),
	ADD CONSTRAINT uq_transportista_telefono UNIQUE (telefono_transportista);

-- Validaciones de datos
ALTER TABLE InfoPersona.Transportista
	ADD CONSTRAINT chk_transportista_estado CHECK (estado IN ('Disponible', 'En servicio')),
	ADD CONSTRAINT chk_transportista_telefono CHECK (telefono_transportista ~ '^[0-9]{10}$');


-- ESQUEMA: InfoLogistica

-- ===== TABLA: Transporte =====
CREATE TABLE InfoLogistica.Transporte (
	id_transporte BIGSERIAL NOT NULL,
	placa VARCHAR(10) NOT NULL,
	tipo_unidad VARCHAR(200) NOT NULL,
	marca VARCHAR(100) NOT NULL,
	modelo VARCHAR(100) NOT NULL,
	estado VARCHAR(20) DEFAULT 'Disponible' NOT NULL,

	CONSTRAINT pk_transporte PRIMARY KEY (id_transporte)
);

-- Placa única
ALTER TABLE InfoLogistica.Transporte
	ADD CONSTRAINT uq_transporte_placa UNIQUE (placa);

-- Validaciones de datos (sustituyen a las reglas de SQL Server)
ALTER TABLE InfoLogistica.Transporte
	ADD CONSTRAINT chk_transporte_tipo_unidad CHECK (tipo_unidad IN
		('Tractocamión', 'Camión de Caja Cerrada', 'Van de Carga', 'Furgoneta Compacta', 'Camioneta Pick-Up')),
	ADD CONSTRAINT chk_transporte_marca CHECK (marca IN
		('Freightliner', 'Isuzu', 'Ford', 'Peugeot', 'Toyota')),
	ADD CONSTRAINT chk_transporte_modelo CHECK (modelo IN
		('Cascadia', 'ELF 600', 'Transit', 'Partner', 'Hilux')),
	ADD CONSTRAINT chk_transporte_estado CHECK (estado IN
		('Disponible', 'En tránsito', 'Mantenimiento'));


-- ===== TABLA: Ruta =====
CREATE TABLE InfoLogistica.Ruta (
	id_ruta BIGSERIAL NOT NULL,
	id_transporte BIGINT NOT NULL,
	id_transportista BIGINT NOT NULL,
	hora_salida TIME NOT NULL,
	hora_estimada_llegada TIME NOT NULL,
	zona_ruta VARCHAR(200) NOT NULL,
	distancia_estimada DECIMAL(10, 2) NOT NULL,
	estado_ruta VARCHAR(200) NOT NULL,
	nombre_ruta VARCHAR(200) NOT NULL,
	fecha_ruta DATE NOT NULL,

	CONSTRAINT pk_ruta PRIMARY KEY (id_ruta),
	CONSTRAINT fk_ruta_transporte FOREIGN KEY (id_transporte)
		REFERENCES InfoLogistica.Transporte(id_transporte),
	CONSTRAINT fk_ruta_transportista FOREIGN KEY (id_transportista)
		REFERENCES InfoPersona.Transportista(id_transportista)
);

-- Validaciones de datos
ALTER TABLE InfoLogistica.Ruta
	ADD CONSTRAINT chk_ruta_estado CHECK (estado_ruta IN
		('Programada', 'En curso', 'Completada', 'Cancelada'));
