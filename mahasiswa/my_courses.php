<?php
$pageTitle = 'Praktikum Saya';
$activePage = 'my_courses'; 

require_once 'templates/header_mahasiswa.php'; 
require_once '../config.php'; 

// Validasi session dan redirect jika perlu
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}
$mahasiswa_id = $_SESSION['user_id'];

// Query untuk mengambil praktikum yang diikuti mahasiswa dengan informasi tambahan
$sql = "SELECT mp.id, mp.nama_praktikum, mp.deskripsi, pp.tanggal_daftar,
               COUNT(m.id) as total_modul,
               COUNT(l.id) as laporan_terkumpul,
               COUNT(CASE WHEN l.nilai IS NOT NULL THEN 1 END) as laporan_dinilai
        FROM mata_praktikum mp
        INNER JOIN pendaftaran_praktikum pp ON mp.id = pp.id_praktikum
        LEFT JOIN modul m ON mp.id = m.id_praktikum
        LEFT JOIN laporan l ON m.id = l.id_modul AND l.id_mahasiswa = ?
        WHERE pp.id_mahasiswa = ?
        GROUP BY mp.id, mp.nama_praktikum, mp.deskripsi, pp.tanggal_daftar
        ORDER BY pp.tanggal_daftar DESC";

$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die("Error preparing the statement: " . $conn->error);
}
$stmt->bind_param("ii", $mahasiswa_id, $mahasiswa_id);
$stmt->execute();
$result = $stmt->get_result();

// Hitung statistik untuk hero section
$stats_sql = "SELECT 
                COUNT(DISTINCT mp.id) as total_praktikum,
                COUNT(DISTINCT l.id) as total_laporan,
                COUNT(DISTINCT CASE WHEN l.nilai IS NOT NULL THEN l.id END) as laporan_dinilai
              FROM mata_praktikum mp
              INNER JOIN pendaftaran_praktikum pp ON mp.id = pp.id_praktikum
              LEFT JOIN modul m ON mp.id = m.id_praktikum
              LEFT JOIN laporan l ON m.id = l.id_modul AND l.id_mahasiswa = ?
              WHERE pp.id_mahasiswa = ?";

$stats_stmt = $conn->prepare($stats_sql);
$stats_stmt->bind_param("ii", $mahasiswa_id, $mahasiswa_id);
$stats_stmt->execute();
$stats = $stats_stmt->get_result()->fetch_assoc();
$stats_stmt->close();
?>

