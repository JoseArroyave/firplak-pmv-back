<!-- resources/views/envio/guia.blade.php -->

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalles de la Guía de Envío</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }

        h1 {
            text-align: center;
            color: #333;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            padding: 10px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        img {
            max-width: 200px;
            display: block;
            margin: 0 auto;
            margin-bottom: 20px;
        }

        .firma {
            margin-top: 30px;
            text-align: center;
        }

        .center-div {
            width: 10%;
            margin: 0 auto;
        }
    </style>
</head>

<body>

    <h1>Detalles de la Guía de Envío</h1>


    <table>
        <tr>
            <td>Dirección de Entrega</td>
            <td>{{ $direccion_entrega }}</td>
        </tr>
        <tr>
            <td>Fecha de Despacho</td>
            <td>{{ $fecha_despacho }}</td>
        </tr>
        <tr>
            <td>Fecha estimada de entrega</td>
            <td>{{ $fecha_entrega }}</td>
        </tr>
        <tr>
            <td>ID del Cliente</td>
            <td>{{ $id_cliente }}</td>
        </tr>
        <tr>
            <td>Apellido</td>
            <td>{{ $apellido }}</td>
        </tr>
        <tr>
            <td>ID de la Guía</td>
            <td>{{ $id_guia }}</td>
        </tr>
        <tr>
            <td>Nombre</td>
            <td>{{ $nombre }}</td>
        </tr>
    </table>

    <!-- Espacio para firma -->
    <div class="firma">
        <p>Firma: ___________________________</p>
    </div>

    <h1>Agregar POD</h1>
    <div class="center-div">
        <img src="data:image/png;base64, {{$qr}}" alt="Red dot" />
    </div>

</body>

</html>