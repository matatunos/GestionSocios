<?php

/**
 * ExportHelper - Helper para exportación de reportes contables a Excel
 * Usa PhpSpreadsheet para generar archivos .xlsx
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Font;

class ExportHelper {
    
    /**
     * Exporta el Balance de Sumas y Saldos (Trial Balance) a Excel
     */
    public static function exportTrialBalance($data, $startDate, $endDate) {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Título
        $sheet->setCellValue('A1', 'BALANCE DE SUMAS Y SALDOS');
        $sheet->mergeCells('A1:H1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        // Periodo
        $sheet->setCellValue('A2', 'Periodo: ' . date('d/m/Y', strtotime($startDate)) . ' - ' . date('d/m/Y', strtotime($endDate)));
        $sheet->mergeCells('A2:H2');
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        // Encabezados
        $headers = ['Código', 'Cuenta', 'Debe (Sumas)', 'Haber (Sumas)', 'Debe (Saldos)', 'Haber (Saldos)', 'Saldo Deudor', 'Saldo Acreedor'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '4', $header);
            $col++;
        }
        
        // Estilo de encabezados
        $sheet->getStyle('A4:H4')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4472C4']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ]);
        
        // Datos
        $row = 5;
        $totalDebit = 0;
        $totalCredit = 0;
        $totalDebitBalance = 0;
        $totalCreditBalance = 0;
        
        foreach ($data as $account) {
            $sheet->setCellValue('A' . $row, $account['code']);
            $sheet->setCellValue('B' . $row, $account['name']);
            $sheet->setCellValue('C' . $row, $account['total_debit']);
            $sheet->setCellValue('D' . $row, $account['total_credit']);
            
            $balance = $account['balance'];
            if ($account['balance_type'] === 'debit') {
                $sheet->setCellValue('E' . $row, max(0, $balance));
                $sheet->setCellValue('F' . $row, max(0, -$balance));
                $sheet->setCellValue('G' . $row, max(0, $balance));
                $sheet->setCellValue('H' . $row, 0);
            } else {
                $sheet->setCellValue('E' . $row, max(0, -$balance));
                $sheet->setCellValue('F' . $row, max(0, $balance));
                $sheet->setCellValue('G' . $row, 0);
                $sheet->setCellValue('H' . $row, max(0, $balance));
            }
            
            $totalDebit += $account['total_debit'];
            $totalCredit += $account['total_credit'];
            $totalDebitBalance += max(0, $balance > 0 ? $balance : 0);
            $totalCreditBalance += max(0, $balance < 0 ? -$balance : 0);
            
            $row++;
        }
        
        // Totales
        $sheet->setCellValue('A' . $row, 'TOTALES');
        $sheet->setCellValue('B' . $row, '');
        $sheet->setCellValue('C' . $row, $totalDebit);
        $sheet->setCellValue('D' . $row, $totalCredit);
        $sheet->setCellValue('G' . $row, $totalDebitBalance);
        $sheet->setCellValue('H' . $row, $totalCreditBalance);
        
        $sheet->getStyle('A' . $row . ':H' . $row)->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D9E1F2']],
            'borders' => ['top' => ['borderStyle' => Border::BORDER_DOUBLE]]
        ]);
        
        // Formato de números
        $sheet->getStyle('C5:H' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
        
        // Ajustar anchos
        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        return self::downloadSpreadsheet($spreadsheet, 'Balance_Sumas_Saldos_' . date('Y-m-d') . '.xlsx');
    }
    
    /**
     * Exporta el Libro Mayor (General Ledger) a Excel
     */
    public static function exportGeneralLedger($account, $ledgerData, $startDate, $endDate) {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Título
        $sheet->setCellValue('A1', 'LIBRO MAYOR');
        $sheet->mergeCells('A1:F1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        // Cuenta
        $sheet->setCellValue('A2', 'Cuenta: ' . $account['code'] . ' - ' . $account['name']);
        $sheet->mergeCells('A2:F2');
        
        // Periodo
        $sheet->setCellValue('A3', 'Periodo: ' . date('d/m/Y', strtotime($startDate)) . ' - ' . date('d/m/Y', strtotime($endDate)));
        $sheet->mergeCells('A3:F3');
        
        // Encabezados
        $headers = ['Fecha', 'Nº Asiento', 'Descripción', 'Debe', 'Haber', 'Saldo'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '5', $header);
            $col++;
        }
        
        $sheet->getStyle('A5:F5')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4472C4']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ]);
        
        // Datos
        $row = 6;
        $balance = 0;
        
        foreach ($ledgerData as $transaction) {
            $sheet->setCellValue('A' . $row, date('d/m/Y', strtotime($transaction['entry_date'])));
            $sheet->setCellValue('B' . $row, $transaction['entry_number']);
            $sheet->setCellValue('C' . $row, $transaction['description'] . ' - ' . $transaction['line_description']);
            $sheet->setCellValue('D' . $row, $transaction['debit']);
            $sheet->setCellValue('E' . $row, $transaction['credit']);
            
            if ($account['balance_type'] === 'debit') {
                $balance += $transaction['debit'] - $transaction['credit'];
            } else {
                $balance += $transaction['credit'] - $transaction['debit'];
            }
            
            $sheet->setCellValue('F' . $row, $balance);
            $row++;
        }
        
        // Formato
        $sheet->getStyle('D6:F' . ($row - 1))->getNumberFormat()->setFormatCode('#,##0.00');
        
        foreach (range('A', 'F') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        return self::downloadSpreadsheet($spreadsheet, 'Libro_Mayor_' . $account['code'] . '_' . date('Y-m-d') . '.xlsx');
    }
    
    /**
     * Exporta el Balance de Situación a Excel
     */
    public static function exportBalanceSheet($assets, $liabilities, $equity, $endDate) {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Título
        $sheet->setCellValue('A1', 'BALANCE DE SITUACIÓN');
        $sheet->mergeCells('A1:E1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        // Fecha
        $sheet->setCellValue('A2', 'A fecha: ' . date('d/m/Y', strtotime($endDate)));
        $sheet->mergeCells('A2:E2');
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        $row = 4;
        
        // ACTIVO
        $sheet->setCellValue('A' . $row, 'ACTIVO');
        $sheet->mergeCells('A' . $row . ':B' . $row);
        $sheet->getStyle('A' . $row . ':B' . $row)->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '007BFF']]
        ]);
        $row++;
        
        $sheet->setCellValue('A' . $row, 'Código');
        $sheet->setCellValue('B' . $row, 'Cuenta');
        $sheet->setCellValue('C' . $row, 'Importe €');
        $sheet->getStyle('A' . $row . ':C' . $row)->getFont()->setBold(true);
        $row++;
        
        $totalAssets = 0;
        foreach ($assets as $account) {
            $sheet->setCellValue('A' . $row, $account['code']);
            $sheet->setCellValue('B' . $row, $account['name']);
            $sheet->setCellValue('C' . $row, $account['balance']);
            $totalAssets += $account['balance'];
            $row++;
        }
        
        $sheet->setCellValue('B' . $row, 'TOTAL ACTIVO');
        $sheet->setCellValue('C' . $row, $totalAssets);
        $sheet->getStyle('B' . $row . ':C' . $row)->getFont()->setBold(true);
        $row += 2;
        
        // PASIVO Y PATRIMONIO
        $sheet->setCellValue('D' . $row, 'PASIVO Y PATRIMONIO NETO');
        $sheet->mergeCells('D' . $row . ':E' . $row);
        $sheet->getStyle('D' . $row . ':E' . $row)->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'DC3545']]
        ]);
        
        $startRow = $row;
        $row++;
        
        // Patrimonio
        $sheet->setCellValue('D' . $row, 'Patrimonio Neto');
        $sheet->getStyle('D' . $row)->getFont()->setBold(true);
        $row++;
        
        $totalEquity = 0;
        foreach ($equity as $account) {
            $sheet->setCellValue('D' . $row, $account['code'] . ' - ' . $account['name']);
            $sheet->setCellValue('E' . $row, $account['balance']);
            $totalEquity += $account['balance'];
            $row++;
        }
        
        // Pasivo
        $sheet->setCellValue('D' . $row, 'Pasivo');
        $sheet->getStyle('D' . $row)->getFont()->setBold(true);
        $row++;
        
        $totalLiabilities = 0;
        foreach ($liabilities as $account) {
            $sheet->setCellValue('D' . $row, $account['code'] . ' - ' . $account['name']);
            $sheet->setCellValue('E' . $row, $account['balance']);
            $totalLiabilities += $account['balance'];
            $row++;
        }
        
        $sheet->setCellValue('D' . $row, 'TOTAL PASIVO Y PATRIMONIO');
        $sheet->setCellValue('E' . $row, $totalLiabilities + $totalEquity);
        $sheet->getStyle('D' . $row . ':E' . $row)->getFont()->setBold(true);
        
        // Formato
        $sheet->getStyle('C6:C' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle('E' . ($startRow + 1) . ':E' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
        
        foreach (range('A', 'E') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        return self::downloadSpreadsheet($spreadsheet, 'Balance_Situacion_' . date('Y-m-d') . '.xlsx');
    }
    
    /**
     * Exporta la Cuenta de Resultados a Excel
     */
    public static function exportIncomeStatement($incomeAccounts, $expenseAccounts, $startDate, $endDate) {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Título
        $sheet->setCellValue('A1', 'CUENTA DE RESULTADOS');
        $sheet->mergeCells('A1:C1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        // Periodo
        $sheet->setCellValue('A2', 'Periodo: ' . date('d/m/Y', strtotime($startDate)) . ' - ' . date('d/m/Y', strtotime($endDate)));
        $sheet->mergeCells('A2:C2');
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        $row = 4;
        
        // INGRESOS
        $sheet->setCellValue('A' . $row, 'INGRESOS');
        $sheet->mergeCells('A' . $row . ':C' . $row);
        $sheet->getStyle('A' . $row . ':C' . $row)->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '28A745']]
        ]);
        $row++;
        
        $totalIncome = 0;
        foreach ($incomeAccounts as $account) {
            $sheet->setCellValue('A' . $row, $account['code']);
            $sheet->setCellValue('B' . $row, $account['name']);
            $sheet->setCellValue('C' . $row, $account['balance']);
            $totalIncome += $account['balance'];
            $row++;
        }
        
        $sheet->setCellValue('B' . $row, 'TOTAL INGRESOS');
        $sheet->setCellValue('C' . $row, $totalIncome);
        $sheet->getStyle('B' . $row . ':C' . $row)->getFont()->setBold(true);
        $row += 2;
        
        // GASTOS
        $sheet->setCellValue('A' . $row, 'GASTOS');
        $sheet->mergeCells('A' . $row . ':C' . $row);
        $sheet->getStyle('A' . $row . ':C' . $row)->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'DC3545']]
        ]);
        $row++;
        
        $totalExpenses = 0;
        foreach ($expenseAccounts as $account) {
            $sheet->setCellValue('A' . $row, $account['code']);
            $sheet->setCellValue('B' . $row, $account['name']);
            $sheet->setCellValue('C' . $row, $account['balance']);
            $totalExpenses += $account['balance'];
            $row++;
        }
        
        $sheet->setCellValue('B' . $row, 'TOTAL GASTOS');
        $sheet->setCellValue('C' . $row, $totalExpenses);
        $sheet->getStyle('B' . $row . ':C' . $row)->getFont()->setBold(true);
        $row += 2;
        
        // RESULTADO
        $netProfit = $totalIncome - $totalExpenses;
        $sheet->setCellValue('B' . $row, $netProfit >= 0 ? 'BENEFICIO DEL EJERCICIO' : 'PÉRDIDA DEL EJERCICIO');
        $sheet->setCellValue('C' . $row, abs($netProfit));
        $sheet->getStyle('B' . $row . ':C' . $row)->applyFromArray([
            'font' => ['bold' => true, 'size' => 14],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $netProfit >= 0 ? '007BFF' : 'FFC107']]
        ]);
        
        // Formato
        $sheet->getStyle('C5:C' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
        
        foreach (range('A', 'C') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        return self::downloadSpreadsheet($spreadsheet, 'Cuenta_Resultados_' . date('Y-m-d') . '.xlsx');
    }
    
    /**
     * Descarga el archivo Excel
     */
    private static function downloadSpreadsheet($spreadsheet, $filename) {
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
}
