<?php
// Helper para generar imágenes QR
require_once __DIR__ . '/../../vendor/autoload.php';
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

class QrGenerator {
    public static function generate($text, $size = 300) {
        $qr = QrCode::create($text)
            ->setSize($size);
        $writer = new PngWriter();
        $result = $writer->write($qr);
        return $result->getString(); // Devuelve la imagen PNG en binario
    }
}
