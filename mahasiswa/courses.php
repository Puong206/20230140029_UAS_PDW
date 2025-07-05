<?php

require_once __DIR__ . '/../config.php';

// Pastikan user_id ada di session
if (!isset($_SESSION['user_id'])) {
    // Seharusnya sudah ditangani header, tapi sebagai pengaman tambahan
    header("Location: ../login.php");
    exit();
}
$mahasiswa_id = $_SESSION['user_id'];

// --- PROSES PENDAFTARAN ---
// Cek jika ada form yang disubmit untuk mendaftar
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['daftar_praktikum'])) {
    $praktikum_id_to_daftar = $_POST['praktikum_id'];

    // 1. Cek dulu apakah sudah terdaftar untuk menghindari error
    $checkSql = "SELECT id FROM pendaftaran_praktikum WHERE id_mahasiswa = ? AND id_praktikum = ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param("ii", $mahasiswa_id, $praktikum_id_to_daftar);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows === 0) {
        // 2. Jika belum, daftarkan
        $insertSql = "INSERT INTO pendaftaran_praktikum (id_mahasiswa, id_praktikum) VALUES (?, ?)";
        $insertStmt = $conn->prepare($insertSql);
        $insertStmt->bind_param("ii", $mahasiswa_id, $praktikum_id_to_daftar);
        if ($insertStmt->execute()) {
            $_SESSION['message'] = ['type' => 'sukses', 'text' => 'Anda berhasil mendaftar praktikum!'];
        } else {
            $_SESSION['message'] = ['type' => 'error', 'text' => 'Terjadi kesalahan saat mendaftar.'];
        }
        $insertStmt->close();
    } else {
        // Sebenarnya tombol sudah di-disable, ini hanya pengaman
        $_SESSION['message'] = ['type' => 'error', 'text' => 'Anda sudah terdaftar di praktikum ini.'];
    }
    $checkStmt->close();

    // Redirect untuk mencegah re-submit form saat refresh
    header("Location: courses.php");
    exit();
}

$pageTitle = 'Cari Praktikum';
$activePage = 'courses';

// Menggunakan __DIR__ untuk path yang lebih andal
require_once __DIR__ . '/templates/header_mahasiswa.php';

// --- AMBIL DATA UNTUK DITAMPILKAN ---

// 1. Ambil daftar ID praktikum yang sudah diikuti mahasiswa
$enrolled_courses_ids = [];
$enrolledSql = "SELECT id_praktikum FROM pendaftaran_praktikum WHERE id_mahasiswa = ?";
$enrolledStmt = $conn->prepare($enrolledSql);
$enrolledStmt->bind_param("i", $mahasiswa_id);
$enrolledStmt->execute();
$enrolledResult = $enrolledStmt->get_result();
while ($row = $enrolledResult->fetch_assoc()) {
    $enrolled_courses_ids[] = $row['id_praktikum'];
}
$enrolledStmt->close();

// 2. Ambil semua data mata praktikum yang ada di sistem
$all_courses_sql = "SELECT id, nama_praktikum, deskripsi FROM mata_praktikum ORDER BY nama_praktikum ASC";
$all_courses_result = $conn->query($all_courses_sql);

?>

