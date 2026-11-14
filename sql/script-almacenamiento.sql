
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
CREATE SCHEMA InfoInventario;


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


-- ===== TABLA: Entrega =====
CREATE TABLE InfoLogistica.Entrega (
	id_entrega BIGSERIAL NOT NULL,
	id_ruta BIGINT NOT NULL,
	id_domicilio BIGINT NOT NULL,
	fecha_programada DATE NOT NULL,
	hora_programada TIME NOT NULL,
	fecha_entrega_real DATE NULL,
	hora_entrega_real TIME NULL,
	estado_entrega VARCHAR(200) NOT NULL,

	CONSTRAINT pk_entrega PRIMARY KEY (id_entrega),
	CONSTRAINT fk_entrega_ruta FOREIGN KEY (id_ruta)
		REFERENCES InfoLogistica.Ruta(id_ruta),
	CONSTRAINT fk_entrega_domicilio FOREIGN KEY (id_domicilio)
		REFERENCES InfoPersona.Domicilio_Cliente(id_domicilio)
);


-- ESQUEMA: InfoInventario

-- ===== TABLA: Anaquel =====
CREATE TABLE InfoInventario.Anaquel (
	id_ubicacion BIGSERIAL NOT NULL,
	zona INT NOT NULL,
	pasillo INT NOT NULL,
	rack INT NOT NULL,
	nivel INT NOT NULL,
	estado VARCHAR(200) NOT NULL,

	CONSTRAINT pk_ubicacion PRIMARY KEY (id_ubicacion)
);


-- ===== TABLA: Producto =====
CREATE TABLE InfoInventario.Producto (
	id_producto BIGSERIAL NOT NULL,
	id_proveedor BIGINT NOT NULL,
	id_ubicacion BIGINT NOT NULL,
	nombre_producto VARCHAR(200) NOT NULL,
	precio DECIMAL(10, 2) NOT NULL,
	peso DECIMAL(10, 2) NOT NULL,
	dimensiones VARCHAR(200) NOT NULL, -- anchoXaltoXlargo
	stock INT NOT NULL,

	CONSTRAINT pk_producto PRIMARY KEY (id_producto),
	CONSTRAINT fk_producto_proveedor FOREIGN KEY (id_proveedor)
		REFERENCES InfoPersona.Proveedor(id_proveedor),
	CONSTRAINT fk_producto_ubicacion FOREIGN KEY (id_ubicacion)
		REFERENCES InfoInventario.Anaquel(id_ubicacion)
);

-- Validaciones de datos
ALTER TABLE InfoInventario.Producto
	ADD CONSTRAINT chk_producto_stock CHECK (stock >= 0);


-- ESQUEMA: InfoLogistica

-- ===== TABLA: Detalle Entrega =====
CREATE TABLE InfoLogistica.Detalle_Entrega (
	id_entrega BIGINT NOT NULL,
	id_producto BIGINT NOT NULL,
	cantidad_entregada INT NOT NULL,
	estado_producto VARCHAR(200) NOT NULL,

	CONSTRAINT fk_detalle_entrega FOREIGN KEY (id_entrega)
		REFERENCES InfoLogistica.Entrega(id_entrega),
	CONSTRAINT fk_detalle_producto FOREIGN KEY (id_producto)
		REFERENCES InfoInventario.Producto(id_producto)
);


-- Disparadores

-- ============================================== --
--         DISPARADOR 1: Descontar Stock          --
-- Descripción: Al insertar un DETALLE ENTREGA, se descontará
-- la cantidad entregada del stock correspondiente en
-- la tabla PRODUCTO
-- ============================================== --

-- Disparador 1: Descontar Stock

CREATE OR REPLACE FUNCTION InfoLogistica.fn_descontar_stock()
RETURNS TRIGGER
LANGUAGE plpgsql
AS $$
BEGIN
	UPDATE InfoInventario.Producto
	SET stock = stock - NEW.cantidad_entregada
	WHERE id_producto = NEW.id_producto;

	RETURN NEW;
END;
$$;

CREATE TRIGGER trg_descontar_stock
AFTER INSERT
ON InfoLogistica.Detalle_Entrega
FOR EACH ROW
EXECUTE FUNCTION InfoLogistica.fn_descontar_stock();


-- DISPARADOR 1.1: Manejar las Modificaciones
-- Descripción: Al modificar un DETALLE ENTREGA, se
-- devolverá la cantidad anterior al stock y se
-- descontará la nueva cantidad entregada.

