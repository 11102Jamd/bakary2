<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .title {
            font-size: 18px;
            font-weight: bold;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .order-header {
            background-color: #e9ecef;
            font-weight: bold;
        }
        .order-total {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        .grand-total {
            font-size: 16px;
            font-weight: bold;
            text-align: right;
            margin-top: 20px;
        }
        .page-break {
            page-break-after: always;
        }
        .text-right {
            text-align: right;
        }
    </style>
    <title>Reporte de Producciones</title>
</head>
<body>
    <div class="header">
        <div class="title">Reporte de Producciones</div>
        <div class="period">
            Período: {{ $startDate ?? 'N/A' }} al {{ $endDate ?? 'N/A' }}
        </div>
        <div class="generated">
            Generado el: {{ $generateAt ?? 'Fecha no disponible' }}
        </div>
    </div>

    @foreach ($productions as $production)
        @php
            $firstProductProduction = $production->productProduction->first();
        @endphp

        <table>
            <tr class="order-header">
                <td colspan="5">
                    Producción N° {{ $production->id }} -
                    Receta: {{ $production->recipe->name ?? 'Sin Receta' }} -
                    Producto: {{ $firstProductProduction->product->name ?? 'Sin producto' }} -
                    Fecha: {{ Carbon\Carbon::parse($production->production_date)->format('d/m/Y') }}
                    Ganancia: {{$firstProductProduction->profit_margin_porcentage ?? 'Sin Ganancia'}} %
                </td>
            </tr>
            <tr>
                <th>Insumo</th>
                <th>Cantidad Gastada</th>
                <th>Precio Unitario</th>
                <th>Subtotal</th>
                <th>Lote N°</th>
            </tr>

            @foreach ($production->consumptions as $consumption)
                <tr>
                    <td>{{ $consumption->batch->input->name ?? 'N/A' }}</td>
                    <td>{{ $consumption->quantity_used }} g</td>
                    <td class="text-right">${{ number_format($consumption->unit_cost ?? 0, 2) }}</td>
                    <td class="text-right">${{ number_format($consumption->total_cost, 2) }}</td>
                    <td>{{ $consumption->batch->batch_number ?? 'N/A' }}</td>
                </tr>
            @endforeach

            <tr class="order-total">
                <td>Precio por producto</td>
                <td class="text-right">${{ number_format($production->price_for_product ?? 0, 2) }}</td>
            </tr>
            <tr class="order-total">
                <td colspan="4">Total Producción:</td>
                <td class="text-right">${{ number_format($production->total_cost, 2) }}</td>
            </tr>
        </table>

        @if (!$loop->last)
            <div class="page-break"></div>
        @endif
    @endforeach

    <div class="grand-total">
        Total de las Producciones: ${{ number_format($totalProductions, 2) }}
    </div>
</body>
</html>
