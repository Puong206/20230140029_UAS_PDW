<?php
$pageTitle = 'Dashboard';
$activePage = 'dashboard';

// Memuat header
require_once 'templates/header_mahasiswa.php';
// Memuat koneksi database
require_once __DIR__ . '/../config.php';

// Pastikan session user_id tersedia
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}
$mahasiswa_id = $_SESSION['user_id'];
$nama_mahasiswa = $_SESSION['user_name'] ?? 'Mahasiswa'; // Menggunakan user_name dari header

// --- MENGHITUNG DATA STATISTIK ---

// 1. Jumlah Praktikum Diikuti
$stmt_praktikum = $conn->prepare("SELECT COUNT(id) as total FROM pendaftaran_praktikum WHERE id_mahasiswa = ?");
$stmt_praktikum->bind_param("i", $mahasiswa_id);
$stmt_praktikum->execute();
$result_praktikum = $stmt_praktikum->get_result()->fetch_assoc();
$total_praktikum = $result_praktikum['total'];
$stmt_praktikum->close();

// 2. Jumlah Tugas Selesai (sudah dinilai)
$stmt_selesai = $conn->prepare("SELECT COUNT(id) as total FROM laporan WHERE id_mahasiswa = ? AND nilai IS NOT NULL");
$stmt_selesai->bind_param("i", $mahasiswa_id);
$stmt_selesai->execute();
$result_selesai = $stmt_selesai->get_result()->fetch_assoc();
$tugas_selesai = $result_selesai['total'];
$stmt_selesai->close();

// 3. Jumlah Tugas Menunggu (dikumpul, belum dinilai)
$stmt_menunggu = $conn->prepare("SELECT COUNT(id) as total FROM laporan WHERE id_mahasiswa = ? AND nilai IS NULL");
$stmt_menunggu->bind_param("i", $mahasiswa_id);
$stmt_menunggu->execute();
$result_menunggu = $stmt_menunggu->get_result()->fetch_assoc();
$tugas_menunggu = $result_menunggu['total'];
$stmt_menunggu->close();

// --- MENGAMBIL NOTIFIKASI TERBARU ---
$notifikasi = [];

// Notifikasi 1: Nilai terbaru yang diberikan
$stmt_notif_nilai = $conn->prepare("SELECT m.judul_modul, mp.nama_praktikum FROM laporan l JOIN modul m ON l.id_modul = m.id JOIN mata_praktikum mp ON m.id_praktikum = mp.id WHERE l.id_mahasiswa = ? AND l.nilai IS NOT NULL ORDER BY l.tanggal_nilai DESC LIMIT 1");
$stmt_notif_nilai->bind_param("i", $mahasiswa_id);
$stmt_notif_nilai->execute();
$result_notif_nilai = $stmt_notif_nilai->get_result()->fetch_assoc();
if ($result_notif_nilai) {
    $notifikasi[] = [
        'icon' => '🔔',
        'text' => 'Nilai untuk <span class="font-semibold text-[#093880]">'.$result_notif_nilai['judul_modul'].'</span> di praktikum '.$result_notif_nilai['nama_praktikum'].' telah diberikan.'
    ];
}
$stmt_notif_nilai->close();

// Notifikasi 2: Pendaftaran praktikum terbaru
$stmt_notif_daftar = $conn->prepare("SELECT mp.nama_praktikum FROM pendaftaran_praktikum pp JOIN mata_praktikum mp ON pp.id_praktikum = mp.id WHERE pp.id_mahasiswa = ? ORDER BY pp.tanggal_daftar DESC LIMIT 1");
$stmt_notif_daftar->bind_param("i", $mahasiswa_id);
$stmt_notif_daftar->execute();
$result_notif_daftar = $stmt_notif_daftar->get_result()->fetch_assoc();
if ($result_notif_daftar) {
     $notifikasi[] = [
        'icon' => '✅',
        'text' => 'Anda berhasil mendaftar pada mata praktikum <span class="font-semibold text-[#093880]">'.$result_notif_daftar['nama_praktikum'].'</span>.'
    ];
}
$stmt_notif_daftar->close();

?>

<div class="bg-gradient-to-r from-[#093880] to-[#41c7c7] text-white p-8 rounded-xl shadow-lg mb-8">
    <h1 class="text-4xl font-bold">Selamat Datang, <?php echo htmlspecialchars($nama_mahasiswa); ?>!</h1>
    <p class="mt-2 text-lg opacity-90">Terus semangat dalam menyelesaikan semua modul praktikummu.</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
    
    <div class="bg-white p-6 rounded-xl shadow-md flex flex-col items-center justify-center transition hover:shadow-xl hover:-translate-y-1">
        <div class="text-5xl font-extrabold text-[#093880]"><?php echo $total_praktikum; ?></div>
        <div class="mt-2 text-lg font-medium text-gray-600">Praktikum Diikuti</div>
    </div>
    
    <div class="bg-white p-6 rounded-xl shadow-md flex flex-col items-center justify-center transition hover:shadow-xl hover:-translate-y-1">
        <div class="text-5xl font-extrabold text-green-500"><?php echo $tugas_selesai; ?></div>
        <div class="mt-2 text-lg font-medium text-gray-600">Tugas Selesai</div>
    </div>
    
    <div class="bg-white p-6 rounded-xl shadow-md flex flex-col items-center justify-center transition hover:shadow-xl hover:-translate-y-1">
        <div class="text-5xl font-extrabold text-yellow-500"><?php echo $tugas_menunggu; ?></div>
        <div class="mt-2 text-lg font-medium text-gray-600">Tugas Menunggu Nilai</div>
    </div>
    
</div>

<div class="bg-white p-6 rounded-xl shadow-md">
    <h3 class="text-2xl font-bold text-[#0a1f33] mb-4">Aktivitas Terbaru</h3>
    
    <?php if (!empty($notifikasi)): ?>
    <ul class="space-y-4">
        <?php foreach ($notifikasi as $item): ?>
            <li class="flex items-start p-3 border-b border-gray-100 last:border-b-0">
                <span class="text-xl mr-4"><?php echo $item['icon']; ?></span>
                <div class="text-gray-700">
                    <?php echo $item['text']; // Teks sudah di-escape saat query atau tidak mengandung input user berbahaya ?>
                </div>
            </li>
        <?php endforeach; ?>
    </ul>
    <?php else: ?>
        <p class="text-gray-500 text-center py-4">Tidak ada aktivitas terbaru untuk ditampilkan.</p>
    <?php endif; ?>

</div>


<?php
// Selalu tutup koneksi di akhir
$conn->close();
// Panggil Footer
require_once 'templates/footer_mahasiswa.php';
?>