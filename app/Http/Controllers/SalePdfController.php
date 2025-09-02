<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\services\PdfService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SalePdfController extends Controller
{
    protected $pdfService;

    public function __construct(PdfService $pdfService)
    {
        $this->pdfService = $pdfService;
    }

    public function exportPdf(Request $request)
    {
        try {

            $request->validate([
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date'
            ]);

            $startDate = Carbon::parse($request->start_date)->startOfDay();
            $endDate = Carbon::parse($request->end_date)->endOfDay();

            $sales = Sale::with(['saleProducts.product','user'])
                ->whereBetween('sale_date', [$startDate, $endDate])
                ->orderBy('sale_date', 'asc')
                ->get();

            if ($sales->isEmpty()) {
                return response()->json([
                    'error' => 'No hay ventas en el rango de fechas establecido'
                ], 404);
            }

            $totalSales = $sales->sum('sale_total');

            $data = [
                'sales' => $sales,
                'totalSales' => $totalSales,
                'startDate' => $startDate->format('d/m/Y'),
                'endDate' => $endDate->format('d/m/Y'),
                'generateAt' => now()->format('d/m/Y')
            ];

            $pdf = $this->pdfService->generatePdf('pdf.sales', $data);

            return response($pdf->output(), 200)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'attachment; filename="reporte-sales.pdf"')
                ->header('Cache-Control', 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0')
                ->header('Pragma', 'no-cache')
                ->header('Expires', '0');

        } catch (\Throwable $th) {
            Log::error('Error al generar PDF:', [
                'message' => $th->getMessage(),
                'file' => $th->getFile(),
                'line' => $th->getLine(),
                'trace' => $th->getTraceAsString(),
            ]);

            return response()->json([
                'error' => 'Error interno al generar el PDF.',
                'details' => env('APP_DEBUG') ? $th->getMessage() : null,
            ], 500);
        }
    }
}
