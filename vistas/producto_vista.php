<?php

/*
 * ============================================================
 * producto_vista.php
 * ------------------------------------------------------------
 * Parte visual del módulo Producto.
 *
 * Se intenta conservar la misma distribución
 * que tenía el formulario de Visual Studio.
 * ============================================================
 */

?>

<!DOCTYPE html>

<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Producto</title>


    <link
        rel="stylesheet"
        href="css/styles.css"
    >


    <style>

        body
        {
            font-family: Arial, sans-serif;

            background-color: #f5f5f5;

            margin: 0;

            padding: 0;
        }


        .contenedor
        {
            max-width: 1250px;

            margin: 35px auto;

            background-color: white;

            padding: 25px;

            border-radius: 5px;

            box-shadow:
                0 2px 10px
                rgba(0,0,0,.10);
        }


        h1
        {
            margin-top: 0;
        }


        .volver
        {
            display: inline-block;

            margin-bottom: 18px;

            padding: 6px 10px;

            background-color: #eeeeee;

            color: #333333;

            text-decoration: none;

            border-radius: 3px;

            font-size: 12px;
        }


        /*
         * Los tres botones quedan arriba,
         * como en Visual.
         */
        .botones
        {
            display: flex;

            gap: 8px;

            margin-bottom: 25px;
        }


        .botones button
        {
            padding: 7px 18px;

            border: 1px solid #cccccc;

            border-radius: 3px;

            cursor: pointer;
        }


        .insertar
        {
            background-color: #357bd8;
            color: white;
        }


        .modificar
        {
            background-color: #357bd8;
            color: white;
        }


        .eliminar
        {
            background-color: #d9534f;
            color: white;
        }


        /*
         * Campos a la izquierda y tabla a la derecha.
         */
        .contenido
        {
            display: grid;

            grid-template-columns:
                260px 1fr;

            gap: 35px;

            align-items: end;
        }


        label
        {
            display: block;

            font-weight: bold;

            font-size: 13px;

            margin-top: 10px;

            margin-bottom: 4px;
        }


        input,
        select
        {
            width: 100%;

            box-sizing: border-box;

            padding: 7px;

            border: 1px solid #cccccc;

            border-radius: 2px;
        }


        /*
         * Ancho, Alto y Largo aparecen juntos.
         */
        .dimensiones
        {
            display: grid;

            grid-template-columns:
                1fr 1fr 1fr;

            gap: 7px;
        }


        .dimension label
        {
            font-size: 11px;

            text-align: center;

            font-weight: normal;
        }


        .mensaje
        {
            padding: 10px;

            margin-bottom: 15px;

            border-radius: 4px;
        }


        .mensaje-exito
        {
            background-color: #e8f7ed;

            color: #22733d;
        }


        .mensaje-error
        {
            background-color: #fde9e9;

            color: #9b2525;
        }


        .texto-error
        {
            display: block;

            color: #a72222;

            font-size: 11px;

            margin-top: 3px;
        }


        .tabla-contenedor
        {
            overflow-x: auto;
        }


        table
        {
            width: 100%;

            border-collapse: collapse;

            font-size: 12px;
        }


        th,
        td
        {
            border: 1px solid #cccccc;

            padding: 7px;

            text-align: left;

            white-space: nowrap;
        }


        th
        {
            background-color: #eeeeee;
        }


        tbody tr:hover
        {
            background-color: #f1f6ff;

            cursor: pointer;
        }


        .fila-seleccionada
        {
            background-color: #dceaff;
        }


        @media
        (
            max-width: 950px
        )
        {
            .contenido
            {
                grid-template-columns: 1fr;
            }
        }

    </style>

</head>


<body>


