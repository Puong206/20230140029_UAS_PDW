<?php
// 1. Definisi Variabel
$pageTitle = 'Dashboard';
$activePage = 'dashboard';

// 2. Panggil koneksi TERLEBIH DAHULU sebelum header
require_once __DIR__ . '/../config.php';

// 3. Keamanan & Role Check (Sama seperti sebelumnya)
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'asisten') {
    header("Location: ../login.php");
    exit();
}
$asistenName = $_SESSION['nama'] ?? '';

// Mengambil Data Statistik
$total_praktikum = $conn->query("SELECT COUNT(id) as total FROM mata_praktikum")->fetch_assoc()['total'];
$laporan_belum_dinilai = $conn->query("SELECT COUNT(id) as total FROM laporan WHERE nilai IS NULL")->fetch_assoc()['total'];
$total_mahasiswa = $conn->query("SELECT COUNT(id) as total FROM users WHERE role = 'mahasiswa'")->fetch_assoc()['total'];

// Mengambil Laporan Terbaru untuk Dinilai
$sql_perlu_dinilai = "SELECT u.nama, m.judul_modul, l.tanggal_kumpul 
                      FROM laporan l JOIN users u ON l.id_mahasiswa = u.id JOIN modul m ON l.id_modul = m.id
                      WHERE l.nilai IS NULL ORDER BY l.tanggal_kumpul ASC LIMIT 5";
$result_perlu_dinilai = $conn->query($sql_perlu_dinilai);

// 4. Panggil Header SETELAH semua query selesai
require_once 'templates/header.php';
?>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <div class="stats shadow bg-base-100">
        <div class="stat">
            <div class="stat-figure text-primary">
                <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/>
                </svg>
            </div>
            <div class="stat-title">Total Praktikum</div>
            <div class="stat-value text-primary"><?php echo $total_praktikum; ?></div>
            <div class="stat-desc">Mata praktikum aktif</div>
        </div>
    </div>
    <div class="stats shadow bg-base-100">
        <div class="stat">
            <div class="stat-figure text-warning">
                <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
            </div>
            <div class="stat-title">Perlu Dinilai</div>
            <div class="stat-value text-warning"><?php echo $laporan_belum_dinilai; ?></div>
            <div class="stat-desc">Laporan menunggu penilaian</div>
        </div>
    </div>
    <div class="stats shadow bg-base-100">
        <div class="stat">
            <div class="stat-figure text-accent">
                <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            </div>
            <div class="stat-title">Total Mahasiswa</div>
            <div class="stat-value text-accent"><?php echo $total_mahasiswa; ?></div>
            <div class="stat-desc">Mahasiswa terdaftar</div>
        </div>
    </div>
</div>

<div class="card bg-base-100 shadow-xl mt-8">
    <div class="card-body">
        <h2 class="card-title text-base-content" style="color: oklch(100% 0 0);">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
            Antrian Penilaian Teratas
        </h2>
        <p class="text-base-content/70 mb-4" style="color: oklch(100% 0 0);">Laporan yang memerlukan penilaian segera</p>
        <div class="overflow-x-auto">
            <table class="table table-zebra">
                <thead>
                    <tr>
                        <th>Mahasiswa</th>
                        <th>Modul</th>
                        <th>Waktu Kumpul</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result_perlu_dinilai->num_rows > 0): ?>
                        <?php while($row = $result_perlu_dinilai->fetch_assoc()): ?>
                        <tr class="hover">
                            <td>
                                <div class="flex items-center gap-3">
                                    <div class="avatar placeholder">
                                        <div class="bg-neutral text-neutral-content rounded-full w-8">
                                            <span class="text-xs"><?php echo strtoupper(substr($row['nama'], 0, 2)); ?></span>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="font-bold"><?php echo htmlspecialchars($row['nama']); ?></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge badge-ghost badge-sm"><?php echo htmlspecialchars($row['judul_modul']); ?></span>
                            </td>
                            <td>
                                <span class="text-sm opacity-75"><?php echo date('d M Y, H:i', strtotime($row['tanggal_kumpul'])); ?></span>
                            </td>
                            <td>
                                <a href="laporan.php?filter_status=belum_dinilai" class="btn btn-primary btn-sm">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                    Nilai
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4">
                                <div class="text-center p-8">
                                    <svg class="w-16 h-16 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="color: oklch(64.8% 0.223 136.073);">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <div class="text-base-content/70">
                                        <p class="font-semibold">Semua laporan sudah dinilai!</p>
                                        <p class="text-sm">Tidak ada laporan yang perlu dinilai saat ini.</p>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
// 3. Panggil Footer
$conn->close();
require_once 'templates/footer.php';
?>