<!-- Enhanced Page Header with Hero Style -->
<div class="relative bg-gradient-to-br from-[oklch(42%_0.199_265.638)] via-[oklch(48%_0.211_225.457)] to-[oklch(55%_0.223_136.073)] rounded-3xl shadow-2xl mb-8 animate-fade-in overflow-hidden mx-2 sm:mx-0">
    <!-- Animated Background Pattern -->
    <div class="absolute inset-0 opacity-10">
        <div class="absolute top-0 left-0 w-24 h-24 bg-white rounded-full -translate-x-12 -translate-y-12 blur-xl animate-float"></div>
        <div class="absolute top-6 right-8 w-16 h-16 bg-white rounded-full opacity-50 blur-lg animate-float-delayed"></div>
        <div class="absolute bottom-0 right-0 w-32 h-32 bg-white rounded-full translate-x-16 translate-y-16 blur-2xl animate-pulse-gentle"></div>
    </div>
    
    <!-- Content -->
    <div class="relative z-10 text-center text-white py-12 px-6 sm:py-16 sm:px-8">
        <div class="max-w-4xl mx-auto">
            <!-- Icon -->
            <div class="flex justify-center mb-6">
                <div class="w-20 h-20 bg-white/20 rounded-2xl flex items-center justify-center shadow-lg animate-bounce-gentle">
                    <svg class="w-10 h-10 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            </div>
            
            <!-- Typography -->
            <h1 class="text-3xl sm:text-4xl md:text-5xl font-extrabold mb-8 pb-2 bg-gradient-to-r from-white via-blue-100 to-white bg-clip-text text-transparent animate-shimmer leading-relaxed">
                Katalog Praktikum
            </h1>
            <p class="text-lg sm:text-xl opacity-90 leading-relaxed max-w-2xl mx-auto px-4">
                Temukan dan daftarkan diri Anda pada praktikum yang sesuai dengan minat dan kebutuhan pembelajaran Anda 🎯
            </p>
            
            <!-- Stats Badge -->
            <div class="flex justify-center mt-6">
                <div class="badge bg-white/20 text-white border-white/30 px-4 py-3 text-sm sm:text-base hover:bg-white/30 transition-all duration-300">
                    <svg class="w-4 h-4 mr-2 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                    <span class="!text-white">Praktikum Tersedia</span>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Tampilkan pesan notifikasi jika ada dengan styling yang enhanced
if (isset($_SESSION['message'])) {
    $alert_type = $_SESSION['message']['type'] === 'sukses' ? 'alert-success' : 'alert-error';
    $bg_color = $_SESSION['message']['type'] === 'sukses' ? 'bg-success/10' : 'bg-error/10';
    $border_color = $_SESSION['message']['type'] === 'sukses' ? 'border-success' : 'border-error';
    $text_color = $_SESSION['message']['type'] === 'sukses' ? 'text-success' : 'text-error';
    
    echo '<div class="alert ' . $alert_type . ' mb-8 animate-fade-in-up shadow-lg border-l-4 ' . $border_color . ' ' . $bg_color . ' mx-2 sm:mx-0">';
    echo '<div class="flex items-center">';
    echo '<svg class="w-6 h-6 mr-3 ' . $text_color . '" fill="none" viewBox="0 0 24 24" stroke="currentColor">';
    if ($_SESSION['message']['type'] === 'sukses') {
        echo '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />';
    } else {
        echo '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />';
    }
    echo '</svg>';
    echo '<span class="font-medium">' . htmlspecialchars($_SESSION['message']['text']) . '</span>';
    echo '</div>';
    echo '</div>';
    unset($_SESSION['message']); // Hapus pesan setelah ditampilkan
}
?>

