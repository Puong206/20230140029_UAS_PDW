<?php
// Pastikan session selalu dimulai di paling atas
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Panggil file koneksi terlebih dahulu
require_once __DIR__ . '/../config.php';

// Validasi & Keamanan Awal
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    // Keluar dengan pesan error sebelum mencetak header
    die("Error: ID Praktikum tidak valid.");
}
$praktikum_id = $_GET['id'];
$mahasiswa_id = $_SESSION['user_id'];

// --- BLOK LOGIKA PEMROSESAN FORM (POST) DIPINDAHKAN KE ATAS ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['kumpul_laporan'])) {
    $modul_id = $_POST['modul_id'];
    $upload_dir = __DIR__ . '/../uploads/laporan/';
    
    if (isset($_FILES['file_laporan']) && $_FILES['file_laporan']['error'] == 0) {
        $file_name = uniqid() . '-' . $mahasiswa_id . '-' . basename($_FILES['file_laporan']['name']);
        
        // Cek apakah direktori bisa ditulis
        if (is_writable($upload_dir) && move_uploaded_file($_FILES['file_laporan']['tmp_name'], $upload_dir . $file_name)) {
            $sql_upsert = "INSERT INTO laporan (id_modul, id_mahasiswa, file_laporan, tanggal_kumpul) VALUES (?, ?, ?, NOW())
                           ON DUPLICATE KEY UPDATE file_laporan = VALUES(file_laporan), tanggal_kumpul = NOW(), nilai = NULL, feedback = NULL";
            $stmt_upsert = $conn->prepare($sql_upsert);
            $stmt_upsert->bind_param("iis", $modul_id, $mahasiswa_id, $file_name);
            $_SESSION['message'] = $stmt_upsert->execute() ? ['type' => 'sukses', 'text' => 'Laporan berhasil diunggah!'] : ['type' => 'error', 'text' => 'Gagal menyimpan data laporan.'];
            $stmt_upsert->close();
        } else {
            $_SESSION['message'] = ['type' => 'error', 'text' => 'Gagal memindahkan file. Pastikan folder uploads/laporan ada dan writable.'];
        }
    } else {
        $_SESSION['message'] = ['type' => 'error', 'text' => 'Tidak ada file yang diunggah atau terjadi error.'];
    }
    // Redirect sekarang akan berhasil karena belum ada HTML yang tercetak
    header("Location: course_detail.php?id=" . $praktikum_id);
    exit();
}

// --- PERSIAPAN DATA UNTUK DITAMPILKAN ---

// Otorisasi: Pastikan mahasiswa terdaftar
$stmt_auth = $conn->prepare("SELECT id FROM pendaftaran_praktikum WHERE id_mahasiswa = ? AND id_praktikum = ?");
$stmt_auth->bind_param("ii", $mahasiswa_id, $praktikum_id);
$stmt_auth->execute();
if ($stmt_auth->get_result()->num_rows === 0) {
    die("Error: Anda tidak terdaftar di praktikum ini.");
}
$stmt_auth->close();

// Ambil nama praktikum untuk judul halaman
$stmt_praktikum = $conn->prepare("SELECT nama_praktikum FROM mata_praktikum WHERE id = ?");
$stmt_praktikum->bind_param("i", $praktikum_id);
$stmt_praktikum->execute();
$praktikum_data = $stmt_praktikum->get_result()->fetch_assoc();
$pageTitle = $praktikum_data['nama_praktikum'] ?? 'Detail Praktikum';
$stmt_praktikum->close();

// Ambil semua modul dan status pengumpulannya
$sql_modules = "SELECT m.id AS modul_id, m.judul_modul, m.deskripsi_modul, m.file_materi,
                       l.file_laporan, l.tanggal_kumpul, l.nilai, l.feedback
                FROM modul m
                LEFT JOIN laporan l ON m.id = l.id_modul AND l.id_mahasiswa = ?
                WHERE m.id_praktikum = ?
                ORDER BY m.id ASC";
