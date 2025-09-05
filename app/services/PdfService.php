<?php

namespace App\Services;

use Dompdf\Dompdf;
use Dompdf\Options;

class PdfService
{
    public function generatePdf($view, $data = [], $fillename = 'document.pdf')
    {
        $options = new Options();

        // Permite cargar recursos externos
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', true);
        // Define la fuente por defecto
        $options->set('defaultFont', 'Arial');

        //Se crea la instancia de Dompdf con opciones personalizadas
        $dompdf = new Dompdf($options);

        // Carga erl contenido HTML de la vista blade
        $dompdf->loadHtml(view($view, $data)->render());

        // Definir el tamaño de la Hoja y orientacion
        $dompdf->setPaper('A4', 'portrait');

        // Renderiza el PDF
        $dompdf->render();

        // Devuelve el objeto creado a partir de la instancia
        return $dompdf;
    }
}
