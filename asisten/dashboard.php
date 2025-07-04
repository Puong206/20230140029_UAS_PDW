<?php
// 1. Definisi Variabel
$pageTitle = 'Dashboard';
$activePage = 'dashboard';

// 2. Panggil Header & Koneksi
require_once 'templates/header.php';
require_once __DIR__ . '/../config.php';

// 3. Keamanan & Role Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'asisten') {
    header("Location: ../login.php");
    exit();
}
$asistenName = $_SESSION['nama'] ?? '';

// --- Mengambil Data untuk Statistik & Tampilan ---

// 1. Data untuk Kartu Statistik
$total_praktikum = $conn->query("SELECT COUNT(id) as total FROM mata_praktikum")->fetch_assoc()['total'];
$laporan_masuk = $conn->query("SELECT COUNT(id) as total FROM laporan")->fetch_assoc()['total'];
$laporan_belum_dinilai = $conn->query("SELECT COUNT(id) as total FROM laporan WHERE nilai IS NULL")->fetch_assoc()['total'];

// 2. Data untuk Grafik (Laporan 7 Hari Terakhir)
$chart_labels = [];
$chart_data = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $chart_labels[] = date('d M', strtotime($date));
    $sql = "SELECT COUNT(id) as total FROM laporan WHERE DATE(tanggal_kumpul) = '$date'";
    $chart_data[] = $conn->query($sql)->fetch_assoc()['total'];
}

// 3. Data untuk Daftar "Perlu Dinilai" (5 Laporan terlama yang belum dinilai)
$sql_perlu_dinilai = "SELECT u.nama, m.judul_modul, l.tanggal_kumpul 
                      FROM laporan l
                      JOIN users u ON l.id_mahasiswa = u.id
                      JOIN modul m ON l.id_modul = m.id
                      WHERE l.nilai IS NULL
                      ORDER BY l.tanggal_kumpul ASC
                      LIMIT 5";
$result_perlu_dinilai = $conn->query($sql_perlu_dinilai);

// Fungsi Bantuan
function format_waktu_lalu($datetime) {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);
    if ($diff->d > 0) return $diff->d . ' hari lalu';
    if ($diff->h > 0) return $diff->h . ' jam lalu';
    if ($diff->i > 0) return $diff->i . ' menit lalu';
    return 'Baru saja';
}
setlocale(LC_TIME, 'id_ID.UTF-8');
$tanggal_hari_ini = strftime('%A, %d %B %Y');
?>

<div class="mb-8">
    <h1 class="text-3xl font-bold text-gray-800">Selamat Datang Kembali, <?php echo htmlspecialchars(explode(' ', $asistenName)[0]); ?>!</h1>
    <p class="text-gray-500 mt-1"><?php echo $tanggal_hari_ini; ?></p>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
    <div class="bg-gradient-to-br from-blue-500 to-indigo-600 text-white p-6 rounded-2xl shadow-lg flex items-center space-x-4">
        <div class="bg-white/30 p-4 rounded-2xl"><svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" /></svg></div>
        <div>
            <p class="text-sm text-blue-100">Total Praktikum</p>
            <p class="text-3xl font-bold"><?php echo $total_praktikum; ?></p>
        </div>
    </div>
    <div class="bg-gradient-to-br from-green-500 to-teal-600 text-white p-6 rounded-2xl shadow-lg flex items-center space-x-4">
        <div class="bg-white/30 p-4 rounded-2xl"><svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg></div>
        <div>
            <p class="text-sm text-green-100">Total Laporan Masuk</p>
            <p class="text-3xl font-bold"><?php echo $laporan_masuk; ?></p>
        </div>
    </div>
    <div class="bg-gradient-to-br from-yellow-500 to-orange-600 text-white p-6 rounded-2xl shadow-lg flex items-center space-x-4">
        <div class="bg-white/30 p-4 rounded-2xl"><svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg></div>
        <div>
            <p class="text-sm text-yellow-100">Perlu Penilaian</p>
            <p class="text-3xl font-bold"><?php echo $laporan_belum_dinilai; ?></p>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <div class="lg:col-span-2 bg-white p-6 rounded-2xl shadow-lg">
        <h3 class="text-xl font-bold text-gray-800 mb-4">Aktivitas Laporan (7 Hari Terakhir)</h3>
        <div>
            <canvas id="laporanChart"></canvas>
        </div>
    </div>

    <div class="bg-white p-6 rounded-2xl shadow-lg">
        <h3 class="text-xl font-bold text-gray-800 mb-4">Perlu Dinilai</h3>
        <div class="space-y-4">
            <?php if ($result_perlu_dinilai->num_rows > 0): ?>
                <?php while($row = $result_perlu_dinilai->fetch_assoc()): ?>
                <div class="flex items-center">
                    <div class="p-3 bg-gray-100 rounded-full mr-4">
                        <svg class="w-5 h-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-gray-800"><?php echo htmlspecialchars($row['nama']); ?></p>
                        <p class="text-xs text-gray-500">Mengumpulkan "<?php echo htmlspecialchars($row['judul_modul']); ?>" - <?php echo format_waktu_lalu($row['tanggal_kumpul']); ?></p>
                    </div>
                    <a href="laporan.php?filter_status=belum_dinilai" class="ml-auto text-sm font-semibold text-blue-600 hover:underline">Nilai</a>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="text-center py-8">
                    <p class="text-gray-500">🎉 Semua laporan sudah dinilai!</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const ctx = document.getElementById('laporanChart').getContext('2d');
    
    // Gradient untuk latar belakang grafik
    let gradient = ctx.createLinearGradient(0, 0, 0, 400);
    gradient.addColorStop(0, 'rgba(9, 56, 128, 0.5)');
    gradient.addColorStop(1, 'rgba(9, 56, 128, 0)');

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($chart_labels); ?>,
            datasets: [{
                label: 'Laporan Masuk',
                data: <?php echo json_encode($chart_data); ?>,
                backgroundColor: gradient,
                borderColor: '#093880',
                borderWidth: 3,
                tension: 0.4,
                fill: true,
                pointBackgroundColor: '#093880',
                pointRadius: 5,
                pointHoverRadius: 7
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
});
</script>


<?php
// 3. Panggil Footer
$conn->close();
require_once 'templates/footer.php';
?>