CREATE OR REPLACE FUNCTION InfoLogistica.fn_modificar_stock_detalle()
RETURNS TRIGGER
LANGUAGE plpgsql
AS $$
BEGIN
	UPDATE InfoInventario.Producto
	SET stock = stock + OLD.cantidad_entregada
	WHERE id_producto = OLD.id_producto;

	UPDATE InfoInventario.Producto
	SET stock = stock - NEW.cantidad_entregada
	WHERE id_producto = NEW.id_producto;

	RETURN NEW;
END;
$$;

CREATE TRIGGER trg_modificar_stock_detalle
AFTER UPDATE
ON InfoLogistica.Detalle_Entrega
FOR EACH ROW
EXECUTE FUNCTION InfoLogistica.fn_modificar_stock_detalle();


-- DISPARADOR 1.2: Manejar las Eliminaciones
-- Descripción: Al eliminar un DETALLE ENTREGA, se
-- devolverá la cantidad entregada al stock del
-- producto correspondiente.

CREATE OR REPLACE FUNCTION InfoLogistica.fn_eliminar_stock_detalle()
RETURNS TRIGGER
LANGUAGE plpgsql
AS $$
BEGIN
	UPDATE InfoInventario.Producto
	SET stock = stock + OLD.cantidad_entregada
	WHERE id_producto = OLD.id_producto;

	RETURN OLD;
END;
$$;

CREATE TRIGGER trg_eliminar_stock_detalle
AFTER DELETE
ON InfoLogistica.Detalle_Entrega
FOR EACH ROW
EXECUTE FUNCTION InfoLogistica.fn_eliminar_stock_detalle();


-- ============================================== --
--         DISPARADOR 2: Completar Entrega        --
-- Descripción: Se actualizará el estado entrega de
-- la tabla ENTREGA a completado se actualizará fecha
-- entrega real y hora entrega real con la información
-- del sistema
-- ============================================== --

-- Disparador 2: Completar Entrega

CREATE OR REPLACE FUNCTION InfoLogistica.fn_completar_entrega()
RETURNS TRIGGER
LANGUAGE plpgsql
AS $$
BEGIN
	IF NEW.estado_entrega = 'Completado'
		AND OLD.estado_entrega IS DISTINCT FROM NEW.estado_entrega
	THEN
		NEW.fecha_entrega_real := CURRENT_DATE;
		NEW.hora_entrega_real := LOCALTIME;
	END IF;

	RETURN NEW;
END;
$$;

CREATE TRIGGER trg_completar_entrega
BEFORE UPDATE OF estado_entrega
ON InfoLogistica.Entrega
FOR EACH ROW
EXECUTE FUNCTION InfoLogistica.fn_completar_entrega();


-- ============================================== --
--         DISPARADOR 3: Estado de Transporte     --
--
-- Descripción: Se vinculará un id transporte a un
-- nuevo registro dentro de la tabla RUTA un evento
-- modificará el estado en la tabla TRANSPORTE para
-- reflejar que la unidad se encuentra en tránsito
-- ============================================== --

-- Disparador 3: Estado de Transporte

CREATE OR REPLACE FUNCTION InfoLogistica.fn_estado_transporte()
RETURNS TRIGGER
LANGUAGE plpgsql
AS $$
BEGIN
	IF TG_OP = 'INSERT' OR TG_OP = 'UPDATE' THEN

		IF NEW.estado_ruta IN ('Programada', 'En curso') THEN
			UPDATE InfoLogistica.Transporte
			SET estado = 'En tránsito'
			WHERE id_transporte = NEW.id_transporte;

		ELSIF NEW.estado_ruta IN ('Completada', 'Cancelada') THEN
			UPDATE InfoLogistica.Transporte
			SET estado = 'Disponible'
			WHERE id_transporte = NEW.id_transporte;
		END IF;

	END IF;

	IF TG_OP = 'DELETE' THEN
		UPDATE InfoLogistica.Transporte
		SET estado = 'Disponible'
		WHERE id_transporte = OLD.id_transporte;

		RETURN OLD;
	END IF;

	RETURN NEW;
END;
$$;

CREATE TRIGGER trg_estado_transporte
AFTER INSERT OR UPDATE OR DELETE
ON InfoLogistica.Ruta
FOR EACH ROW
EXECUTE FUNCTION InfoLogistica.fn_estado_transporte();


