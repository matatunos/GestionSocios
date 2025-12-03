<?php

require_once __DIR__ . '/../../vendor/autoload.php';

class InvoicePDFHelper extends TCPDF {
    private $invoice;
    private $lines;
    private $orgData;
    
    /**
     * Generar PDF de factura
     */
    public function generate($invoice, $lines) {
        $this->invoice = $invoice;
        $this->lines = $lines;
        $this->loadOrganizationData();
        
        // Configuración del documento
        $this->SetCreator('GestionSocios');
        $this->SetAuthor($this->orgData['name']);
        $this->SetTitle('Factura ' . $invoice->full_number);
        $this->SetSubject('Factura ' . $invoice->full_number);
        
        // Configuración de página
        $this->SetMargins(15, 15, 15);
        $this->SetHeaderMargin(5);
        $this->SetFooterMargin(10);
        $this->SetAutoPageBreak(true, 25);
        
        // Fuente por defecto
        $this->SetFont('helvetica', '', 10);
        
        // Añadir página
        $this->AddPage();
        
        // Contenido
        $this->renderHeader();
        $this->renderParties();
        $this->renderLines();
        $this->renderTotals();
        $this->renderPaymentInfo();
        $this->renderFooter();
        
        // Retornar PDF
        return $this->Output('', 'S');
    }
    
    /**
     * Cargar datos de la organización
     */
    private function loadOrganizationData() {
        // Por defecto, datos vacíos
        $this->orgData = [
            'name' => 'Asociación',
            'tax_id' => '',
            'address' => '',
            'city' => '',
            'postal_code' => '',
            'phone' => '',
            'email' => '',
            'website' => ''
        ];
        
        // Intentar cargar desde la configuración
        $configFile = __DIR__ . '/../../Config/settings.json';
        if (file_exists($configFile)) {
            $config = json_decode(file_get_contents($configFile), true);
            if (isset($config['organization'])) {
                $this->orgData = array_merge($this->orgData, $config['organization']);
            }
        }
    }
    
    /**
     * Renderizar encabezado de la factura
     */
    private function renderHeader() {
        // Logo (si existe)
        $logoPath = __DIR__ . '/../../public/uploads/logo.png';
        if (file_exists($logoPath)) {
            $this->Image($logoPath, 15, 15, 40);
        }
        
        // Número de factura y estado
        $this->SetFont('helvetica', 'B', 24);
        $this->SetTextColor(37, 99, 235); // Azul
        $this->SetXY(120, 15);
        $this->Cell(75, 10, $this->invoice->full_number, 0, 1, 'R');
        
        // Estado
        $statusColors = [
            'draft' => [254, 243, 199],
            'issued' => [219, 234, 254],
            'paid' => [209, 250, 229],
            'cancelled' => [254, 226, 226]
        ];
        $statusLabels = [
            'draft' => 'BORRADOR',
            'issued' => 'EMITIDA',
            'paid' => 'PAGADA',
            'cancelled' => 'CANCELADA'
        ];
        
        $color = $statusColors[$this->invoice->status] ?? [243, 244, 246];
        $label = $statusLabels[$this->invoice->status] ?? strtoupper($this->invoice->status);
        
        $this->SetFillColor($color[0], $color[1], $color[2]);
        $this->SetTextColor(60, 60, 60);
        $this->SetFont('helvetica', 'B', 10);
        $this->SetXY(120, 27);
        $this->Cell(75, 6, $label, 0, 1, 'R', true);
        
        $this->Ln(15);
    }
    