<div class="contenedor">


    <h1>
        Producto
    </h1>


    <a
        href="index.html"
        class="volver"
    >
        ← Volver al inicio
    </a>



    <!-- MENSAJES -->

    <?php if (!empty($flash)): ?>


        <?php if (($flash['tipo'] ?? '') === 'exito'): ?>

            <div class="mensaje mensaje-exito">

                <?= e($flash['mensaje'] ?? '') ?>

            </div>


        <?php else: ?>

            <div class="mensaje mensaje-error">

                <?= e($flash['mensaje'] ?? '') ?>

            </div>

        <?php endif; ?>


    <?php endif; ?>


    <?php if (!empty($errores['general'])): ?>

        <div class="mensaje mensaje-error">

            <?= e($errores['general']) ?>

        </div>

    <?php endif; ?>



    <form
        id="form-producto"
        method="POST"
        action="producto.php"
    >


        <!-- TOKEN -->

        <input
            type="hidden"
            name="csrf"
            value="<?= e($csrf) ?>"
        >


        <!-- ID DEL PRODUCTO SELECCIONADO -->

        <input
            type="hidden"
            id="id_producto"
            name="id_producto"
            value="<?= e($datos['id_producto'] ?? '') ?>"
        >



        <!-- ================================================
             BOTONES
             ================================================ -->

        <div class="botones">


            <button
                type="submit"
                id="btn-insertar"
                name="accion"
                value="insertar"
                class="insertar"
            >
                Insertar
            </button>


            <button
                type="submit"
                id="btn-modificar"
                name="accion"
                value="modificar"
                class="modificar"
            >
                Modificar
            </button>


            <button
                type="submit"
                id="btn-eliminar"
                name="accion"
                value="eliminar"
                class="eliminar"
                formnovalidate
            >
                Eliminar
            </button>


        </div>



        <div class="contenido">


            <!-- ============================================
                 CAMPOS
                 ============================================ -->

            <section>


                <!-- NOMBRE -->

                <label for="nombre_producto">

                    Nombre

                </label>


                <input
                    type="text"
                    id="nombre_producto"
                    name="nombre_producto"
                    maxlength="200"
                    value="<?= e($datos['nombre_producto'] ?? '') ?>"
                    required
                >


                <?php if (!empty($errores['nombre_producto'])): ?>

                    <span class="texto-error">

                        <?= e($errores['nombre_producto']) ?>

                    </span>

                <?php endif; ?>



                <!-- PESO -->

                <label for="peso">

                    Peso

                </label>


                <input
                    type="number"
                    id="peso"
                    name="peso"
                    min="0"
                    step="0.01"
                    value="<?= e($datos['peso'] ?? '') ?>"
                    required
                >



                <!-- PRECIO -->

                <label for="precio">

                    Precio

                </label>


                <input
                    type="number"
                    id="precio"
                    name="precio"
                    min="0"
                    step="0.01"
                    value="<?= e($datos['precio'] ?? '') ?>"
                    required
                >



                <!-- DIMENSIONES -->

                <label>

                    Dimensiones

                </label>


                <div class="dimensiones">


                    <div class="dimension">

                        <label for="ancho">
                            Ancho
                        </label>

                        <input
                            type="number"
                            id="ancho"
                            name="ancho"
                            min="0.01"
                            step="0.01"
                            value="<?= e($datos['ancho'] ?? '') ?>"
                            required
                        >

                    </div>


                    <div class="dimension">

                        <label for="alto">
                            Alto
                        </label>

                        <input
                            type="number"
                            id="alto"
                            name="alto"
                            min="0.01"
                            step="0.01"
                            value="<?= e($datos['alto'] ?? '') ?>"
                            required
                        >

                    </div>


                    <div class="dimension">

                        <label for="largo">
                            Largo
                        </label>

                        <input
                            type="number"
                            id="largo"
                            name="largo"
                            min="0.01"
                            step="0.01"
                            value="<?= e($datos['largo'] ?? '') ?>"
                            required
                        >

                    </div>


                </div>



                <!-- STOCK -->

                <label for="stock">

                    Stock

                </label>


                <input
                    type="number"
                    id="stock"
                    name="stock"
                    min="0"
                    step="1"
                    value="<?= e($datos['stock'] ?? '0') ?>"
                    required
                >



                <!-- PROVEEDOR -->

                <label for="id_proveedor">

                    Proveedor

                </label>


                <select
                    id="id_proveedor"
                    name="id_proveedor"
                    required
                >


                    <option value="">

                        Seleccione un proveedor

                    </option>


                    <?php foreach ($proveedores as $p): ?>


                        <option
                            value="<?= (int) $p['id_proveedor'] ?>"

                            <?= (string) ($datos['id_proveedor'] ?? '')
                                ===
                                (string) $p['id_proveedor']
                                ? 'selected'
                                : '' ?>
                        >

                            <?= e(
                                $p['nombre_proveedor']
                                .
                                ' - '
                                .
                                $p['nombre_empresa']
                            ) ?>

                        </option>


                    <?php endforeach; ?>


                </select>



                <!-- UBICACIÓN -->

                <label for="id_ubicacion">

                    Ubicación

                </label>


                <select
                    id="id_ubicacion"
                    name="id_ubicacion"
                    required
                >


                    <option value="">

                        Seleccione una ubicación

                    </option>


                    <?php foreach ($anaqueles as $a): ?>


                        <option
                            value="<?= (int) $a['id_ubicacion'] ?>"

                            <?= (string) ($datos['id_ubicacion'] ?? '')
                                ===
                                (string) $a['id_ubicacion']
                                ? 'selected'
                                : '' ?>
                        >

                            <?= e(
                                'Zona '
                                .
                                $a['zona']
                                .
                                ' - Pasillo '
                                .
                                $a['pasillo']
                                .
                                ' - Rack '
                                .
                                $a['rack']
                                .
                                ' - Nivel '
                                .
                                $a['nivel']
                            ) ?>

                        </option>


                    <?php endforeach; ?>


                </select>
                
                <?php if (!empty($errores['id_ubicacion'])): ?>

                    <span class="texto-error">
                        <?= e($errores['id_ubicacion']) ?>
                    </span>

                <?php endif; ?>


            </section>



            <!-- ============================================
                 TABLA
                 ============================================ -->

            <section>


                <div class="tabla-contenedor">


                    <table id="tabla-productos">


                        <thead>


                            <tr>

                                <th>
                                    ID Producto
                                </th>

                                <th>
                                    Proveedor
                                </th>

                                <th>
                                    Ubicación
                                </th>

                                <th>
                                    Nombre
                                </th>

                                <th>
                                    Precio
                                </th>

                                <th>
                                    Peso
                                </th>

                                <th>
                                    Dimensiones
                                </th>

                                <th>
                                    Stock
                                </th>

                            </tr>


                        </thead>


                        <tbody>


                        <?php foreach ($productos as $p): ?>


                            <?php

                            /*
                             * Las dimensiones vienen guardadas
                             * como por ejemplo:
                             *
                             * 10X10X10
                             *
                             * Aquí las separamos para poder
                             * volverlas a poner en los tres campos.
                             */
                            $partes =
                                preg_split(
                                    '/[xX]/',
                                    (string) $p['dimensiones']
                                );


                            $ancho =
                                $partes[0] ?? '';

                            $alto =
                                $partes[1] ?? '';

                            $largo =
                                $partes[2] ?? '';

                            ?>


                            <tr
                                tabindex="0"

                                data-id="<?= (int) $p['id_producto'] ?>"

                                data-proveedor="<?= (int) $p['id_proveedor'] ?>"

                                data-ubicacion="<?= (int) $p['id_ubicacion'] ?>"

                                data-nombre="<?= e($p['nombre_producto']) ?>"

                                data-precio="<?= e($p['precio']) ?>"

                                data-peso="<?= e($p['peso']) ?>"

                                data-ancho="<?= e($ancho) ?>"

                                data-alto="<?= e($alto) ?>"

                                data-largo="<?= e($largo) ?>"

                                data-stock="<?= e($p['stock']) ?>"
                            >


                                <td>

                                    <?= e($p['id_producto']) ?>

                                </td>


                                <td>

                                    <?= e(
                                        $p['nombre_proveedor']
                                        .
                                        ' - '
                                        .
                                        $p['nombre_empresa']
                                    ) ?>

                                </td>


                                <td>

                                    <?= e(
                                        'Zona '
                                        .
                                        $p['zona']
                                        .
                                        ' - Pasillo '
                                        .
                                        $p['pasillo']
                                        .
                                        ' - Rack '
                                        .
                                        $p['rack']
                                        .
                                        ' - Nivel '
                                        .
                                        $p['nivel']
                                    ) ?>

                                </td>


                                <td>

                                    <?= e($p['nombre_producto']) ?>

                                </td>


                                <td>

                                    <?= e(
                                        number_format(
                                            (float) $p['precio'],
                                            2
                                        )
                                    ) ?>

                                </td>


                                <td>

                                    <?= e($p['peso']) ?>

                                </td>


                                <td>

                                    <?= e($p['dimensiones']) ?>

                                </td>


                                <td>

                                    <?= e($p['stock']) ?>

                                </td>


                            </tr>


                        <?php endforeach; ?>


                        </tbody>


                    </table>


                </div>


            </section>


        </div>


    </form>


</div>



<script
    src="js/producto.js"
    defer
></script>


</body>

</html>