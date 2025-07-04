<?php
$pageTitle = 'Praktikum Saya';
$activePage = 'my_courses'; 

// 1. PASTIKAN PATH INI BENAR
//    Jika file my_courses.php ada di root folder, pathnya adalah 'templates/header_mahasiswa.php'
//    Jika ada di dalam folder 'mahasiswa', pathnya mungkin 'templates/header_mahasiswa.php' atau '../templates/header_mahasiswa.php'
require_once 'templates/header_mahasiswa.php'; 

// 2. PASTIKAN PATH KONEKSI DATABASE BENAR
//    Dan pastikan file db.php terhubung ke database `pengumpulantugas`
require_once '../config.php'; 

// 3. PASTIKAN SESSION 'user_id' TERSEDIA SETELAH LOGIN
//    Kode ini mengambil ID mahasiswa dari session
if (!isset($_SESSION['user_id'])) {
    // Jika tidak ada session, mungkin lebih baik diarahkan kembali ke login
    echo "Error: Sesi pengguna tidak ditemukan. Silakan login kembali.";
    exit();
}
$mahasiswa_id = $_SESSION['user_id'];

// Query ini sudah sesuai dengan struktur database Anda.
// Ia akan mengambil data praktikum berdasarkan mahasiswa yang login.
$sql = "SELECT mp.id, mp.nama_praktikum, mp.deskripsi 
        FROM mata_praktikum mp
        INNER JOIN pendaftaran_praktikum pp ON mp.id = pp.id_praktikum
        WHERE pp.id_mahasiswa = ?";

$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die("Error preparing the statement: " . $conn->error);
}
$stmt->bind_param("i", $mahasiswa_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!-- Konten Halaman -->
<div class="px-4">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-[#0a1f33]">Praktikum yang Anda Ikuti</h1>
        <p class="text-lg text-[#6B7280] mt-1">Berikut adalah daftar semua praktikum yang telah Anda daftarkan.</p>
    </div>

    <?php if ($result->num_rows > 0): ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php while ($course = $result->fetch_assoc()): ?>
                <!-- Kartu Praktikum -->
                <div class="bg-white rounded-xl shadow-lg overflow-hidden transform hover:-translate-y-1 transition-all duration-300 hover:shadow-2xl">
                    <div class="p-6">
                        <h2 class="text-xl font-bold text-[#093880] mb-2"><?php echo htmlspecialchars($course['nama_praktikum']); ?></h2>
                        <p class="text-[#6B7280] text-sm leading-relaxed mb-4">
                            <?php 
                                // Memotong deskripsi jika terlalu panjang
                                $deskripsi = htmlspecialchars($course['deskripsi']);
                                echo strlen($deskripsi) > 100 ? substr($deskripsi, 0, 100) . '...' : $deskripsi;
                            ?>
                        </p>
                        <!-- Link menuju halaman detail praktikum -->
                        <a href="course_detail.php?id=<?php echo $course['id']; ?>" class="inline-block bg-[#eb8317] text-white font-bold py-2 px-4 rounded-md hover:bg-opacity-90 transition-colors duration-300">
                            Lihat Detail & Tugas
                        </a>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <!-- Pesan jika mahasiswa belum mendaftar praktikum apapun -->
        <div class="bg-white text-center p-12 rounded-xl shadow-md">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                <path vector-effect="non-scaling-stroke" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z" />
            </svg>
            <h3 class="mt-2 text-xl font-medium text-[#0a1f33]">Belum Ada Praktikum</h3>
            <p class="mt-1 text-base text-[#6B7280]">Anda belum terdaftar di praktikum manapun.</p>
            <div class="mt-6">
                <a href="courses.php" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-[#093880] hover:bg-opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#093880]">
                    Cari Praktikum Baru
                </a>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php
// Panggil Footer
require_once 'templates/footer_mahasiswa.php';
?>