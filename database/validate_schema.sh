#!/bin/bash
# Script para validar sintaxis SQL sin necesidad de MySQL instalado
# Este script busca errores comunes de sintaxis

echo "============================================"
echo "Validación de sintaxis de schema.sql"
echo "============================================"
echo ""

SCHEMA_FILE="database/schema.sql"
ERRORS=0

echo "Verificando archivo: $SCHEMA_FILE"
echo ""

# Verificar que el archivo existe
if [ ! -f "$SCHEMA_FILE" ]; then
    echo "ERROR: No se encuentra el archivo $SCHEMA_FILE"
    exit 1
fi

echo "✓ Archivo encontrado"
echo ""

# Verificar paréntesis balanceados
echo "Verificando paréntesis balanceados..."
OPEN_PAREN=$(grep -o "(" "$SCHEMA_FILE" | wc -l)
CLOSE_PAREN=$(grep -o ")" "$SCHEMA_FILE" | wc -l)

if [ "$OPEN_PAREN" -ne "$CLOSE_PAREN" ]; then
    echo "✗ ERROR: Paréntesis desbalanceados (abiertos: $OPEN_PAREN, cerrados: $CLOSE_PAREN)"
    ERRORS=$((ERRORS + 1))
else
    echo "✓ Paréntesis balanceados ($OPEN_PAREN pares)"
fi

# Verificar que todas las sentencias CREATE TABLE terminan correctamente
echo ""
echo "Verificando sentencias CREATE TABLE..."
CREATE_COUNT=$(grep -c "CREATE TABLE" "$SCHEMA_FILE")
ENGINE_COUNT=$(grep -c "ENGINE=InnoDB" "$SCHEMA_FILE")

echo "  - Tablas CREATE TABLE encontradas: $CREATE_COUNT"
echo "  - Declaraciones ENGINE encontradas: $ENGINE_COUNT"

if [ "$CREATE_COUNT" -ne "$ENGINE_COUNT" ]; then
    echo "✗ ADVERTENCIA: Posible tabla sin ENGINE declarado"
fi

# Verificar que no hay líneas duplicadas sospechosas
echo ""
echo "Verificando duplicados..."

# Buscar CREATE TABLE duplicados
DUPLICATES=$(grep "CREATE TABLE IF NOT EXISTS" "$SCHEMA_FILE" | sort | uniq -d)
if [ -n "$DUPLICATES" ]; then
    echo "✗ ERROR: Tablas duplicadas encontradas:"
    echo "$DUPLICATES"
    ERRORS=$((ERRORS + 1))
else
    echo "✓ No se encontraron tablas duplicadas"
fi

# Verificar que no hay comas finales antes de paréntesis de cierre
echo ""
echo "Verificando comas finales incorrectas..."
TRAILING_COMMAS=$(grep -n ",[ ]*$" "$SCHEMA_FILE" | grep -v "COMMENT" | head -5)
if [ -n "$TRAILING_COMMAS" ]; then
    echo "✗ ADVERTENCIA: Posibles comas finales (revisar manualmente):"
    echo "$TRAILING_COMMAS"
fi

# Verificar FOREIGN KEY sin tabla referenciada
echo ""
echo "Verificando FOREIGN KEYs..."
FK_COUNT=$(grep -c "FOREIGN KEY" "$SCHEMA_FILE")
REFERENCES_COUNT=$(grep -c "REFERENCES" "$SCHEMA_FILE")

echo "  - FOREIGN KEY declaradas: $FK_COUNT"
echo "  - REFERENCES encontradas: $REFERENCES_COUNT"

if [ "$FK_COUNT" -ne "$REFERENCES_COUNT" ]; then
    echo "✗ ERROR: Número de FOREIGN KEY no coincide con REFERENCES"
    ERRORS=$((ERRORS + 1))
else
    echo "✓ FOREIGN KEYs correctamente declaradas"
fi

# Resumen
echo ""
echo "============================================"
if [ $ERRORS -eq 0 ]; then
    echo "✓ VALIDACIÓN EXITOSA"
    echo "El archivo parece sintácticamente correcto"
else
    echo "✗ SE ENCONTRARON $ERRORS ERRORES"
    echo "Revisa los mensajes anteriores"
fi
echo "============================================"

exit $ERRORS