    /**
     * Renderizar datos de emisor y receptor
     */
    private function renderParties() {
        $y = $this->GetY();
        
        // Emisor (izquierda)
        $this->SetTextColor(107, 114, 128);
        $this->SetFont('helvetica', 'B', 8);
        $this->SetXY(15, $y);
        $this->Cell(90, 5, 'EMISOR', 0, 1);
        
        $this->SetTextColor(17, 24, 39);
        $this->SetFont('helvetica', 'B', 11);
        $this->SetX(15);
        $this->Cell(90, 5, $this->orgData['name'], 0, 1);
        
        $this->SetFont('helvetica', '', 9);
        if ($this->orgData['tax_id']) {
            $this->SetX(15);
            $this->Cell(90, 4, 'NIF: ' . $this->orgData['tax_id'], 0, 1);
        }
        if ($this->orgData['address']) {
            $this->SetX(15);
            $this->Cell(90, 4, $this->orgData['address'], 0, 1);
        }
        if ($this->orgData['postal_code'] || $this->orgData['city']) {
            $this->SetX(15);
            $this->Cell(90, 4, trim($this->orgData['postal_code'] . ' ' . $this->orgData['city']), 0, 1);
        }
        if ($this->orgData['phone']) {
            $this->SetX(15);
            $this->Cell(90, 4, 'Tel: ' . $this->orgData['phone'], 0, 1);
        }
        if ($this->orgData['email']) {
            $this->SetX(15);
            $this->Cell(90, 4, 'Email: ' . $this->orgData['email'], 0, 1);
        }
        
        // Cliente (derecha)
        $this->SetTextColor(107, 114, 128);
        $this->SetFont('helvetica', 'B', 8);
        $this->SetXY(110, $y);
        $this->Cell(85, 5, 'CLIENTE', 0, 1);
        
        $this->SetTextColor(17, 24, 39);
        $this->SetFont('helvetica', 'B', 11);
        $this->SetX(110);
        $customerName = $this->invoice->customer_name;
        if ($this->invoice->customer_type === 'member') {
            $customerName .= ' (Socio #' . $this->invoice->member_number . ')';
        }
        $this->Cell(85, 5, $customerName, 0, 1);
        
        $this->SetFont('helvetica', '', 9);
        if ($this->invoice->customer_tax_id) {
            $this->SetX(110);
            $this->Cell(85, 4, 'NIF: ' . $this->invoice->customer_tax_id, 0, 1);
        }
        if ($this->invoice->customer_address) {
            $this->SetX(110);
            $this->Cell(85, 4, $this->invoice->customer_address, 0, 1);
        }
        if ($this->invoice->customer_postal_code || $this->invoice->customer_city) {
            $this->SetX(110);
            $this->Cell(85, 4, trim($this->invoice->customer_postal_code . ' ' . $this->invoice->customer_city), 0, 1);
        }
        if ($this->invoice->customer_country && $this->invoice->customer_country !== 'España') {
            $this->SetX(110);
            $this->Cell(85, 4, $this->invoice->customer_country, 0, 1);
        }
        if ($this->invoice->customer_email) {
            $this->SetX(110);
            $this->Cell(85, 4, $this->invoice->customer_email, 0, 1);
        }
        if ($this->invoice->customer_phone) {
            $this->SetX(110);
            $this->Cell(85, 4, 'Tel: ' . $this->invoice->customer_phone, 0, 1);
        }
        
        $this->Ln(10);
        
        // Fechas y referencia
        $this->SetFillColor(249, 250, 251);
        $this->SetTextColor(107, 114, 128);
        $this->SetFont('helvetica', 'B', 8);
        
        $dateY = $this->GetY();
        $this->SetXY(15, $dateY);
        $this->Cell(30, 5, 'FECHA EMISIÓN', 0, 0, 'L', true);
        $this->SetTextColor(17, 24, 39);
        $this->SetFont('helvetica', '', 9);
        $this->Cell(50, 5, date('d/m/Y', strtotime($this->invoice->issue_date)), 0, 1, 'L', true);
        
        if ($this->invoice->due_date) {
            $this->SetX(15);
            $this->SetTextColor(107, 114, 128);
            $this->SetFont('helvetica', 'B', 8);
            $this->Cell(30, 5, 'VENCIMIENTO', 0, 0, 'L', true);
            $this->SetTextColor(17, 24, 39);
            $this->SetFont('helvetica', '', 9);
            $this->Cell(50, 5, date('d/m/Y', strtotime($this->invoice->due_date)), 0, 1, 'L', true);
        }
        
        if ($this->invoice->reference) {
            $this->SetX(15);
            $this->SetTextColor(107, 114, 128);
            $this->SetFont('helvetica', 'B', 8);
            $this->Cell(30, 5, 'REFERENCIA', 0, 0, 'L', true);
            $this->SetTextColor(17, 24, 39);
            $this->SetFont('helvetica', '', 9);
            $this->Cell(50, 5, $this->invoice->reference, 0, 1, 'L', true);
        }
        
        $this->Ln(8);
    }
    
    /**
     * Renderizar líneas de factura
     */
    private function renderLines() {
        // Descripción general
        if ($this->invoice->description) {
            $this->SetFillColor(249, 250, 251);
            $this->SetFont('helvetica', 'B', 9);
            $this->SetTextColor(17, 24, 39);
            $this->Cell(180, 5, 'DESCRIPCIÓN', 0, 1, 'L', true);
            $this->SetFont('helvetica', '', 9);
            $this->MultiCell(180, 4, $this->invoice->description, 0, 'L', true);
            $this->Ln(5);
        }
        
        // Cabecera de tabla
        $this->SetFillColor(249, 250, 251);
        $this->SetTextColor(107, 114, 128);
        $this->SetFont('helvetica', 'B', 8);
        
        $this->Cell(70, 7, 'CONCEPTO', 1, 0, 'L', true);
        $this->Cell(20, 7, 'CANTIDAD', 1, 0, 'C', true);
        $this->Cell(25, 7, 'PRECIO', 1, 0, 'R', true);
        $this->Cell(15, 7, 'DTO %', 1, 0, 'C', true);
        $this->Cell(15, 7, 'IVA %', 1, 0, 'C', true);
        $this->Cell(35, 7, 'TOTAL', 1, 1, 'R', true);
        
        // Líneas
        $this->SetTextColor(17, 24, 39);
        $this->SetFont('helvetica', '', 9);
        
        foreach ($this->lines as $line) {
            $startY = $this->GetY();
            
            // Concepto (con descripción si existe)
            $this->SetXY(15, $startY);
            $concept = $line['concept'];
            if ($line['description']) {
                $concept .= "\n" . $line['description'];
            }
            $this->MultiCell(70, 5, $concept, 1, 'L');
            
            $endY = $this->GetY();
            $cellHeight = $endY - $startY;
            
            // Resto de columnas
            $this->SetXY(85, $startY);
            $this->Cell(20, $cellHeight, number_format($line['quantity'], 2), 1, 0, 'C');
            $this->Cell(25, $cellHeight, number_format($line['unit_price'], 2) . ' €', 1, 0, 'R');
            $this->Cell(15, $cellHeight, number_format($line['discount_rate'], 2), 1, 0, 'C');
            $this->Cell(15, $cellHeight, number_format($line['tax_rate'], 2), 1, 0, 'C');
            $this->SetFont('helvetica', 'B', 9);
            $this->Cell(35, $cellHeight, number_format($line['line_total'], 2) . ' €', 1, 1, 'R');
            $this->SetFont('helvetica', '', 9);
        }
        
        $this->Ln(5);
    }
    