$stmt_modules = $conn->prepare($sql_modules);
$stmt_modules->bind_param("ii", $mahasiswa_id, $praktikum_id);
$stmt_modules->execute();
$result_modules = $stmt_modules->get_result();

// --- BARU SETELAH SEMUA LOGIKA SELESAI, PANGGIL HEADER ---
$activePage = 'my_courses';
require_once 'templates/header_mahasiswa.php';
?>

<!-- Main Course Detail Container with Enhanced Design -->
<div class="max-w-full">
    <!-- Hero Section with Enhanced Design -->
    <div class="relative hero bg-gradient-to-br from-[oklch(42%_0.199_265.638)] via-[oklch(48%_0.211_225.457)] to-[oklch(55%_0.223_136.073)] rounded-3xl shadow-2xl mb-8 animate-fade-in overflow-hidden mx-2 sm:mx-0">
        <!-- Animated Background Pattern -->
        <div class="absolute inset-0 opacity-15">
            <div class="absolute top-0 left-0 w-32 h-32 bg-white rounded-full -translate-x-16 -translate-y-16 blur-xl animate-float"></div>
            <div class="absolute top-10 right-10 w-20 h-20 bg-white rounded-full opacity-50 blur-lg animate-float-delayed"></div>
            <div class="absolute bottom-0 right-0 w-40 h-40 bg-white rounded-full translate-x-20 translate-y-20 blur-2xl animate-pulse-gentle"></div>
        </div>
        
        <!-- Decorative Elements -->
        <div class="absolute top-8 left-8 text-white/20 text-4xl animate-sparkle">📚</div>
        <div class="absolute bottom-8 right-8 text-white/20 text-3xl animate-sparkle" style="animation-delay: 2s">🎯</div>
        
        <div class="hero-content text-center text-white py-12 px-6 sm:py-16 sm:px-8 relative z-10">
            <div class="max-w-4xl mx-auto">
                <!-- Enhanced Icon Section -->
                <div class="flex justify-center mb-6">
                    <div class="w-20 h-20 bg-white/20 rounded-2xl flex items-center justify-center shadow-lg animate-bounce-gentle">
                        <svg class="w-10 h-10 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                    </div>
                </div>
                
                <!-- Enhanced Typography -->
                <h1 class="text-3xl sm:text-4xl md:text-5xl font-extrabold mb-8 pb-2 bg-gradient-to-r from-white via-blue-100 to-white bg-clip-text text-transparent animate-shimmer leading-relaxed">
                    <?php echo htmlspecialchars($pageTitle); ?>
                </h1>
                <p class="text-lg sm:text-xl opacity-90 mb-6 leading-relaxed max-w-3xl mx-auto px-4">
                    Akses semua modul praktikum dan kelola pengumpulan tugas Anda dengan mudah
                </p>
                
                <!-- Enhanced Breadcrumbs di Hero -->
                <div class="flex justify-center mb-6">
                    <div class="breadcrumbs text-sm text-white">
                        <ul>
                            <li><a href="my_courses.php" class="text-white hover:text-[oklch(70%_0.213_47.604)] transition-colors">Praktikum Saya</a></li> 
                            <li class="!text-white font-semibold"><?php echo htmlspecialchars($pageTitle); ?></li>
                        </ul>
                    </div>
                </div>
                
                <!-- Enhanced Badges -->
                <div class="flex justify-center gap-4 flex-wrap">
                    <div class="badge bg-white/20 text-white border-white/30 px-4 py-2 text-sm hover:bg-white/30 transition-all duration-300">
                        <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <span>Modul Praktikum</span>
                    </div>
                    <div class="badge bg-emerald-500/30 text-white border-emerald-300/30 px-4 py-2 text-sm hover:bg-emerald-500/40 transition-all duration-300">
                        <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                        </svg>
                        <span>Upload Laporan</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Enhanced Alert Messages -->
    <?php
    if (isset($_SESSION['message'])) {
        $alert_type = $_SESSION['message']['type'] === 'sukses' ? 'alert-success' : 'alert-error';
        $icon = $_SESSION['message']['type'] === 'sukses' ? 
            '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />' :
            '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />';
        echo '<div class="' . $alert_type . ' shadow-xl mb-8 mx-2 sm:mx-0 animate-fade-in border-none">
                <div class="flex items-center gap-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current flex-shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24">' . $icon . '</svg>
                    <span class="font-semibold">' . htmlspecialchars($_SESSION['message']['text']) . '</span>
                </div>
              </div>';
        unset($_SESSION['message']);
    }
    ?>
    
    <!-- Enhanced Module Section -->
    <div class="px-2 sm:px-0">
        <div class="grid gap-6">
        <?php if ($result_modules->num_rows > 0): ?>
            <?php while ($module = $result_modules->fetch_assoc()): ?>
            <div class="card bg-gradient-to-br from-base-100 to-base-200/50 shadow-xl border border-base-300/50 hover:shadow-2xl transition-all duration-300 animate-fade-in-up">
                <div class="card-body p-6 sm:p-8">
                    <!-- Module Header -->
                    <div class="flex items-start justify-between mb-6">
                        <div class="flex items-start gap-4">
                            <div class="p-3 bg-[oklch(42%_0.199_265.638)]/10 rounded-xl">
                                <svg class="w-6 h-6 text-[oklch(42%_0.199_265.638)]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-xl sm:text-2xl font-bold text-base-content mb-2">
                                    <?php echo htmlspecialchars($module['judul_modul']); ?>
                                </h3>
                                <p class="text-base-content/70 line-clamp-2 max-w-2xl">
                                    <?php echo nl2br(htmlspecialchars($module['deskripsi_modul'])); ?>
                                </p>
                            </div>
                        </div>
                        
                        <!-- Status Badge -->
                        <?php if(!empty($module['file_laporan'])): ?>
                            <div class="badge bg-emerald-500/20 text-white border-emerald-300/30 px-3 py-2">
                                <svg class="w-4 h-4 mr-1 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4" />
                                </svg>
                                <span class="text-sm font-medium !text-white">Terkumpul</span>
                            </div>
                        <?php else: ?>
                            <div class="badge bg-amber-500/20 text-white border-amber-300/30 px-3 py-2">
                                <svg class="w-4 h-4 mr-1 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span class="text-sm font-medium !text-white">Belum</span>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Module Content Grid -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        <!-- Materi Section -->
                        <div class="space-y-4">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="p-2 bg-blue-500/10 rounded-lg">
                                    <svg class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                                <h4 class="text-lg font-semibold text-base-content">Materi Praktikum</h4>
                            </div>
                            
                            <?php if(!empty($module['file_materi'])): ?>
                                <a href="../uploads/materi/<?php echo htmlspecialchars($module['file_materi']); ?>" target="_blank" 
                                   class="btn btn-primary shadow-lg hover:shadow-xl transition-all duration-300 w-full sm:w-auto">
                                    <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    Unduh Materi
                                </a>
                            <?php else: ?>
                                <div class="alert alert-warning shadow-md">
                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                                    </svg>
                                    <span>Materi belum tersedia</span>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Laporan Section -->
                        <div class="space-y-4">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="p-2 bg-emerald-500/10 rounded-lg">
                                    <svg class="w-5 h-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                                <h4 class="text-lg font-semibold text-base-content">Status Laporan Anda</h4>
                            </div>
                            
                            <?php if(!empty($module['file_laporan'])): ?>
                                <div class="card bg-emerald-50 border border-emerald-200 shadow-md">
                                    <div class="card-body p-5">
                                        <div class="flex items-center gap-3 mb-4">
                                            <div class="p-2 bg-emerald-500/20 rounded-lg">
                                                <svg class="w-5 h-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                            </div>
                                            <div>
                                                <p class="text-sm font-semibold text-emerald-800">Laporan Terkumpul</p>
                                                <p class="text-xs text-emerald-600"><?php echo date('d M Y, H:i', strtotime($module['tanggal_kumpul'])); ?></p>
                                            </div>
                                        </div>
                                        
                                        <!-- Nilai Section -->
                                        <div class="mb-4">
                                            <div class="flex items-center justify-between mb-2">
                                                <span class="font-semibold text-base-content">Nilai:</span>
                                                <?php if($module['nilai'] !== null): ?>
                                                    <div class="badge badge-success badge-lg font-bold text-white">
                                                        <?php echo htmlspecialchars($module['nilai']); ?>
                                                    </div>
                                                <?php else: ?>
                                                    <div class="badge badge-warning badge-lg font-semibold text-white">
                                                        <span class="!text-white">Menunggu Penilaian</span>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        
                                        <!-- Feedback Section -->
                                        <?php if(!empty($module['feedback'])): ?>
                                            <div class="space-y-2">
                                                <p class="font-semibold text-base-content">Feedback Asisten:</p>
                                                <div class="bg-white border-l-4 border-emerald-400 p-3 rounded-r-lg">
                                                    <p class="text-sm text-base-content/80 italic">
                                                        <?php echo nl2br(htmlspecialchars($module['feedback'])); ?>
                                                    </p>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <!-- Re-upload Section -->
                                        <div class="mt-4">
                                            <details class="text-sm">
                                                <summary class="cursor-pointer text-[oklch(42%_0.199_265.638)] hover:text-[oklch(37%_0.199_265.638)] font-medium transition-colors duration-200">
                                                    Unggah Ulang Laporan
                                                </summary>
                                                <form action="course_detail.php?id=<?php echo $praktikum_id; ?>" method="POST" enctype="multipart/form-data" class="mt-3 space-y-3">
                                                    <input type="hidden" name="modul_id" value="<?php echo $module['modul_id']; ?>">
                                                    <input type="file" name="file_laporan" class="file-input file-input-bordered file-input-sm w-full" required />
                                                    <button type="submit" name="kumpul_laporan" class="btn btn-primary btn-sm">
                                                        <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                                        </svg>
                                                        Kirim Ulang
                                                    </button>
                                                </form>
                                            </details>
                                        </div>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="card bg-amber-50 border border-amber-200 shadow-md">
                                    <div class="card-body p-5">
                                        <div class="flex items-center gap-3 mb-4">
                                            <div class="p-2 bg-amber-500/20 rounded-lg">
                                                <svg class="w-5 h-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                                </svg>
                                            </div>
                                            <div>
                                                <p class="text-sm font-semibold text-amber-800">Upload Laporan</p>
                                                <p class="text-xs text-amber-600">Pilih file untuk dikumpulkan</p>
                                            </div>
                                        </div>
                                        
                                        <form action="course_detail.php?id=<?php echo $praktikum_id; ?>" method="POST" enctype="multipart/form-data" class="space-y-4">
                                            <input type="hidden" name="modul_id" value="<?php echo $module['modul_id']; ?>">
                                            <input type="file" name="file_laporan" class="file-input file-input-bordered w-full" required />
                                            <button type="submit" name="kumpul_laporan" class="btn btn-primary shadow-lg hover:shadow-xl transition-all duration-300 w-full">
                                                <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                                </svg>
                                                Kumpulkan Laporan
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <!-- Enhanced Empty State -->
            <div class="card bg-gradient-to-br from-base-100 to-base-200/50 shadow-xl border border-base-300/50">
                <div class="card-body text-center py-16 px-8">
                    <div class="flex justify-center mb-6">
                        <div class="w-24 h-24 bg-base-300/30 rounded-2xl flex items-center justify-center">
                            <svg class="w-12 h-12 text-base-content/40" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                    </div>
                    <h3 class="text-xl font-bold text-base-content mb-2">Belum Ada Modul</h3>
                    <p class="text-base-content/60 max-w-md mx-auto">
                        Modul untuk praktikum ini belum tersedia. Silakan hubungi asisten atau periksa kembali nanti.
                    </p>
                </div>
            </div>
        <?php endif; ?>
        </div>
    </div>
</div>

<?php
// Panggil Footer
$stmt_modules->close();
$conn->close();
require_once 'templates/footer_mahasiswa.php';
?>