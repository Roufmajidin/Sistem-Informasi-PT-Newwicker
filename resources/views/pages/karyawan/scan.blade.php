<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Kamera Selfie + Lokasi</title>
  <style>
    body {
      font-family: sans-serif;
      text-align: center;
      background: #f2f2f2;
    }
    .camera-wrapper {
      display: inline-block;
      position: relative;
      border: 4px solid #333;
      border-radius: 10px;
      background: #000;
      padding: 5px;
      margin-top: 20px;
    }
    video {
      width: 100%;
      border-radius: 10px;
    }
    canvas {
      display: none;
      margin-top: 10px;
      max-width: 100%;
    }
    .timestamp {
      color: white;
      font-weight: bold;
      background-color: rgba(0, 0, 0, 0.7);
      padding: 5px;
      position: absolute;
      bottom: 5px;
      left: 50%;
      transform: translateX(-50%);
      font-size: 16px;
      border-radius: 5px;
    }
    .capture-btn {
      margin-top: 20px;
      padding: 10px 20px;
      font-size: 16px;
      border: none;
      background-color: #4CAF50;
      color: white;
      border-radius: 5px;
      cursor: pointer;
    }
    .photo-preview {
      margin-top: 20px;
    }
    .info {
      margin-top: 10px;
      font-weight: bold;
    }
  </style>
</head>
<body>

  <h4>Arahkan wajah anda</h4>

  <div class="camera-wrapper">
    <video id="video" autoplay playsinline></video>
    <div class="timestamp" id="timestamp"></div>
  </div>

  <br>
  <button class="capture-btn" onclick="takeSnapshot()">Ambil Foto & Lokasi</button>

  <div class="photo-preview">
    <h3>Hasil Selfie</h3>
    <canvas id="canvas"></canvas>
    <div class="info" id="locationInfo"></div>
    <div class="info" id="imageBase64Info"></div>
  </div>
<script>
  const video = document.getElementById('video');
  const timestamp = document.getElementById('timestamp');
  const canvas = document.getElementById('canvas');
  const ctx = canvas.getContext('2d');
  const locationInfo = document.getElementById('locationInfo');
  const imageBase64Info = document.getElementById('imageBase64Info');

  // Tampilkan kamera depan
  navigator.mediaDevices.getUserMedia({
    video: { facingMode: "user" },
    audio: false
  })
  .then(stream => {
    video.srcObject = stream;
  })
  .catch(err => {
    alert("Gagal membuka kamera: " + err.message);
  });

  // Tampilkan jam real-time
  function updateClock() {
    timestamp.innerText = new Date().toLocaleTimeString();
  }
  setInterval(updateClock, 1000);
  updateClock();

  function takeSnapshot() {
    // Tangkap gambar dari video
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
    canvas.style.display = 'block';

    // Konversi ke blob (bukan base64 agar sesuai Laravel file upload)
    canvas.toBlob(function(blob) {
      if (!navigator.geolocation) {
        alert("Geolocation tidak didukung.");
        return;
      }

      navigator.geolocation.getCurrentPosition(function(position) {
        const lat = position.coords.latitude.toFixed(6);
        const lon = position.coords.longitude.toFixed(6);

        locationInfo.innerHTML = `Latitude: ${lat}<br>Longitude: ${lon}`;

        const token = localStorage.getItem('token');
        if (!token) {
          alert("Belum login atau token tidak ditemukan.");
          return;
        }

        const formData = new FormData();
        formData.append('latitude', lat);
        formData.append('longitude', lon);
        formData.append('foto', blob, 'selfie.jpg');

        fetch('http://127.0.0.1:8000/api/absen', {
          method: 'POST',
          headers: {
              'Authorization': 'Bearer 10|A9rQSXIhXAoESsnuJeoTnLovUhwfUcWqZEN8uXNG7da67c92'

          },
          body: formData
        })
        .then(async res => {
          const data = await res.json();
          if (res.ok) {
            alert("✅ Absen berhasil: " + data.message);
          } else {
            alert("❌ Gagal absen: " + (data.message || 'Terjadi kesalahan' ));
          }
        })
        .catch(err => {
          console.error(err);
          alert("❌ Gagal mengirim data absen.");
        });

      }, function(err) {
        alert("Gagal mendapatkan lokasi: " + err.message);
      });

    }, 'image/jpeg');
  }
</script>


</body>
</html>
