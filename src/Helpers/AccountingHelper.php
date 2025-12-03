<?php

/**
 * AccountingHelper - Helper para generación automática de asientos contables
 * Centraliza la lógica de creación de asientos desde diferentes módulos
 */

require_once __DIR__ . '/../Models/AccountingEntry.php';
require_once __DIR__ . '/../Models/AccountingPeriod.php';
require_once __DIR__ . '/../Models/AccountingAccount.php';

class AccountingHelper {
    
    /**
     * Crea un asiento contable automático desde un gasto
     * 
     * @param PDO $db Conexión a la base de datos
     * @param int $expenseId ID del gasto
     * @param float $amount Monto del gasto
     * @param string $description Descripción
     * @param string $date Fecha del gasto
     * @param string $paymentMethod Método de pago (cash, transfer, card)
     * @param int $categoryId ID de la categoría (opcional)
     * @return bool True si se creó correctamente
     */
    public static function createEntryFromExpense($db, $expenseId, $amount, $description, $date, $paymentMethod = 'transfer', $categoryId = null) {
        try {
            // Obtener periodo activo para la fecha
            $period = AccountingPeriod::getOpenPeriodForDate($db, $date);
            if (!$period) {
                error_log("No hay periodo contable abierto para la fecha: $date");
                return false;
            }
            
            // Determinar cuenta de gasto (600-699 son gastos en PGC español)
            // Por defecto: 629 - Otros servicios
            $expenseAccountCode = '629';
            
            // Si hay categoría, intentar mapear a cuenta específica
            if ($categoryId) {
                $expenseAccountCode = self::mapExpenseCategoryToAccount($db, $categoryId);
            }
            
            // Determinar cuenta de tesorería según método de pago
            $treasuryAccountCode = self::getTreasuryAccountByPaymentMethod($paymentMethod);
            
            // Buscar las cuentas
            $expenseAccount = self::getAccountByCode($db, $expenseAccountCode);
            $treasuryAccount = self::getAccountByCode($db, $treasuryAccountCode);
            
            if (!$expenseAccount || !$treasuryAccount) {
                error_log("No se encontraron las cuentas contables necesarias");
                return false;
            }
            
            // Crear el asiento
            $entry = new AccountingEntry($db);
            $entry->entry_date = $date;
            $entry->period_id = $period['id'];
            $entry->description = "Gasto: " . $description;
            $entry->reference = "EXP-" . $expenseId;
            $entry->entry_type = 'automatic';
            $entry->source_type = 'expense';
            $entry->source_id = $expenseId;
            $entry->status = 'posted'; // Auto-contabilizado
            $entry->created_by = $_SESSION['user_id'] ?? 1;
            $entry->posted_by = $_SESSION['user_id'] ?? 1;
            $entry->posted_at = date('Y-m-d H:i:s');
            
            // Líneas del asiento (doble partida)
            $lines = [
                [
                    'account_id' => $expenseAccount['id'],
                    'description' => $description,
                    'debit' => $amount,
                    'credit' => 0,
                    'line_order' => 1
                ],
                [
                    'account_id' => $treasuryAccount['id'],
                    'description' => $description,
                    'debit' => 0,
                    'credit' => $amount,
                    'line_order' => 2
                ]
            ];
            
            return $entry->create($lines);
            
        } catch (Exception $e) {
            error_log("Error al crear asiento desde gasto: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Crea un asiento contable automático desde un pago de cuota/evento
     * 
     * @param PDO $db Conexión a la base de datos
     * @param int $paymentId ID del pago
     * @param float $amount Monto del pago
     * @param string $description Descripción
     * @param string $date Fecha del pago
     * @param string $paymentMethod Método de pago
     * @param string $type Tipo: 'fee' o 'event'
     * @return bool True si se creó correctamente
     */
    public static function createEntryFromPayment($db, $paymentId, $amount, $description, $date, $paymentMethod = 'transfer', $type = 'fee') {
        try {
            $period = AccountingPeriod::getOpenPeriodForDate($db, $date);
            if (!$period) {
                error_log("No hay periodo contable abierto para la fecha: $date");
                return false;
            }
            
            // Cuenta de ingreso: 705 - Prestaciones de servicios (cuotas)
            $incomeAccountCode = $type === 'event' ? '705' : '705';
            
            // Cuenta de tesorería
            $treasuryAccountCode = self::getTreasuryAccountByPaymentMethod($paymentMethod);
            
            $incomeAccount = self::getAccountByCode($db, $incomeAccountCode);
            $treasuryAccount = self::getAccountByCode($db, $treasuryAccountCode);
            
            if (!$incomeAccount || !$treasuryAccount) {
                error_log("No se encontraron las cuentas contables necesarias");
                return false;
            }
            
            $entry = new AccountingEntry($db);
            $entry->entry_date = $date;
            $entry->period_id = $period['id'];
            $entry->description = "Ingreso: " . $description;
            $entry->reference = "PAY-" . $paymentId;
            $entry->entry_type = 'automatic';
            $entry->source_type = 'payment';
            $entry->source_id = $paymentId;
            $entry->status = 'posted';
            $entry->created_by = $_SESSION['user_id'] ?? 1;
            $entry->posted_by = $_SESSION['user_id'] ?? 1;
            $entry->posted_at = date('Y-m-d H:i:s');
            
            $lines = [
                [
                    'account_id' => $treasuryAccount['id'],
                    'description' => $description,
                    'debit' => $amount,
                    'credit' => 0,
                    'line_order' => 1
                ],
                [
                    'account_id' => $incomeAccount['id'],
                    'description' => $description,
                    'debit' => 0,
                    'credit' => $amount,
                    'line_order' => 2
                ]
            ];
            
            return $entry->create($lines);
            
        } catch (Exception $e) {
            error_log("Error al crear asiento desde pago: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Crea un asiento contable automático desde una donación
     * 
     * @param PDO $db Conexión a la base de datos
     * @param int $donationId ID de la donación
     * @param float $amount Monto de la donación
     * @param string $description Descripción
     * @param string $date Fecha de la donación
     * @param string $paymentMethod Método de pago
     * @return bool True si se creó correctamente
     */
    public static function createEntryFromDonation($db, $donationId, $amount, $description, $date, $paymentMethod = 'transfer') {
        try {
            $period = AccountingPeriod::getOpenPeriodForDate($db, $date);
            if (!$period) {
                error_log("No hay periodo contable abierto para la fecha: $date");
                return false;
            }
            
            // Cuenta de ingreso: 740 - Subvenciones, donaciones y legados
            $incomeAccountCode = '740';
            $treasuryAccountCode = self::getTreasuryAccountByPaymentMethod($paymentMethod);
            
            $incomeAccount = self::getAccountByCode($db, $incomeAccountCode);
            $treasuryAccount = self::getAccountByCode($db, $treasuryAccountCode);
            
            if (!$incomeAccount || !$treasuryAccount) {
                error_log("No se encontraron las cuentas contables necesarias");
                return false;
            }
            
            $entry = new AccountingEntry($db);
            $entry->entry_date = $date;
            $entry->period_id = $period['id'];
            $entry->description = "Donación: " . $description;
            $entry->reference = "DON-" . $donationId;
            $entry->entry_type = 'automatic';
            $entry->source_type = 'donation';
            $entry->source_id = $donationId;
            $entry->status = 'posted';
            $entry->created_by = $_SESSION['user_id'] ?? 1;
            $entry->posted_by = $_SESSION['user_id'] ?? 1;
            $entry->posted_at = date('Y-m-d H:i:s');
            
            $lines = [
                [
                    'account_id' => $treasuryAccount['id'],
                    'description' => $description,
                    'debit' => $amount,
                    'credit' => 0,
                    'line_order' => 1
                ],
                [
                    'account_id' => $incomeAccount['id'],
                    'description' => $description,
                    'debit' => 0,
                    'credit' => $amount,
                    'line_order' => 2
                ]
            ];
            
            return $entry->create($lines);
            
        } catch (Exception $e) {
            error_log("Error al crear asiento desde donación: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtiene el código de cuenta de tesorería según el método de pago
     */
    private static function getTreasuryAccountByPaymentMethod($paymentMethod) {
        switch ($paymentMethod) {
            case 'cash':
                return '570'; // Caja, euros
            case 'transfer':
            case 'bank':
                return '572'; // Bancos c/c
            case 'card':
                return '572'; // Bancos c/c (por defecto)
            default:
                return '572';
        }
    }
    
    /**
     * Mapea una categoría de gasto a una cuenta contable
     */
    private static function mapExpenseCategoryToAccount($db, $categoryId) {
        // Mapeo básico de categorías a cuentas PGC
        // Esto debería ser configurable, pero de momento usamos valores por defecto
        
        // Obtener nombre de la categoría para mapeo inteligente
        $query = "SELECT name FROM expense_categories WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $categoryId);
        $stmt->execute();
        $category = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($category) {
            $name = strtolower($category['name']);
            
            // Mapeo por palabras clave
            if (strpos($name, 'alquiler') !== false || strpos($name, 'rent') !== false) {
                return '621'; // Arrendamientos y cánones
            } elseif (strpos($name, 'suministro') !== false || strpos($name, 'agua') !== false || 
                      strpos($name, 'luz') !== false || strpos($name, 'electricidad') !== false) {
                return '628'; // Suministros
            } elseif (strpos($name, 'publicidad') !== false || strpos($name, 'marketing') !== false) {
                return '627'; // Publicidad, propaganda y relaciones públicas
            } elseif (strpos($name, 'transporte') !== false || strpos($name, 'viaje') !== false) {
                return '624'; // Transportes
            } elseif (strpos($name, 'seguro') !== false) {
                return '625'; // Primas de seguros
            } elseif (strpos($name, 'material') !== false || strpos($name, 'suministro') !== false) {
                return '602'; // Compras de otros aprovisionamientos
            }
        }
        
        // Por defecto: Otros servicios
        return '629';
    }
    
    /**
     * Obtiene una cuenta contable por su código
     */
    private static function getAccountByCode($db, $code) {
        $query = "SELECT * FROM accounting_accounts WHERE code = :code AND is_active = 1 LIMIT 1";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':code', $code);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