<!-- Main Container with Enhanced Design -->
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
                <h1 class="text-3xl sm:text-4xl md:text-5xl font-extrabold mb-8 pb-2 bg-gradient-to-r from-white via-blue-100 to-white bg-clip-text text-transparent animate-shimmer leading-relaxed
                ">
                    Praktikum Saya
                </h1>
                <p class="text-lg sm:text-xl opacity-90 mb-6 leading-relaxed max-w-3xl mx-auto px-4">
                    Akses semua praktikum yang Anda ikuti dan kelola tugas-tugas Anda dengan mudah
                </p>
                
                <!-- Enhanced Statistics in Hero -->
                <div class="flex justify-center gap-6 flex-wrap mb-6">
                    <div class="text-center">
                        <div class="text-2xl sm:text-3xl font-bold text-white"><?php echo $stats['total_praktikum']; ?></div>
                        <div class="text-xs sm:text-sm text-white/80">Praktikum Aktif</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl sm:text-3xl font-bold text-white"><?php echo $stats['total_laporan']; ?></div>
                        <div class="text-xs sm:text-sm text-white/80">Total Laporan</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl sm:text-3xl font-bold text-white"><?php echo $stats['laporan_dinilai']; ?></div>
                        <div class="text-xs sm:text-sm text-white/80">Sudah Dinilai</div>
                    </div>
                </div>
                
                <!-- Enhanced Badges -->
                <div class="flex justify-center gap-4 flex-wrap">
                    <div class="badge bg-white/20 text-white border-white/30 px-4 py-2 text-sm hover:bg-white/30 transition-all duration-300">
                        <svg class="w-4 h-4 mr-2 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                        <span class="!text-white">Praktikum Aktif</span>
                    </div>
                    <div class="badge bg-emerald-500/30 text-white border-emerald-300/30 px-4 py-2 text-sm hover:bg-emerald-500/40 transition-all duration-300">
                        <svg class="w-4 h-4 mr-2 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="!text-white">Tugas & Laporan</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Content Section -->
    <div class="px-2 sm:px-0">
        <?php if ($result->num_rows > 0): ?>
            <!-- Enhanced Courses Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 animate-fade-in-up">
                <?php while ($course = $result->fetch_assoc()): ?>
                    <!-- Enhanced Course Card -->
                    <div class="card bg-gradient-to-br from-base-100 to-base-200/50 shadow-xl border border-base-300/50 hover-lift hover:shadow-2xl transition-all duration-300 course-card-hover">
                        <div class="card-body p-6">
                            <!-- Card Header -->
                            <div class="flex items-start justify-between mb-4">
                                <div class="p-3 bg-[oklch(42%_0.199_265.638)]/10 rounded-xl">
                                    <svg class="w-6 h-6 text-[oklch(42%_0.199_265.638)]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                    </svg>
                                </div>
                                <div class="badge bg-emerald-500/20 text-white border-emerald-300/30 px-3 py-2">
                                    <svg class="w-3 h-3 mr-1 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span class="text-xs font-medium !text-white">Aktif</span>
                                </div>
                            </div>
                            
                            <!-- Card Content -->
                            <h2 class="text-xl font-bold text-base-content mb-3 line-clamp-2">
                                <?php echo htmlspecialchars($course['nama_praktikum']); ?>
                            </h2>
                            <p class="text-base-content/70 text-sm leading-relaxed mb-4 line-clamp-3">
                                <?php echo htmlspecialchars($course['deskripsi']); ?>
                            </p>
                            
                            <!-- Progress Statistics -->
                            <div class="grid grid-cols-3 gap-3 mb-6">
                                <div class="text-center p-3 bg-blue-50 rounded-lg border border-blue-100">
                                    <div class="text-lg font-bold text-blue-600"><?php echo $course['total_modul']; ?></div>
                                    <div class="text-xs text-blue-500">Modul</div>
                                </div>
                                <div class="text-center p-3 bg-amber-50 rounded-lg border border-amber-100">
                                    <div class="text-lg font-bold text-amber-600"><?php echo $course['laporan_terkumpul']; ?></div>
                                    <div class="text-xs text-amber-500">Terkumpul</div>
                                </div>
                                <div class="text-center p-3 bg-emerald-50 rounded-lg border border-emerald-100">
                                    <div class="text-lg font-bold text-emerald-600"><?php echo $course['laporan_dinilai']; ?></div>
                                    <div class="text-xs text-emerald-500">Dinilai</div>
                                </div>
                            </div>
                            
                            <!-- Progress Bar -->
                            <?php 
                            $progress = $course['total_modul'] > 0 ? ($course['laporan_dinilai'] / $course['total_modul']) * 100 : 0;
                            ?>
                            <div class="mb-4">
                                <div class="flex justify-between text-xs mb-1">
                                    <span class="text-base-content/60">Progress</span>
                                    <span class="text-base-content/60"><?php echo round($progress); ?>%</span>
                                </div>
                                <div class="w-full bg-base-300 rounded-full h-2">
                                    <div class="bg-gradient-to-r from-[oklch(42%_0.199_265.638)] to-[oklch(55%_0.223_136.073)] h-2 rounded-full transition-all duration-300" 
                                         style="width: <?php echo $progress; ?>%"></div>
                                </div>
                            </div>
                            
                            <!-- Registration Date -->
                            <div class="flex items-center gap-2 mb-4 text-xs text-base-content/60">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <span>Terdaftar: <?php echo date('d M Y', strtotime($course['tanggal_daftar'])); ?></span>
                            </div>
                            
                            <!-- Card Actions -->
                            <div class="card-actions justify-end">
                                <a href="course_detail.php?id=<?php echo $course['id']; ?>" 
                                   class="btn bg-[oklch(42%_0.199_265.638)] hover:bg-[oklch(37%_0.199_265.638)] text-white border-none shadow-lg hover:shadow-xl transition-all duration-300 w-full">
                                    <svg class="w-5 h-5 mr-2 !text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    <span class="!text-white">Lihat Detail & Tugas</span>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <!-- Enhanced Empty State -->
            <div class="card bg-gradient-to-br from-base-100 to-base-200/50 shadow-xl border border-base-300/50 animate-fade-in">
                <div class="card-body text-center py-16 px-8">
                    <div class="flex justify-center mb-8">
                        <div class="relative">
                            <div class="w-28 h-28 bg-gradient-to-br from-[oklch(42%_0.199_265.638)]/10 to-[oklch(55%_0.223_136.073)]/10 rounded-3xl flex items-center justify-center animate-pulse-gentle">
                                <svg class="w-16 h-16 text-base-content/40" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                </svg>
                            </div>
                            <!-- Decorative dots -->
                            <div class="absolute -top-2 -right-2 w-4 h-4 bg-[oklch(42%_0.199_265.638)]/20 rounded-full animate-bounce-gentle"></div>
                            <div class="absolute -bottom-2 -left-2 w-3 h-3 bg-[oklch(55%_0.223_136.073)]/20 rounded-full animate-bounce-gentle" style="animation-delay: 0.5s"></div>
                        </div>
                    </div>
                    <h3 class="text-2xl sm:text-3xl font-bold text-base-content mb-4">Belum Ada Praktikum</h3>
                    <p class="text-base-content/60 mb-8 max-w-md mx-auto text-lg leading-relaxed">
                        Anda belum terdaftar di praktikum manapun. Mulailah perjalanan pembelajaran Anda dengan mendaftar praktikum yang tersedia.
                    </p>
                    
                    <!-- Action buttons -->
                    <div class="flex flex-col sm:flex-row gap-4 justify-center">
                        <a href="courses.php" class="btn bg-[oklch(42%_0.199_265.638)] hover:bg-[oklch(37%_0.199_265.638)] text-white border-none shadow-lg hover:shadow-xl transition-all duration-300">
                            <svg class="w-5 h-5 mr-2 !text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            <span class="!text-white">Cari Praktikum Baru</span>
                        </a>
                        <a href="dashboard.php" class="btn btn-ghost border border-base-300 hover:border-[oklch(42%_0.199_265.638)] hover:bg-[oklch(42%_0.199_265.638)]/10 transition-all duration-300">
                            <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z" />
                            </svg>
                            <span>Kembali ke Dashboard</span>
                        </a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
// Cleanup dan Footer
$stmt->close();
$conn->close();
require_once 'templates/footer_mahasiswa.php';
?>