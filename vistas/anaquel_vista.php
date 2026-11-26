<?php

/*
 * ============================================================
 * anaquel_vista.php
 * ------------------------------------------------------------
 * Esta es la parte visual del módulo de Anaquel.
 *
 * Aquí se muestran:
 * - Los botones Insertar, Modificar y Eliminar.
 * - Los campos Zona, Pasillo, Rack y Nivel.
 * - La tabla con todos los anaqueles.
 *
 * El estado no se modifica manualmente.
 * Se mantiene oculto porque PostgreSQL y los triggers
 * se encargan de manejar Disponible/Ocupado.
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

    <title>Anaquel</title>


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
            max-width: 1150px;
            margin: 40px auto;
            background-color: white;
            padding: 25px;
            border-radius: 6px;

            box-shadow:
                0 2px 10px
                rgba(0, 0, 0, 0.10);
        }


        h1
        {
            margin-top: 0;
        }


        .volver
        {
            display: inline-block;
            margin-bottom: 20px;
            padding: 7px 12px;
            background-color: #eeeeee;
            color: #333333;
            text-decoration: none;
            border-radius: 3px;
            font-size: 13px;
        }


        /*
         * Aquí acomodamos los botones
         * como en el formulario de Visual.
         */
        .botones-superiores
        {
            display: flex;
            gap: 7px;
            margin-bottom: 25px;
        }


        .botones-superiores button
        {
            padding: 7px 14px;
            border: 1px solid #cccccc;
            border-radius: 3px;
            cursor: pointer;
            font-size: 13px;
        }


        .btn-insertar
        {
            background-color: #2563eb;
            color: white;
        }


        .btn-modificar
        {
            background-color: #2563eb;
            color: white;
        }


        .btn-eliminar
        {
            background-color: #dc2626;
            color: white;
        }


        .contenido
        {
            display: grid;
            grid-template-columns: 220px 1fr;
            gap: 35px;
            align-items: start;
        }


        label
        {
            display: block;
            font-weight: bold;
            font-size: 14px;
            margin-top: 13px;
            margin-bottom: 5px;
        }


        input
        {
            width: 100%;
            box-sizing: border-box;
            padding: 9px;
            border: 1px solid #cccccc;
            border-radius: 3px;
        }


        table
        {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }


        th,
        td
        {
            border: 1px solid #dddddd;
            padding: 9px;
            text-align: left;
        }


        th
        {
            background-color: #eeeeee;
            font-weight: bold;
            white-space: nowrap;
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


        .estado-disponible
        {
            color: #18833c;
            background-color: #e4f5e9;
            border-radius: 10px;
            padding: 4px 8px;
            font-size: 12px;
        }


        .estado-ocupado
        {
            color: #9a5c00;
            background-color: #fff0d5;
            border-radius: 10px;
            padding: 4px 8px;
            font-size: 12px;
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
            font-size: 12px;
            margin-top: 4px;
        }


        .tabla-contenedor
        {
            overflow-x: auto;
        }


        @media
        (
            max-width: 850px
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
        Anaquel
    </h1>


    <a
        href="index.html"
        class="volver"
    >
        ← Volver al inicio
    </a>



    <!-- ======================================================
         MENSAJES
         ====================================================== -->

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



    <!-- ======================================================
         FORMULARIO
         ====================================================== -->

    <form
        id="form-anaquel"
        method="POST"
        action="anaquel.php"
    >


        <!-- Token de seguridad -->
        <input
            type="hidden"
            name="csrf"
            value="<?= e($csrf) ?>"
        >


        <!-- Guarda el ID de la fila seleccionada -->
        <input
            type="hidden"
            id="id_ubicacion"
            name="id_ubicacion"
            value="<?= e($datos['id_ubicacion'] ?? '') ?>"
        >


        <!--
            El estado se conserva internamente,
            pero nunca se muestra como campo editable.
        -->
        <input
            type="hidden"
            id="estado"
            name="estado"
            value="<?= e($datos['estado'] ?? 'Disponible') ?>"
        >



        <!-- ==================================================
             BOTONES
             ================================================== -->

        <div class="botones-superiores">


            <button
                type="submit"
                id="btn-insertar"
                name="accion"
                value="guardar"
                class="btn-insertar"
            >
                Insertar
            </button>


            <button
                type="submit"
                id="btn-modificar"
                name="accion"
                value="guardar"
                class="btn-modificar"
            >
                Modificar
            </button>


            <button
                type="submit"
                id="btn-eliminar"
                name="accion"
                value="eliminar"
                class="btn-eliminar"
                formnovalidate
            >
                Eliminar
            </button>


        </div>



        <div class="contenido">


            <!-- ==================================================
                 CAMPOS
                 ================================================== -->

            <section>


                <!-- ZONA -->

                <label for="zona">

                    Zona:

                </label>


                <input
                    type="number"
                    id="zona"
                    name="zona"
                    min="1"
                    step="1"
                    value="<?= e($datos['zona'] ?? '1') ?>"
                    required
                >


                <?php if (!empty($errores['zona'])): ?>

                    <span class="texto-error">

                        <?= e($errores['zona']) ?>

                    </span>

                <?php endif; ?>



                <!-- PASILLO -->

                <label for="pasillo">

                    Pasillo:

                </label>


                <input
                    type="number"
                    id="pasillo"
                    name="pasillo"
                    min="1"
                    step="1"
                    value="<?= e($datos['pasillo'] ?? '1') ?>"
                    required
                >


                <?php if (!empty($errores['pasillo'])): ?>

                    <span class="texto-error">

                        <?= e($errores['pasillo']) ?>

                    </span>

                <?php endif; ?>



                <!-- RACK -->

                <label for="rack">

                    Rack:

                </label>


                <input
                    type="number"
                    id="rack"
                    name="rack"
                    min="1"
                    step="1"
                    value="<?= e($datos['rack'] ?? '1') ?>"
                    required
                >


                <?php if (!empty($errores['rack'])): ?>

                    <span class="texto-error">

                        <?= e($errores['rack']) ?>

                    </span>

                <?php endif; ?>



                <!-- NIVEL -->

                <label for="nivel">

                    Nivel:

                </label>


                <input
                    type="number"
                    id="nivel"
                    name="nivel"
                    min="1"
                    step="1"
                    value="<?= e($datos['nivel'] ?? '1') ?>"
                    required
                >


                <?php if (!empty($errores['nivel'])): ?>

                    <span class="texto-error">

                        <?= e($errores['nivel']) ?>

                    </span>

                <?php endif; ?>


            </section>



            <!-- ==================================================
                 TABLA DE ANAQUELES
                 ================================================== -->

            <section>


                <div class="tabla-contenedor">


                    <table id="tabla-anaqueles">


                        <thead>

                            <tr>

                                <th>
                                    ID Ubicación
                                </th>

                                <th>
                                    Zona
                                </th>

                                <th>
                                    Pasillo
                                </th>

                                <th>
                                    Rack
                                </th>

                                <th>
                                    Nivel
                                </th>

                                <th>
                                    Estado
                                </th>

                            </tr>

                        </thead>



                        <tbody>


                        <?php foreach ($anaqueles as $a): ?>


                            <tr
                                tabindex="0"

                                class="<?=
                                    (int) ($datos['id_ubicacion'] ?? 0)
                                    ===
                                    (int) $a['id_ubicacion']
                                    ? 'fila-seleccionada'
                                    : ''
                                ?>"

                                data-id="<?= (int) $a['id_ubicacion'] ?>"

                                data-zona="<?= e($a['zona']) ?>"

                                data-pasillo="<?= e($a['pasillo']) ?>"

                                data-rack="<?= e($a['rack']) ?>"

                                data-nivel="<?= e($a['nivel']) ?>"

                                data-estado="<?= e($a['estado']) ?>"
                            >


                                <td>

                                    <?= e($a['id_ubicacion']) ?>

                                </td>


                                <td>

                                    <?= e($a['zona']) ?>

                                </td>


                                <td>

                                    <?= e($a['pasillo']) ?>

                                </td>


                                <td>

                                    <?= e($a['rack']) ?>

                                </td>


                                <td>

                                    <?= e($a['nivel']) ?>

                                </td>


                                <td>


                                    <?php if (
                                        $a['estado']
                                        ===
                                        'Disponible'
                                    ): ?>


                                        <span class="estado-disponible">

                                            Disponible

                                        </span>


                                    <?php else: ?>


                                        <span class="estado-ocupado">

                                            <?= e($a['estado']) ?>

                                        </span>


                                    <?php endif; ?>


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
    src="js/anaquel.js"
    defer
></script>


</body>

</html>