    /**
     * Renderizar totales
     */
    private function renderTotals() {
        $this->SetFont('helvetica', '', 9);
        $this->SetTextColor(17, 24, 39);
        
        $x = 125;
        $y = $this->GetY();
        
        // Subtotal
        $this->SetXY($x, $y);
        $this->Cell(35, 6, 'Subtotal:', 0, 0, 'L');
        $this->Cell(25, 6, number_format($this->invoice->subtotal, 2) . ' €', 0, 1, 'R');
        
        // Descuento
        if ($this->invoice->discount_amount > 0) {
            $this->SetX($x);
            $this->Cell(35, 6, 'Descuento:', 0, 0, 'L');
            $this->Cell(25, 6, '-' . number_format($this->invoice->discount_amount, 2) . ' €', 0, 1, 'R');
        }
        
        // Base imponible
        $this->SetX($x);
        $this->Cell(35, 6, 'Base Imponible:', 0, 0, 'L');
        $this->Cell(25, 6, number_format($this->invoice->subtotal - $this->invoice->discount_amount, 2) . ' €', 0, 1, 'R');
        
        // IVA
        $this->SetX($x);
        $this->Cell(35, 6, 'IVA (' . number_format($this->invoice->tax_rate, 2) . '%):', 0, 0, 'L');
        $this->Cell(25, 6, number_format($this->invoice->tax_amount, 2) . ' €', 0, 1, 'R');
        
        // Total
        $this->SetFont('helvetica', 'B', 12);
        $this->SetFillColor(249, 250, 251);
        $this->SetX($x);
        $this->Cell(35, 8, 'TOTAL:', 1, 0, 'L', true);
        $this->Cell(25, 8, number_format($this->invoice->total, 2) . ' €', 1, 1, 'R', true);
        
        $this->Ln(5);
    }
    
    /**
     * Renderizar información de pago
     */
    private function renderPaymentInfo() {
        if (!$this->invoice->payment_method && !$this->invoice->reference) {
            return;
        }
        
        $this->SetFont('helvetica', 'B', 9);
        $this->SetTextColor(17, 24, 39);
        $this->Cell(180, 5, 'INFORMACIÓN DE PAGO', 0, 1, 'L');
        
        $this->SetFont('helvetica', '', 9);
        $this->SetTextColor(60, 60, 60);
        
        if ($this->invoice->payment_method) {
            $methods = [
                'transfer' => 'Transferencia bancaria',
                'cash' => 'Efectivo',
                'card' => 'Tarjeta',
                'check' => 'Cheque',
                'other' => 'Otro'
            ];
            $method = $methods[$this->invoice->payment_method] ?? $this->invoice->payment_method;
            $this->Cell(180, 4, 'Método de pago: ' . $method, 0, 1, 'L');
        }
        
        if ($this->invoice->reference) {
            $this->Cell(180, 4, 'Referencia: ' . $this->invoice->reference, 0, 1, 'L');
        }
        
        $this->Ln(3);
    }
    
    /**
     * Pie de página personalizado
     */
    private function renderFooter() {
        // No usar el método Footer() de TCPDF para tener más control
    }
    
    /**
     * Footer automático de TCPDF
     */
    public function Footer() {
        $this->SetY(-20);
        $this->SetFont('helvetica', 'I', 8);
        $this->SetTextColor(107, 114, 128);
        
        // Línea
        $this->Line(15, $this->GetY(), 195, $this->GetY());
        $this->Ln(2);
        
        // Texto
        $footerText = $this->orgData['name'];
        if ($this->orgData['website']) {
            $footerText .= ' | ' . $this->orgData['website'];
        }
        if ($this->orgData['email']) {
            $footerText .= ' | ' . $this->orgData['email'];
        }
        
        $this->Cell(0, 5, $footerText, 0, 0, 'C');
        $this->Cell(0, 5, 'Página ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, 0, 'R');
    }
}
?>
