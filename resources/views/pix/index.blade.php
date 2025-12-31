<!-- resources/views/pix/index.blade.php -->
<!DOCTYPE html>
<html>
<head>
    <title>Gerar PIX</title>
    <style>
        body { font-family: Arial; max-width: 500px; margin: 50px auto; }
        input, button { width: 100%; padding: 10px; margin: 10px 0; }
        .qr-code { max-width: 300px; margin: 20px auto; }
    </style>
</head>
<body>
    <h2>Gerar PIX</h2>
    
    <form id="pixForm">
        @csrf
        <input type="number" name="valor" step="0.01" placeholder="Valor (ex: 10.50)" required>
        <input type="text" name="descricao" placeholder="Descrição (opcional)">
        <button type="submit">Gerar PIX</button>
    </form>
    
    <div id="resultado"></div>
    
    <script>
        document.getElementById('pixForm').onsubmit = async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const response = await fetch('/gerar-pix', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                document.getElementById('resultado').innerHTML = `
                    <h3>✅ PIX Gerado!</h3>
                    <p><strong>TXID:</strong> ${data.pix.txid}</p>
                    <p><strong>Valor:</strong> R$ ${data.pix.valor}</p>
                    <div class="qr-code">
                        <img src="${data.pix.qr_code}" alt="QR Code PIX">
                    </div>
                    <p><strong>PIX Copia e Cola:</strong></p>
                    <textarea style="width:100%;height:100px">${data.pix.copia_cola}</textarea>
                `;
            } else {
                document.getElementById('resultado').innerHTML = `
                    <p style="color:red">❌ Erro: ${data.error || 'Erro desconhecido'}</p>
                `;
            }
        };
    </script>
</body>
</html>