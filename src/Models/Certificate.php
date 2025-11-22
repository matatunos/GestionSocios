<?php

class Certificate {
    private $conn;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    /**
     * Generate membership certificate for a member
     */
    public function generateMembershipCertificate($memberId) {
        // Get member data
        $query = "SELECT m.*, mc.name as category_name 
                  FROM members m 
                  LEFT JOIN member_categories mc ON m.category_id = mc.id 
                  WHERE m.id = :member_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':member_id', $memberId);
        $stmt->execute();
        $member = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$member) {
            return false;
        }
        
        // Get organization settings
        $orgSettings = $this->getOrganizationSettings();
        
        // Generate PDF
        return $this->createPDF('membership', $member, $orgSettings);
    }
    
    /**
     * Generate payment certificate for a member
     */
    public function generatePaymentCertificate($memberId, $year = null) {
        if (!$year) {
            $year = date('Y');
        }
        
        // Get member data
        $memberQuery = "SELECT m.*, mc.name as category_name 
                       FROM members m 
                       LEFT JOIN member_categories mc ON m.category_id = mc.id 
                       WHERE m.id = :member_id";
        $stmt = $this->conn->prepare($memberQuery);
        $stmt->bindParam(':member_id', $memberId);
        $stmt->execute();
        $member = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$member) {
            return false;
        }
        
        // Get payments for the year
        $paymentQuery = "SELECT * FROM payments 
                        WHERE member_id = :member_id 
                        AND YEAR(payment_date) = :year 
                        ORDER BY payment_date";
        $stmt = $this->conn->prepare($paymentQuery);
        $stmt->bindParam(':member_id', $memberId);
        $stmt->bindParam(':year', $year);
        $stmt->execute();
        $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $member['payments'] = $payments;
        $member['year'] = $year;
        
        // Get organization settings
        $orgSettings = $this->getOrganizationSettings();
        
        // Generate PDF
        return $this->createPDF('payment', $member, $orgSettings);
    }
    
    /**
     * Generate donation certificate for a donor
     */
    public function generateDonationCertificate($donorId, $year = null) {
        if (!$year) {
            $year = date('Y');
        }
        
        // Get donor data
        $donorQuery = "SELECT * FROM donors WHERE id = :donor_id";
        $stmt = $this->conn->prepare($donorQuery);
        $stmt->bindParam(':donor_id', $donorId);
        $stmt->execute();
        $donor = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$donor) {
            return false;
        }
        
        // Get donations for the year
        $donationQuery = "SELECT * FROM donations 
                         WHERE donor_id = :donor_id 
                         AND YEAR(donation_date) = :year 
                         ORDER BY donation_date";
        $stmt = $this->conn->prepare($donationQuery);
        $stmt->bindParam(':donor_id', $donorId);
        $stmt->bindParam(':year', $year);
        $stmt->execute();
        $donations = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $donor['donations'] = $donations;
        $donor['year'] = $year;
        
        // Get organization settings
        $orgSettings = $this->getOrganizationSettings();
        
        // Generate PDF
        return $this->createPDF('donation', $donor, $orgSettings);
    }
    
    /**
     * Get organization settings
     */
    private function getOrganizationSettings() {
        $settings = [];
        
        $query = "SELECT setting_key, setting_value FROM organization_settings";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        
        return $settings;
    }
    
    /**
     * Create PDF document
     */
    private function createPDF($type, $data, $orgSettings) {
        $autoloadPath = __DIR__ . '/../../vendor/autoload.php';
        
        if (!file_exists($autoloadPath)) {
            error_log('Certificate Error: Composer autoload not found at ' . $autoloadPath);
            throw new Exception('Sistema de generación de PDFs no disponible. Ejecute: composer install');
        }
        
        require_once $autoloadPath;
        
        if (!class_exists('TCPDF')) {
            error_log('Certificate Error: TCPDF class not found after autoload');
            throw new Exception('Librería TCPDF no está instalada. Ejecute: composer require tecnickcom/tcpdf');
        }
        
        try {
            // Create new PDF document
            $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        } catch (Exception $e) {
            error_log('Certificate Error creating TCPDF: ' . $e->getMessage());
            throw new Exception('Error al crear documento PDF: ' . $e->getMessage());
        }
        
        // Set document information
        $pdf->SetCreator('Sistema de Gestión');
        $pdf->SetAuthor($orgSettings['org_name'] ?? 'Asociación');
        $pdf->SetTitle($this->getCertificateTitle($type));
        
        // Remove default header/footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        
        // Set margins
        $pdf->SetMargins(20, 20, 20);
        $pdf->SetAutoPageBreak(true, 20);
        
        // Add a page
        $pdf->AddPage();
        
        // Set font
        $pdf->SetFont('helvetica', '', 11);
        
        // Generate content based on type
        $html = $this->generateCertificateHTML($type, $data, $orgSettings);
        
        // Write HTML content
        $pdf->writeHTML($html, true, false, true, false, '');
        
        // Return PDF as string
        return $pdf->Output('', 'S');
    }
    
    /**
     * Get certificate title
     */
    private function getCertificateTitle($type) {
        $titles = [
            'membership' => 'Certificado de Socio',
            'payment' => 'Certificado de Pagos',
            'donation' => 'Certificado de Donaciones'
        ];
        
        return $titles[$type] ?? 'Certificado';
    }
    
    /**
     * Generate HTML content for certificate
     */
    private function generateCertificateHTML($type, $data, $orgSettings) {
        ob_start();
        
        // Include the appropriate template
        $templateFile = __DIR__ . "/../Views/certificates/{$type}.php";
        if (file_exists($templateFile)) {
            include $templateFile;
        } else {
            echo $this->getDefaultTemplate($type, $data, $orgSettings);
        }
        
        return ob_get_clean();
    }
    
    /**
     * Get default template if custom template doesn't exist
     */
    private function getDefaultTemplate($type, $data, $orgSettings) {
        $orgName = htmlspecialchars($orgSettings['org_name'] ?? 'Asociación');
        $orgCif = htmlspecialchars($orgSettings['org_cif'] ?? '');
        $primaryColor = $orgSettings['primary_color'] ?? '#4f46e5';
        
        $html = '<style>
            .certificate-header {
                text-align: center;
                margin-bottom: 30px;
                border-bottom: 3px solid ' . $primaryColor . ';
                padding-bottom: 20px;
            }
            .certificate-logo {
                max-height: 80px;
                margin-bottom: 15px;
            }
            .certificate-title {
                font-size: 24px;
                font-weight: bold;
                color: ' . $primaryColor . ';
                margin: 15px 0;
            }
            .certificate-body {
                margin: 30px 0;
                line-height: 1.8;
            }
            .certificate-footer {
                margin-top: 50px;
                text-align: center;
                font-size: 10px;
                color: #666;
            }
            .signature-section {
                margin-top: 60px;
                text-align: center;
            }
            .signature-line {
                border-top: 1px solid #000;
                width: 200px;
                margin: 0 auto;
                padding-top: 5px;
            }
        </style>';
        
        $html .= '<div class="certificate-header">';
        
        // Logo
        if (!empty($orgSettings['org_logo'])) {
            $logoPath = __DIR__ . '/../../public' . $orgSettings['org_logo'];
            if (file_exists($logoPath)) {
                $html .= '<img src="' . $logoPath . '" class="certificate-logo">';
            }
        }
        
        $html .= '<div class="certificate-title">' . $this->getCertificateTitle($type) . '</div>';
        $html .= '<div>' . $orgName;
        if ($orgCif) {
            $html .= ' - CIF: ' . $orgCif;
        }
        $html .= '</div>';
        $html .= '</div>';
        
        $html .= '<div class="certificate-body">';
        
        switch ($type) {
            case 'membership':
                $html .= $this->getMembershipContent($data, $orgSettings);
                break;
            case 'payment':
                $html .= $this->getPaymentContent($data, $orgSettings);
                break;
            case 'donation':
                $html .= $this->getDonationContent($data, $orgSettings);
                break;
        }
        
        $html .= '</div>';
        
        // Signature section
        if (!empty($orgSettings['cert_signature_title'])) {
            $html .= '<div class="signature-section">';
            $html .= '<div class="signature-line">';
            $html .= htmlspecialchars($orgSettings['cert_signature_title']);
            $html .= '</div>';
            if (!empty($orgSettings['cert_signature_name'])) {
                $html .= '<div>' . htmlspecialchars($orgSettings['cert_signature_name']) . '</div>';
            }
            $html .= '</div>';
        }
        
        // Footer
        $html .= '<div class="certificate-footer">';
        if (!empty($orgSettings['cert_footer_text'])) {
            $html .= '<p>' . nl2br(htmlspecialchars($orgSettings['cert_footer_text'])) . '</p>';
        }
        $html .= '<p>Documento generado electrónicamente el ' . date('d/m/Y H:i') . '</p>';
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Generate membership certificate content
     */
    private function getMembershipContent($member, $orgSettings) {
        $orgName = htmlspecialchars($orgSettings['org_name'] ?? 'la Asociación');
        $name = htmlspecialchars($member['first_name'] . ' ' . $member['last_name']);
        $dni = htmlspecialchars($member['dni'] ?? '');
        $memberNumber = htmlspecialchars($member['member_number'] ?? '');
        $joinDate = date('d/m/Y', strtotime($member['join_date']));
        $category = htmlspecialchars($member['category_name'] ?? 'General');
        
        $html = '<p style="text-align: justify;">';
        $html .= '<strong>' . $orgName . '</strong> certifica que:';
        $html .= '</p>';
        
        $html .= '<p style="text-align: center; margin: 30px 0; font-size: 14px;">';
        $html .= '<strong>' . $name . '</strong>';
        if ($dni) {
            $html .= '<br>DNI: ' . $dni;
        }
        $html .= '</p>';
        
        $html .= '<p style="text-align: justify;">';
        $html .= 'Es socio/a de esta asociación con número de socio <strong>' . $memberNumber . '</strong>, ';
        $html .= 'perteneciente a la categoría <strong>' . $category . '</strong>, ';
        $html .= 'desde el <strong>' . $joinDate . '</strong>, ';
        $html .= 'encontrándose al corriente de sus obligaciones como socio/a.';
        $html .= '</p>';
        
        $html .= '<p style="text-align: justify; margin-top: 20px;">';
        $html .= 'Se expide el presente certificado a petición del interesado/a para los fines que estime oportunos.';
        $html .= '</p>';
        
        $html .= '<p style="text-align: right; margin-top: 30px;">';
        $html .= date('d') . ' de ' . $this->getSpanishMonth(date('m')) . ' de ' . date('Y');
        $html .= '</p>';
        
        return $html;
    }
    
    /**
     * Generate payment certificate content
     */
    private function getPaymentContent($member, $orgSettings) {
        $orgName = htmlspecialchars($orgSettings['org_name'] ?? 'la Asociación');
        $name = htmlspecialchars($member['first_name'] . ' ' . $member['last_name']);
        $dni = htmlspecialchars($member['dni'] ?? '');
        $year = $member['year'];
        
        $html = '<p style="text-align: justify;">';
        $html .= '<strong>' . $orgName . '</strong> certifica que:';
        $html .= '</p>';
        
        $html .= '<p style="text-align: center; margin: 30px 0; font-size: 14px;">';
        $html .= '<strong>' . $name . '</strong>';
        if ($dni) {
            $html .= '<br>DNI: ' . $dni;
        }
        $html .= '</p>';
        
        if (count($member['payments']) > 0) {
            $total = 0;
            
            $html .= '<p style="text-align: justify;">';
            $html .= 'Ha realizado los siguientes pagos de cuotas durante el año <strong>' . $year . '</strong>:';
            $html .= '</p>';
            
            $html .= '<table style="width: 100%; margin: 20px 0; border-collapse: collapse;">';
            $html .= '<thead>';
            $html .= '<tr style="background-color: #f3f4f6;">';
            $html .= '<th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Fecha</th>';
            $html .= '<th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Concepto</th>';
            $html .= '<th style="border: 1px solid #ddd; padding: 8px; text-align: right;">Importe</th>';
            $html .= '</tr>';
            $html .= '</thead>';
            $html .= '<tbody>';
            
            foreach ($member['payments'] as $payment) {
                $total += $payment['amount'];
                $html .= '<tr>';
                $html .= '<td style="border: 1px solid #ddd; padding: 8px;">' . date('d/m/Y', strtotime($payment['payment_date'])) . '</td>';
                $html .= '<td style="border: 1px solid #ddd; padding: 8px;">' . htmlspecialchars($payment['concept'] ?? 'Cuota de socio') . '</td>';
                $html .= '<td style="border: 1px solid #ddd; padding: 8px; text-align: right;">' . number_format($payment['amount'], 2, ',', '.') . ' €</td>';
                $html .= '</tr>';
            }
            
            $html .= '<tr style="font-weight: bold; background-color: #f9fafb;">';
            $html .= '<td colspan="2" style="border: 1px solid #ddd; padding: 8px; text-align: right;">TOTAL:</td>';
            $html .= '<td style="border: 1px solid #ddd; padding: 8px; text-align: right;">' . number_format($total, 2, ',', '.') . ' €</td>';
            $html .= '</tr>';
            
            $html .= '</tbody>';
            $html .= '</table>';
            
            $html .= '<p style="text-align: justify;">';
            $html .= 'El importe total de las cuotas abonadas durante el año ' . $year . ' asciende a ';
            $html .= '<strong>' . number_format($total, 2, ',', '.') . ' euros</strong>.';
            $html .= '</p>';
        } else {
            $html .= '<p style="text-align: justify;">';
            $html .= 'No consta ningún pago de cuotas registrado durante el año <strong>' . $year . '</strong>.';
            $html .= '</p>';
        }
        
        $html .= '<p style="text-align: justify; margin-top: 20px;">';
        $html .= 'Se expide el presente certificado a petición del interesado/a para los fines que estime oportunos.';
        $html .= '</p>';
        
        $html .= '<p style="text-align: right; margin-top: 30px;">';
        $html .= date('d') . ' de ' . $this->getSpanishMonth(date('m')) . ' de ' . date('Y');
        $html .= '</p>';
        
        return $html;
    }
    
    /**
     * Generate donation certificate content
     */
    private function getDonationContent($donor, $orgSettings) {
        $orgName = htmlspecialchars($orgSettings['org_name'] ?? 'la Asociación');
        $orgCif = htmlspecialchars($orgSettings['org_cif'] ?? '');
        $name = htmlspecialchars($donor['first_name'] . ' ' . $donor['last_name']);
        $dni = htmlspecialchars($donor['dni'] ?? '');
        $year = $donor['year'];
        
        $html = '<p style="text-align: justify;">';
        $html .= '<strong>' . $orgName . '</strong>';
        if ($orgCif) {
            $html .= ' (CIF: ' . $orgCif . ')';
        }
        $html .= ' certifica que:';
        $html .= '</p>';
        
        $html .= '<p style="text-align: center; margin: 30px 0; font-size: 14px;">';
        $html .= '<strong>' . $name . '</strong>';
        if ($dni) {
            $html .= '<br>DNI/NIF: ' . $dni;
        }
        $html .= '</p>';
        
        if (count($donor['donations']) > 0) {
            $total = 0;
            
            $html .= '<p style="text-align: justify;">';
            $html .= 'Ha realizado las siguientes donaciones durante el año <strong>' . $year . '</strong>:';
            $html .= '</p>';
            
            $html .= '<table style="width: 100%; margin: 20px 0; border-collapse: collapse;">';
            $html .= '<thead>';
            $html .= '<tr style="background-color: #f3f4f6;">';
            $html .= '<th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Fecha</th>';
            $html .= '<th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Concepto</th>';
            $html .= '<th style="border: 1px solid #ddd; padding: 8px; text-align: right;">Importe</th>';
            $html .= '</tr>';
            $html .= '</thead>';
            $html .= '<tbody>';
            
            foreach ($donor['donations'] as $donation) {
                $total += $donation['amount'];
                $html .= '<tr>';
                $html .= '<td style="border: 1px solid #ddd; padding: 8px;">' . date('d/m/Y', strtotime($donation['donation_date'])) . '</td>';
                $html .= '<td style="border: 1px solid #ddd; padding: 8px;">' . htmlspecialchars($donation['description'] ?? 'Donación') . '</td>';
                $html .= '<td style="border: 1px solid #ddd; padding: 8px; text-align: right;">' . number_format($donation['amount'], 2, ',', '.') . ' €</td>';
                $html .= '</tr>';
            }
            
            $html .= '<tr style="font-weight: bold; background-color: #f9fafb;">';
            $html .= '<td colspan="2" style="border: 1px solid #ddd; padding: 8px; text-align: right;">TOTAL:</td>';
            $html .= '<td style="border: 1px solid #ddd; padding: 8px; text-align: right;">' . number_format($total, 2, ',', '.') . ' €</td>';
            $html .= '</tr>';
            
            $html .= '</tbody>';
            $html .= '</table>';
            
            $html .= '<p style="text-align: justify;">';
            $html .= 'El importe total de las donaciones realizadas durante el año ' . $year . ' asciende a ';
            $html .= '<strong>' . number_format($total, 2, ',', '.') . ' euros</strong>.';
            $html .= '</p>';
            
            $html .= '<p style="text-align: justify; margin-top: 20px;">';
            $html .= '<em>Este certificado puede ser utilizado para la deducción fiscal de donaciones según lo establecido ';
            $html .= 'en la Ley 49/2002, de 23 de diciembre, de régimen fiscal de las entidades sin fines lucrativos y de los incentivos fiscales al mecenazgo.</em>';
            $html .= '</p>';
        } else {
            $html .= '<p style="text-align: justify;">';
            $html .= 'No consta ninguna donación registrada durante el año <strong>' . $year . '</strong>.';
            $html .= '</p>';
        }
        
        $html .= '<p style="text-align: justify; margin-top: 20px;">';
        $html .= 'Se expide el presente certificado a petición del interesado/a para los fines que estime oportunos.';
        $html .= '</p>';
        
        $html .= '<p style="text-align: right; margin-top: 30px;">';
        $html .= date('d') . ' de ' . $this->getSpanishMonth(date('m')) . ' de ' . date('Y');
        $html .= '</p>';
        
        return $html;
    }
    
    /**
     * Get Spanish month name
     */
    private function getSpanishMonth($month) {
        $months = [
            '01' => 'enero', '02' => 'febrero', '03' => 'marzo',
            '04' => 'abril', '05' => 'mayo', '06' => 'junio',
            '07' => 'julio', '08' => 'agosto', '09' => 'septiembre',
            '10' => 'octubre', '11' => 'noviembre', '12' => 'diciembre'
        ];
        
        return $months[$month] ?? '';
    }
}
