<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Lector QR de Vales de Evento</title>
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
</head>
<body>
    <h2>Escanea el QR del vale</h2>
    <div id="reader" style="width:300px;"></div>
    <div id="result"></div>
    <script>
        function showResult(status) {
            let msg = '';
            if (status === 'valid') msg = 'Vale válido. Recogido correctamente.';
            else if (status === 'used') msg = 'Este vale ya fue recogido.';
            else msg = 'Vale inválido.';
            document.getElementById('result').innerText = msg;
        }
        function onScanSuccess(decodedText, decodedResult) {
            fetch('src/Controllers/EventVoucherController.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'code=' + encodeURIComponent(decodedText)
            })
            .then(response => response.json())
            .then(data => showResult(data.status));
        }
        let html5QrcodeScanner = new Html5QrcodeScanner(
            "reader", { fps: 10, qrbox: 250 });
        html5QrcodeScanner.render(onScanSuccess);
    </script>
</body>
</html>