<?php if ($all_courses_result && $all_courses_result->num_rows > 0): ?>
    <!-- Enhanced Courses Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6 lg:gap-8 animate-fade-in-up px-2 sm:px-0">
        <?php while ($course = $all_courses_result->fetch_assoc()): ?>
            <div class="card bg-base-100 shadow-2xl hover-lift transform transition-all duration-500 hover:shadow-3xl group border border-base-200">
                <div class="card-body p-6 sm:p-8 relative overflow-hidden">
                    <!-- Decorative Background -->
                    <div class="absolute inset-0 bg-gradient-to-br from-[oklch(42%_0.199_265.638)]/5 to-transparent"></div>
                    <div class="absolute top-0 right-0 w-20 h-20 bg-[oklch(42%_0.199_265.638)]/10 rounded-full blur-2xl group-hover:scale-150 transition-all duration-500"></div>
                    
                    <div class="relative z-10">
                        <!-- Course Icon -->
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-12 h-12 bg-gradient-to-br from-[oklch(42%_0.199_265.638)] to-[oklch(48%_0.211_225.457)] rounded-xl flex items-center justify-center shadow-lg">
                                <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                </svg>
                            </div>
                            <h2 class="card-title text-xl text-[oklch(42%_0.199_265.638)] font-bold flex-1">
                                <?php echo htmlspecialchars($course['nama_praktikum']); ?>
                            </h2>
                        </div>
                        
                        <!-- Description -->
                        <div class="mb-6">
                            <p class="text-base-content/70 text-sm leading-relaxed line-clamp-3">
                                <?php echo nl2br(htmlspecialchars($course['deskripsi'])); ?>
                            </p>
                        </div>
                        
                        <!-- Action Section -->
                        <div class="card-actions justify-end">
                            <?php if (in_array($course['id'], $enrolled_courses_ids)): ?>
                                <div class="w-full">
                                    <div class="badge badge-success badge-lg gap-2 w-full justify-center py-3 shadow-lg">
                                        <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                        <span class="font-semibold !text-white">Telah Terdaftar</span>
                                    </div>
                                </div>
                            <?php else: ?>
                                <form method="POST" action="courses.php" class="w-full">
                                    <input type="hidden" name="praktikum_id" value="<?php echo $course['id']; ?>">
                                    <button type="submit" name="daftar_praktikum" class="btn btn-primary w-full btn-lg group hover:scale-105 transition-all duration-300 shadow-lg">
                                        <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                        </svg>
                                        <span class="font-semibold">Daftar Praktikum</span>
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
<?php else: ?>
    <!-- Enhanced Empty State -->
    <div class="card bg-base-100 shadow-2xl animate-fade-in border border-base-200 mx-2 sm:mx-0">
        <div class="card-body text-center py-12 sm:py-16 relative overflow-hidden">
            <!-- Decorative Background -->
            <div class="absolute inset-0 bg-gradient-to-br from-base-200/30 to-transparent"></div>
            <div class="absolute top-0 right-0 w-32 h-32 bg-[oklch(42%_0.199_265.638)]/5 rounded-full blur-3xl"></div>
            <div class="absolute bottom-0 left-0 w-24 h-24 bg-success/5 rounded-full blur-2xl"></div>
            
            <div class="relative z-10">
                <!-- Enhanced Icon -->
                <div class="flex justify-center mb-6">
                    <div class="w-24 h-24 bg-gradient-to-br from-base-200 to-base-300/50 rounded-3xl flex items-center justify-center shadow-lg animate-bounce-gentle">
                        <div class="text-6xl opacity-60">📚</div>
                    </div>
                </div>
                
                <!-- Typography -->
                <h3 class="text-2xl sm:text-3xl font-bold text-base-content mb-4">Belum Ada Praktikum Tersedia</h3>
                <p class="text-base-content/60 text-lg sm:text-xl leading-relaxed max-w-md mx-auto mb-8">
                    Saat ini belum ada praktikum yang dibuka oleh asisten. Silakan cek kembali nanti atau hubungi administrator.
                </p>
                
                <!-- Action Buttons -->
                <div class="flex flex-col sm:flex-row justify-center gap-4 max-w-md mx-auto">
                    <a href="dashboard.php" class="btn btn-primary">
                        <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5a2 2 0 012-2h4a2 2 0 012 2v3H8V5z" />
                        </svg>
                        <span>Kembali ke Dashboard</span>
                    </a>
                    <a href="my_courses.php" class="btn btn-outline">
                        <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                        <span>Praktikum Saya</span>
                    </a>
                </div>
                
                <!-- Additional Info -->
                <div class="mt-8 p-4 bg-base-200/50 rounded-xl">
                    <p class="text-sm text-base-content/50">
                        💡 Tip: Praktikum baru biasanya dibuka setiap awal semester. Pastikan untuk memeriksa halaman ini secara berkala.
                    </p>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php
// Panggil Footer
require_once 'templates/footer_mahasiswa.php';
?>