-- ============================================== --
--         DISPARADOR 4: Estado de Transportista  --
--
-- Descripción: Se asignará id transportista a una
-- RUTA, la base de datos cambiará el valor del campo
-- estado en la tabla TRANSPORTISTA que indicará que
-- el conductor está en servicio y no disponible
-- para otras rutas
-- ============================================== --

-- Disparador 4: Estado de Transportista

CREATE OR REPLACE FUNCTION InfoLogistica.fn_estado_transportista()
RETURNS TRIGGER
LANGUAGE plpgsql
AS $$
BEGIN
	IF TG_OP = 'INSERT' OR TG_OP = 'UPDATE' THEN

		IF NEW.estado_ruta IN ('Programada', 'En curso') THEN
			UPDATE InfoPersona.Transportista
			SET estado = 'En servicio'
			WHERE id_transportista = NEW.id_transportista;

		ELSIF NEW.estado_ruta IN ('Completada', 'Cancelada') THEN
			UPDATE InfoPersona.Transportista
			SET estado = 'Disponible'
			WHERE id_transportista = NEW.id_transportista;
		END IF;

	END IF;

	IF TG_OP = 'DELETE' THEN
		UPDATE InfoPersona.Transportista
		SET estado = 'Disponible'
		WHERE id_transportista = OLD.id_transportista;

		RETURN OLD;
	END IF;

	RETURN NEW;
END;
$$;

CREATE TRIGGER trg_estado_transportista
AFTER INSERT OR UPDATE OR DELETE
ON InfoLogistica.Ruta
FOR EACH ROW
EXECUTE FUNCTION InfoLogistica.fn_estado_transportista();


-- ============================================== --
--         DISPARADOR 5: Ocupar Anaquel           --
--
-- Descripción: Al asignar un id ubicacion de un
-- registro de la tabla PRODUCTO actualizará la tabla
-- ANAQUEL que indicará que el atributo estado pasará
-- a estar ocupado
-- ============================================== --

-- Disparador 5: Ocupar Anaquel

CREATE OR REPLACE FUNCTION InfoInventario.fn_ocupar_anaquel()
RETURNS TRIGGER
LANGUAGE plpgsql
AS $$
BEGIN
	IF TG_OP = 'INSERT' OR TG_OP = 'UPDATE' THEN

		UPDATE InfoInventario.Anaquel
		SET estado = 'Ocupado'
		WHERE id_ubicacion = NEW.id_ubicacion;

	END IF;

	IF TG_OP = 'UPDATE' OR TG_OP = 'DELETE' THEN

		UPDATE InfoInventario.Anaquel a
		SET estado = 'Disponible'
		WHERE a.id_ubicacion = OLD.id_ubicacion
		AND NOT EXISTS (
			SELECT 1
			FROM InfoInventario.Producto p
			WHERE p.id_ubicacion = a.id_ubicacion
		);

	END IF;

	IF TG_OP = 'DELETE' THEN
		RETURN OLD;
	END IF;

	RETURN NEW;
END;
$$;

CREATE TRIGGER trg_ocupar_anaquel
AFTER INSERT OR UPDATE OR DELETE
ON InfoInventario.Producto
FOR EACH ROW
EXECUTE FUNCTION InfoInventario.fn_ocupar_anaquel();


-- ============================================== --
--         DISPARADOR 6: Cancelar Entrega         --
--
-- Descripción: Al cambiar el estado entrega de la
-- tabla ENTREGA por cancelado se actualizará el
-- estado ruta de la tabla RUTA a cancelada
-- ============================================== --

-- Disparador 6: Cancelar Entrega

CREATE OR REPLACE FUNCTION InfoLogistica.fn_cancelar_entrega()
RETURNS TRIGGER
LANGUAGE plpgsql
AS $$
BEGIN
	IF NEW.estado_entrega = 'Cancelado'
		AND OLD.estado_entrega IS DISTINCT FROM NEW.estado_entrega
	THEN
		UPDATE InfoLogistica.Ruta
		SET estado_ruta = 'Cancelada'
		WHERE id_ruta = NEW.id_ruta;
	END IF;

	RETURN NEW;
END;
$$;

CREATE TRIGGER trg_cancelar_entrega
AFTER UPDATE OF estado_entrega
ON InfoLogistica.Entrega
FOR EACH ROW
EXECUTE FUNCTION InfoLogistica.fn_cancelar_entrega();

-- PRUEBAS / CONSULTAS
SELECT * FROM InfoPersona.Cliente;