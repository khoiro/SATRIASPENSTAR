<!DOCTYPE html>
<html lang="en">
<?= view('shared/head') ?>

<body>
    <div class="wrapper">
        <?= view('admin/navbar') ?>
        
        <div class="content-wrapper p-4">
            <div class="container">
                <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
                <style>
                    #map {
                        height: 400px;
                        width: 100%;
                        border-radius: 8px;
                        margin-bottom: 20px;
                    }
                </style>
                
                <div class="card shadow">
                    <div class="card-header bg-primary text-white d-flex align-items-center">
                        <i class="fas fa-map-marker-alt me-2"></i>
                        <h5 class="mb-0">&nbsp;Pengaturan Titik Absensi Utama</h5>
                    </div>
                    <div class="card-body">
                        
                        <?php if (session()->getFlashdata('success')): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <?= session()->getFlashdata('success') ?>
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        <?php endif; ?>
                        <?php if (session()->getFlashdata('error')): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <?= session()->getFlashdata('error') ?>
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        <?php endif; ?>

                        <div id="map"></div>

                        <form method="post" action="/admin/location">
                            <?= csrf_field() ?>
                            
                            <p class="text-info">💡 **Klik pada peta** untuk menentukan lokasi baru. Titik merah adalah lokasi yang tersimpan saat ini dan area radius yang diizinkan.</p>

                            <div class="row">
                                <div class="col-md-5 mb-3">
                                    <label for="latitude">Latitude</label>
                                    <input type="text" class="form-control" id="latitude" name="latitude" required value="<?= old('latitude', $item['lat']) ?>">
                                </div>
                                <div class="col-md-5 mb-3">
                                    <label for="longitude">Longitude</label>
                                    <input type="text" class="form-control" id="longitude" name="longitude" required value="<?= old('longitude', $item['lng']) ?>">
                                </div>
                                <div class="col-md-2 mb-3">
                                    <label for="radius">Radius (Meter)</label>
                                    <input type="number" class="form-control" id="radius" name="radius" required value="<?= old('radius', $item['radius']) ?>" min="10" max="1000">
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Simpan Koordinat Baru</button>
                        </form>
                    </div>
                </div>
                </div>
        </div>
    </div>

    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Dapatkan elemen input
            const latInput = document.getElementById('latitude');
            const lngInput = document.getElementById('longitude');
            const radiusInput = document.getElementById('radius');
            
            // Nilai awal
            const defaultLat = parseFloat(latInput.value);
            const defaultLng = parseFloat(lngInput.value);
            const radiusVal = parseFloat(radiusInput.value);

            // 1. Inisialisasi Peta
            if (isNaN(defaultLat) || isNaN(defaultLng)) {
                console.error("Default coordinates are invalid.");
                return;
            }

            const map = L.map('map').setView([defaultLat, defaultLng], 16);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(map);

            let marker = L.marker([defaultLat, defaultLng]).addTo(map)
                .bindPopup("Lokasi Tersimpan Saat Ini").openPopup();
                
            let circle = L.circle([defaultLat, defaultLng], {
                radius: radiusVal,
                color: 'red',
                fillColor: '#f03',
                fillOpacity: 0.3
            }).addTo(map);
            
            // 2. Fungsi Update Input dan Marker (Disederhanakan untuk penggunaan internal)
            function updateMarker(lat, lng, radius) {
                // Pengecekan dasar validitas koordinat
                if (isNaN(lat) || isNaN(lng) || radius <= 0) {
                    // console.warn("Input koordinat tidak valid.");
                    return; 
                }
                
                // Pindahkan Marker dan Peta
                const newLatLng = [lat, lng];
                marker.setLatLng(newLatLng);
                circle.setLatLng(newLatLng);
                circle.setRadius(radius);
                
                // Geser tampilan peta ke titik baru (opsional, tapi disarankan)
                map.setView(newLatLng, map.getZoom()); 

                // Update Popup (jika diperlukan)
                marker.getPopup().setContent(`Lokasi Saat Ini: ${lat.toFixed(6)}, ${lng.toFixed(6)}`);
            }

            // 3. Listener Klik Peta (Mengambil koordinat dari klik)
            map.on('click', function(e) {
                const lat = e.latlng.lat;
                const lng = e.latlng.lng;
                const currentRadius = parseFloat(radiusInput.value);
                
                // Update input fields (karena ini dari klik peta)
                latInput.value = lat.toFixed(6);
                lngInput.value = lng.toFixed(6);

                updateMarker(lat, lng, currentRadius);
            });
            
            // --- Penambahan Baru: Listener untuk Input Manual ---
            
            // Fungsi untuk membaca dan memperbarui peta dari input fields
            function handleManualInput() {
                const lat = parseFloat(latInput.value);
                const lng = parseFloat(lngInput.value);
                const radius = parseFloat(radiusInput.value);

                // Update peta berdasarkan input manual
                if (!isNaN(lat) && !isNaN(lng) && radius > 0) {
                    updateMarker(lat, lng, radius);
                }
            }

            // 4. Listener Perubahan Latitude, Longitude, dan Radius
            latInput.addEventListener('input', handleManualInput);
            lngInput.addEventListener('input', handleManualInput);
            radiusInput.addEventListener('input', handleManualInput);

            // Penting: Memastikan peta me-render ulang (solusi masalah tampilan)
            setTimeout(() => {
                map.invalidateSize();
            }, 300);
        });
    </script>
</body>

</html>