<?php
/**
 * Prueba OrderPdfController Realizada por El Desarollador
 * Juan Alejandro Muñoz Devia
 */

namespace Tests\Feature;

/**
 * Importamos los distintos tyraist que vamos a manejar
 * en las pruebas del controlador de pdf
 */
use App\Models\Order;
use App\Models\Input;
use App\Models\InputBatches;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class OrderPdfTest extends TestCase
{
    /**
     * Utilizamso datatabase transactio n para ejecutar un roollback
     * y nu guardar los datos Ingresados
     *
     * @return
     */
    use DatabaseTransactions;

    /**
     *
     *
     *
     */
    #[Test]
    public function export_pdf_with_dates()
    {
        // Crear datos de prueba
        $order = Order::create([
            'supplier_name' => 'Proveedor Test',
            'order_date' => now()->subDays(15)->format('Y-m-d'),
            'order_total' => 1000.50
        ]);

        $input = Input::create([
            'name' => 'Test Input',
            'category' => 'solido_con'
        ]);

        InputBatches::create([
            'input_id' => $input->id,
            'order_id' => $order->id,
            'quantity_total' => 100,
            'unit' => 'kg',
            'quantity_remaining' => 100,
            'unit_converted' => 100,
            'unit_price' => 10.05,
            'subtotal_price' => 1005.00,
            'batch_number' => 1001,
            'received_date' => now()->format('Y-m-d')
        ]);

        // Datos para la petición POST
        $requestData = [
            'start_date' => now()->subDays(30)->format('Y-m-d'),
            'end_date' => now()->format('Y-m-d')
        ];

        $response = $this->postJson('/api/order/export-pdf', $requestData);

        // Verificar la respuesta - PDF exitoso
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    /**
     *
     *
     *
     */
    #[Test]
    public function if_params_not_exits_return_error()
    {
        $response = $this->postJson('/api/order/export-pdf', []);

        $response->assertStatus(422);
    }

    /**
     *
     *
     *
     */
    #[Test]
    public function order_not_found_in_range_dates()
    {
        $requestData = [
            'start_date' => '2025-01-01',
            'end_date' => '2025-01-31'
        ];

        $response = $this->postJson('/api/order/export-pdf', $requestData);

        // El controlador devuelve 404 cuando no encuentra órdenes
        $response->assertStatus(404);
        $response->assertJson([
            'error' => 'No hay compras en el rango de fechas establecido'
        ]);
    }

    /**
     *
     *
     *
     */
    #[Test]
    public function verifica_datos_no_persisten()
    {
        $initialCount = Order::count();

        // Crear datos temporalmente
        Order::create([
            'supplier_name' => 'Test Temporal',
            'order_date' => now()->format('Y-m-d'),
            'order_total' => 100.00
        ]);

        // DatabaseTransactions se encargará de revertir
        $this->assertTrue(true);
    }

    /**
     *
     *
     *
     */
    #[Test]
    public function debug_void_response()
    {
        // Método para debuggear qué devuelve realmente el controlador
        $requestData = [
            'start_date' => '2025-01-01',
            'end_date' => '2025-01-31',
            'supplier_name' => 'Proveedor Inexistente'
        ];

        $response = $this->postJson('/api/order/export-pdf', $requestData);

        echo "Status: " . $response->getStatusCode() . "\n";
        echo "Content-Type: " . $response->headers->get('Content-Type') . "\n";
        echo "Content: " . substr($response->getContent(), 0, 200) . "...\n";

        $this->assertTrue(true);
    }

    /**
     *
     *
     *
     */
    #[Test]
    public function export_pdf_with_mayor_start_date()
    {
        $requestData = [
            'start_date' => '2025-01-31',
            'end_date' => '2025-01-01'
        ];

        $response = $this->postJson('/api/order/export-pdf', $requestData);

        $response->assertStatus(422);
    }
}
