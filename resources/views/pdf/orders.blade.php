<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    @vite(['resources/css/app.css']);
    <title>Document</title>
</head>
<body>
    <div class="header">
        <div class="title">Reporte de Compras</div>
        <div class="period">
            Perido:{{ @isset($startDate) ? $startDate : 'N/A'}} al
            {{@isset($endDate) ? $endDate : 'N/A'}}
        </div>
        <div class="generated">
            Generado el: {{$generatedAt ?? 'Fecha no disponible'}}
        </div>
    </div>


    @foreach ($orders as $order)
        <table>
            <tr class="order-header">
                <td colspan="4">
                    Orden # {{$order->id}} - Proveedor: {{$order->supplier_name : 'Sin proveedor'}} -
                    Fecha: {{\Carbon\Carbon::parse($order->order_date)->format('d/m/Y')}}
                </td>
            </tr>
            <tr>
                <th>Insumo</th>
                <th>Cantidad</th>
                <th>Precio Unitario</th>
                <th>Subtotal</th>
                <th>Lote N°</th>
            </tr>

            @foreach ($order->batches as $batch)
                <tr>
                    <td>{{$batch->input->name}}</td>
                    <td>{{$batch->quantity_total}} {{$batch->input->unit}}</td>
                    <td>${{ number_format($batch->unit_price, 2)}}</td>
                    <td>${{ number_format($batch->subtotal_price)}}</td>
                </tr>
            @endforeach

            <tr class="order-total">
                <td colspan="3">Total Compra: </td>
                <td>${{number_format($order->order_total, 2)}}</td>
            </tr>
        </table>

        @if (!$loop->last)
            <div class="page-break"></div>
        @endif
    @endforeach

    <div class="grand-total">
        Total de las Compras Realizadas: ${{number_format($totalOrders, 2)}}
    </div>
</body>
</html>
