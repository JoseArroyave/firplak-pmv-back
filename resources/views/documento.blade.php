<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Documento de entrega</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 40px;
        }

        h1 {
            color: #333;
            text-align: center;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        tr:hover {
            background-color: #f5f5f5;
        }
    </style>
</head>

<body>

    <h1>Detalles del Documento de entrega</h1>

    <table>
        <thead>
            <tr>
                <th>ID Pedido</th>
                <th>SKU</th>
                <th>Descripción</th>
                <th>Cantidad</th>
                <th>Precio Unitario</th>
                <th>Total</th>
                <th>Días de Fabricación</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $id_pedido }}</td>
                <td>{{ $SKU }}</td>
                <td>{{ $descripcion }}</td>
                <td>{{ $cantidad }}</td>
                <td>{{ $precio }}</td>
                <td>{{ $total }}</td>
                <td>{{ $dias_fabricacion }}</td>
            </tr>
        </tbody>
    </table>

</body>

</html>