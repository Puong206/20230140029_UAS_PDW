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
$nama_mahasiswa = $_SESSION['nama'] ?? ''; // Menggunakan user_name dari header

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
        'text' => 'Nilai untuk <span class="font-semibold text-[oklch(42%_0.199_265.638)]">'.$result_notif_nilai['judul_modul'].'</span> di praktikum '.$result_notif_nilai['nama_praktikum'].' telah diberikan.'
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
        'text' => 'Anda berhasil mendaftar pada mata praktikum <span class="font-semibold text-[oklch(42%_0.199_265.638)]">'.$result_notif_daftar['nama_praktikum'].'</span>.'
    ];
}
$stmt_notif_daftar->close();

?>

<!-- Main Dashboard Container with Enhanced Padding -->
<div class="max-w-full">
    <!-- Hero Section with Enhanced Design -->
    <div class="relative hero bg-gradient-to-br from-[oklch(42%_0.199_265.638)] via-[oklch(48%_0.211_225.457)] to-[oklch(55%_0.223_136.073)] rounded-3xl shadow-2xl mb-8 animate-fade-in overflow-hidden mx-2 sm:mx-0">
    <!-- Animated Background Pattern -->
    <div class="absolute inset-0 opacity-15">
        <div class="absolute top-0 left-0 w-32 h-32 bg-white rounded-full -translate-x-16 -translate-y-16 blur-xl animate-float"></div>
        <div class="absolute top-10 right-10 w-20 h-20 bg-white rounded-full opacity-50 blur-lg animate-float-delayed"></div>
        <div class="absolute bottom-0 right-0 w-40 h-40 bg-white rounded-full translate-x-20 translate-y-20 blur-2xl animate-pulse-gentle"></div>
        <div class="absolute top-1/3 left-1/4 w-16 h-16 bg-white rounded-full blur-xl animate-bounce-gentle"></div>
    </div>
    
    <!-- Decorative Elements -->
    <div class="absolute top-8 left-8 text-white/20 text-6xl animate-sparkle">✨</div>
    <div class="absolute bottom-8 right-8 text-white/20 text-4xl animate-sparkle" style="animation-delay: 2s">🎯</div>
    
    <div class="hero-content text-center text-white py-16 px-6 sm:py-20 sm:px-8 relative z-10">
        <div class="max-w-4xl mx-auto">
            <!-- Enhanced Avatar Section -->
            <div class="flex justify-center mb-8">
                <div class="relative">
                    <div class="w-28 h-28 rounded-full ring-4 ring-white/30 ring-offset-4 ring-offset-transparent overflow-hidden animate-pulse-gentle shadow-2xl">
                        <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($nama_mahasiswa); ?>&background=ffffff&color=2563eb&size=112&bold=true" alt="Avatar" class="w-full h-full object-cover" />
                    </div>
                    <!-- Status Indicator -->
                    <div class="absolute -bottom-2 -right-2 w-10 h-10 bg-green-400 rounded-full border-4 border-white flex items-center justify-center animate-bounce-gentle">
                        <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <!-- Decorative Ring -->
                    <div class="absolute inset-0 rounded-full border-2 border-white/20 animate-spin-slow"></div>
                </div>
            </div>
            
            <!-- Enhanced Typography -->
            <h1 class="text-4xl sm:text-5xl md:text-6xl lg:text-7xl font-extrabold mb-8 pb-2 bg-gradient-to-r from-white via-blue-100 to-white bg-clip-text text-transparent animate-shimmer leading-relaxed">
                Selamat Datang!
            </h1>
            <h2 class="text-2xl sm:text-3xl md:text-4xl font-bold mb-6 text-white/90 leading-relaxed">
                <?php echo htmlspecialchars($nama_mahasiswa); ?>
            </h2>
            <p class="text-lg sm:text-xl md:text-2xl opacity-90 mb-8 sm:mb-10 leading-relaxed max-w-3xl mx-auto px-4">
                Terus semangat dalam menyelesaikan semua modul praktikummu dan raih prestasi terbaikmu! 🚀
            </p>
            
            <!-- Enhanced Badges -->
            <div class="flex justify-center gap-2 sm:gap-4 flex-wrap mb-6 sm:mb-8 px-4">
                <div class="badge bg-white/20 text-white border-white/30 px-3 sm:px-6 py-2 sm:py-3 text-sm sm:text-lg hover:bg-white/30 transition-all duration-300 animate-fade-in">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5 mr-2 sm:mr-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a.999.999 0 01.356-.257l4-1.714a1 1 0 11.788 1.838L7.667 9.088l1.94.831a1 1 0 00.787 0l7-3a1 1 0 000-1.838l-7-3z"></path>
                    </svg>
                    <span class="!text-white">Panel Mahasiswa</span>
                </div>
                <div class="badge bg-emerald-500/30 text-white border-emerald-300/30 px-3 sm:px-6 py-2 sm:py-3 text-sm sm:text-lg hover:bg-emerald-500/40 transition-all duration-300 animate-fade-in" style="animation-delay: 0.2s">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5 mr-2 sm:mr-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                    <span class="!text-white">Status Aktif</span>
                </div>
                <div class="badge bg-yellow-500/30 text-white border-yellow-300/30 px-3 sm:px-6 py-2 sm:py-3 text-sm sm:text-lg hover:bg-yellow-500/40 transition-all duration-300 animate-fade-in" style="animation-delay: 0.4s">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5 mr-2 sm:mr-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                    </svg>
                    <span class="!text-white"><?php echo date('d M Y'); ?></span>
                </div>
            </div>
            
            <!-- Welcome Action Button -->
            <div class="mt-6 sm:mt-8">
                <button onclick="exploreFeatures()" class="btn btn-lg bg-white/20 text-white border-white/30 hover:bg-white/30 hover:scale-105 transition-all duration-300 px-6 sm:px-8">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6 mr-2 !text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                    </svg>
                    <span class="!text-white">Jelajahi Fitur</span>
                    <svg class="w-4 h-4 sm:w-5 sm:h-5 ml-2 animate-bounce !text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                    </svg>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Enhanced Statistics Cards Row with Better Spacing -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6 lg:gap-8 mb-8 sm:mb-10 animate-fade-in-up px-2 sm:px-0" id="statsSection">
    <!-- Praktikum Diikuti Card -->
    <div class="card bg-base-100 shadow-2xl hover-lift transform transition-all duration-500 hover:shadow-3xl group mx-2 sm:mx-0">
        <div class="card-body items-center text-center relative overflow-hidden p-6 sm:p-8">
            <!-- Decorative Background -->
            <div class="absolute inset-0 bg-gradient-to-br from-[oklch(42%_0.199_265.638)]/5 to-transparent"></div>
            <div class="absolute top-0 right-0 w-24 h-24 bg-[oklch(42%_0.199_265.638)]/10 rounded-full blur-2xl group-hover:scale-150 transition-all duration-500"></div>
            
            <div class="relative z-10">
                <!-- Enhanced Icon -->
                <div class="w-20 h-20 mx-auto mb-6 bg-gradient-to-br from-[oklch(42%_0.199_265.638)] to-[oklch(48%_0.211_225.457)] rounded-2xl flex items-center justify-center shadow-lg animate-bounce-gentle">
                    <svg class="w-10 h-10 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                </div>
                
                <!-- Animated Counter -->
                <div class="text-8xl font-extrabold text-[oklch(42%_0.199_265.638)] mb-4 animate-pulse" data-counter="<?php echo $total_praktikum; ?>">0</div>
                <div class="card-title text-xl text-base-content mb-4 font-bold">Praktikum Diikuti</div>
                
                <!-- Enhanced Badge -->
                <div class="badge badge-primary badge-lg gap-2 mb-4 px-4 py-3">
                    <svg class="w-4 h-4 !text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="!text-white">Aktif</span>
                </div>
                
                <!-- Progress Bar with Animation -->
                <div class="w-full">
                    <div class="flex justify-between text-sm mb-2">
                        <span>Progress</span>
                        <span class="text-[oklch(42%_0.199_265.638)] font-semibold"><?php echo min(100, ($total_praktikum / 10) * 100); ?>%</span>
                    </div>
                    <div class="progress progress-primary w-full h-3 rounded-full" value="<?php echo min(100, ($total_praktikum / 10) * 100); ?>" max="100"></div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Tugas Selesai Card -->
    <div class="card bg-base-100 shadow-2xl hover-lift transform transition-all duration-500 hover:shadow-3xl group mx-2 sm:mx-0">
        <div class="card-body items-center text-center relative overflow-hidden p-6 sm:p-8">
            <!-- Decorative Background -->
            <div class="absolute inset-0 bg-gradient-to-br from-success/5 to-transparent"></div>
            <div class="absolute top-0 right-0 w-24 h-24 bg-success/10 rounded-full blur-2xl group-hover:scale-150 transition-all duration-500"></div>
            
            <div class="relative z-10">
                <!-- Enhanced Icon -->
                <div class="w-20 h-20 mx-auto mb-6 bg-gradient-to-br from-success to-[oklch(60%_0.15_160)] rounded-2xl flex items-center justify-center shadow-lg animate-bounce-gentle" style="animation-delay: 0.2s">
                    <svg class="w-10 h-10 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                
                <!-- Animated Counter -->
                <div class="text-8xl font-extrabold text-success mb-4 animate-pulse" data-counter="<?php echo $tugas_selesai; ?>">0</div>
                <div class="card-title text-xl text-base-content mb-4 font-bold">Tugas Selesai</div>
                
                <!-- Enhanced Badge -->
                <div class="badge badge-success badge-lg gap-2 mb-4 px-4 py-3">
                    <svg class="w-4 h-4 !text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span class="!text-white">Selesai</span>
                </div>
                
                <!-- Progress Bar with Animation -->
                <div class="w-full">
                    <div class="flex justify-between text-sm mb-2">
                        <span>Tingkat Penyelesaian</span>
                        <span class="text-success font-semibold"><?php echo $tugas_selesai > 0 ? min(100, round(($tugas_selesai / ($tugas_selesai + $tugas_menunggu)) * 100)) : 0; ?>%</span>
                    </div>
                    <div class="progress progress-success w-full h-3 rounded-full" value="<?php echo $tugas_selesai > 0 ? min(100, ($tugas_selesai / ($tugas_selesai + $tugas_menunggu)) * 100) : 0; ?>" max="100"></div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Tugas Menunggu Card -->
    <div class="card bg-base-100 shadow-2xl hover-lift transform transition-all duration-500 hover:shadow-3xl group mx-2 sm:mx-0">
        <div class="card-body items-center text-center relative overflow-hidden p-6 sm:p-8">
            <!-- Decorative Background -->
            <div class="absolute inset-0 bg-gradient-to-br from-warning/5 to-transparent"></div>
            <div class="absolute top-0 right-0 w-24 h-24 bg-warning/10 rounded-full blur-2xl group-hover:scale-150 transition-all duration-500"></div>
            
            <div class="relative z-10">
                <!-- Enhanced Icon -->
                <div class="w-20 h-20 mx-auto mb-6 bg-gradient-to-br from-warning to-[oklch(70%_0.12_70)] rounded-2xl flex items-center justify-center shadow-lg animate-bounce-gentle" style="animation-delay: 0.4s">
                    <svg class="w-10 h-10 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                
                <!-- Animated Counter -->
                <div class="text-8xl font-extrabold text-warning mb-4 animate-pulse" data-counter="<?php echo $tugas_menunggu; ?>">0</div>
                <div class="card-title text-xl text-base-content mb-4 font-bold">Tugas Menunggu</div>
                
                <!-- Enhanced Badge -->
                <div class="badge badge-warning badge-lg gap-2 mb-4 px-4 py-3">
                    <svg class="w-4 h-4 !text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="!text-white">Pending</span>
                </div>
                
                <!-- Progress Bar with Animation -->
                <div class="w-full">
                    <div class="flex justify-between text-sm mb-2">
                        <span>Dalam Antrian</span>
                        <span class="text-warning font-semibold"><?php echo $tugas_menunggu > 0 ? min(100, round(($tugas_menunggu / ($tugas_selesai + $tugas_menunggu)) * 100)) : 0; ?>%</span>
                    </div>
                    <div class="progress progress-warning w-full h-3 rounded-full" value="<?php echo $tugas_menunggu > 0 ? min(100, ($tugas_menunggu / ($tugas_selesai + $tugas_menunggu)) * 100) : 0; ?>" max="100"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Enhanced Interactive Charts Section with Better Spacing -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 sm:gap-8 lg:gap-10 mb-8 sm:mb-10 px-2 sm:px-0">
    <!-- Progress Chart with Enhanced Design -->
    <div class="card bg-base-100 shadow-2xl animate-fade-in-up hover:shadow-3xl transition-all duration-500 border border-base-200">
        <div class="card-body p-6 sm:p-8">
            <!-- Enhanced Header -->
            <div class="flex items-center justify-between mb-8">
                <div class="flex items-center gap-4">
                    <div class="p-3 bg-gradient-to-br from-[oklch(42%_0.199_265.638)] to-[oklch(48%_0.211_225.457)] rounded-xl shadow-lg">
                        <svg class="w-7 h-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="card-title text-2xl font-bold text-base-content">Progress Tugas</h3>
                        <p class="text-base-content/60 text-sm">Distribusi status tugas Anda</p>
                    </div>
                </div>
                <div class="badge badge-lg bg-[oklch(42%_0.199_265.638)]/10 text-white border-none">
                    <span class="!text-white">Real-time</span>
                </div>
            </div>
            
            <!-- Chart Container with Enhanced Styling -->
            <div class="relative chart-container flex items-center justify-center bg-gradient-to-br from-base-100 to-base-200/30 rounded-2xl p-4 sm:p-6 shadow-inner">
                <canvas id="progressChart" class="max-w-full max-h-full"></canvas>
                <!-- Loading State -->
                <div id="progressChartLoader" class="absolute inset-0 flex items-center justify-center bg-base-100/80 rounded-2xl">
                    <div class="loading loading-spinner loading-lg text-primary"></div>
                </div>
            </div>
            
            <!-- Chart Statistics -->
            <div class="grid grid-cols-3 gap-4 mt-6">
                <div class="text-center p-3 bg-success/10 rounded-xl">
                    <div class="text-2xl font-bold text-success" data-counter="<?php echo $tugas_selesai; ?>">0</div>
                    <div class="text-xs text-success/80 font-medium">Selesai</div>
                </div>
                <div class="text-center p-3 bg-warning/10 rounded-xl">
                    <div class="text-2xl font-bold text-warning" data-counter="<?php echo $tugas_menunggu; ?>">0</div>
                    <div class="text-xs text-warning/80 font-medium">Menunggu</div>
                </div>
                <div class="text-center p-3 bg-base-300/10 rounded-xl">
                    <div class="text-2xl font-bold text-base-content/60" data-counter="<?php echo max(0, $total_praktikum * 2 - $tugas_selesai - $tugas_menunggu); ?>">0</div>
                    <div class="text-xs text-base-content/40 font-medium">Belum Ada</div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Activity Chart with Enhanced Design -->
    <div class="card bg-base-100 shadow-2xl animate-fade-in-up hover:shadow-3xl transition-all duration-500 border border-base-200" style="animation-delay: 0.2s">
        <div class="card-body p-6 sm:p-8">
            <!-- Enhanced Header -->
            <div class="flex items-center justify-between mb-8">
                <div class="flex items-center gap-4">
                    <div class="p-3 bg-gradient-to-br from-success to-[oklch(60%_0.15_160)] rounded-xl shadow-lg">
                        <svg class="w-7 h-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="card-title text-2xl font-bold text-base-content">Ringkasan Aktivitas</h3>
                        <p class="text-base-content/60 text-sm">Overview keseluruhan aktivitas</p>
                    </div>
                </div>
                <div class="badge badge-lg bg-success/10 text-white border-none">
                    <span class="!text-white">Updated</span>
                </div>
            </div>
            
            <!-- Chart Container with Enhanced Styling -->
            <div class="relative chart-container bg-gradient-to-br from-base-100 to-base-200/30 rounded-2xl p-4 sm:p-6 shadow-inner">
                <canvas id="activityChart" class="w-full h-full"></canvas>
                <!-- Loading State -->
                <div id="activityChartLoader" class="absolute inset-0 flex items-center justify-center bg-base-100/80 rounded-2xl">
                    <div class="loading loading-spinner loading-lg text-success"></div>
                </div>
            </div>
            
            <!-- Activity Summary -->
            <div class="mt-6 p-4 bg-gradient-to-r from-base-200/50 to-transparent rounded-xl">
                <div class="flex justify-between items-center">
                    <span class="text-base-content/60 text-sm">Total Aktivitas</span>
                    <span class="text-2xl font-bold text-base-content" data-counter="<?php echo $total_praktikum + $tugas_selesai + $tugas_menunggu; ?>">0</span>
                </div>
                <div class="progress progress-primary w-full mt-2 h-2" value="85" max="100"></div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions Row with Better Responsive Design -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 sm:gap-6 mb-6 sm:mb-8 px-2 sm:px-0">
    <div class="card bg-gradient-to-br from-[oklch(42%_0.199_265.638)] to-[oklch(48%_0.211_225.457)] text-white shadow-xl hover-lift">
        <div class="card-body items-center text-center p-6">
            <div class="text-2xl sm:text-3xl mb-3">📚</div>
            <h3 class="card-title text-base sm:text-lg mb-3">Praktikum Saya</h3>
            <p class="text-xs sm:text-sm opacity-90 mb-4 px-2">Lihat semua praktikum yang sedang Anda ikuti</p>
            <div class="card-actions">
                <a href="my_courses.php" class="btn btn-ghost btn-sm text-white border-white/30 hover:bg-white/20 hover:text-white">
                    <span class="!text-white">Lihat Semua</span>
                    <svg class="w-4 h-4 ml-1 !text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            </div>
        </div>
    </div>
    
    <div class="card bg-gradient-to-br from-success to-[oklch(60%_0.15_160)] text-white shadow-xl hover-lift">
        <div class="card-body items-center text-center p-6">
            <div class="text-2xl sm:text-3xl mb-3">🎯</div>
            <h3 class="card-title text-base sm:text-lg mb-3">Cari Praktikum</h3>
            <p class="text-xs sm:text-sm opacity-90 mb-4 px-2">Temukan praktikum baru yang menarik</p>
            <div class="card-actions">
                <a href="courses.php" class="btn btn-ghost btn-sm text-white border-white/30 hover:bg-white/20 hover:text-white">
                    <span class="!text-white">Jelajahi</span>
                    <svg class="w-4 h-4 ml-1 !text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </a>
            </div>
        </div>
    </div>
    
    <div class="card bg-gradient-to-br from-warning to-[oklch(70%_0.12_70)] text-white shadow-xl hover-lift">
        <div class="card-body items-center text-center p-6">
            <div class="text-2xl sm:text-3xl mb-3">📊</div>
            <h3 class="card-title text-base sm:text-lg mb-3">Statistik</h3>
            <p class="text-xs sm:text-sm opacity-90 mb-4 px-2">Pantau progress pembelajaran Anda</p>
            <div class="card-actions">
                <button class="btn btn-ghost btn-sm text-white border-white/30 hover:bg-white/20 hover:text-white" onclick="scrollToStats()">
                    Lihat Detail
                    <svg class="w-4 h-4 ml-1 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Activity Section with Enhanced Padding -->
<div class="card bg-base-100 shadow-xl animate-fade-in-up mx-2 sm:mx-0">
    <div class="card-body p-6 sm:p-8">
        <div class="flex items-center gap-3 mb-6">
            <div class="p-2 bg-[oklch(42%_0.199_265.638)]/10 rounded-lg">
                <svg class="w-6 h-6 text-[oklch(42%_0.199_265.638)]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM9 6H4l5-5v5zm5 11V7a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h9a1 1 0 001-1z" />
                </svg>
            </div>
            <h3 class="card-title text-2xl text-base-content">Aktivitas Terbaru</h3>
        </div>
        
        <?php if (!empty($notifikasi)): ?>
        <div class="space-y-3">
            <?php foreach ($notifikasi as $index => $item): ?>
                <div class="alert bg-base-200/50 border-l-4 border-[oklch(42%_0.199_265.638)] animate-slide-in" style="animation-delay: <?php echo $index * 0.1; ?>s">
                    <div class="flex items-start">
                        <span class="text-2xl mr-4 flex-shrink-0"><?php echo $item['icon']; ?></span>
                        <div class="text-base-content leading-relaxed">
                            <?php echo $item['text']; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
            <div class="text-center py-8 sm:py-12 px-4">
                <div class="text-6xl sm:text-8xl mb-4 sm:mb-6 animate-bounce-gentle">📋</div>
                <h4 class="text-lg sm:text-xl font-semibold text-base-content mb-2">Belum Ada Aktivitas</h4>
                <p class="text-base-content/60 text-base sm:text-lg mb-4 sm:mb-6">Tidak ada aktivitas terbaru untuk ditampilkan.</p>
                <div class="flex flex-col sm:flex-row justify-center gap-4">
                    <a href="courses.php" class="btn btn-primary">
                        <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        Cari Praktikum
                    </a>
                    <a href="my_courses.php" class="btn btn-outline">
                        <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                        Praktikum Saya
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- End of Main Dashboard Container -->
</div>


<script>
// Initialize dashboard when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Data from PHP
    const totalPraktikum = <?php echo $total_praktikum; ?>;
    const tugasSelesai = <?php echo $tugas_selesai; ?>;
    const tugasMenunggu = <?php echo $tugas_menunggu; ?>;
    
    // Initialize dashboard charts
    DashboardUtils.initCharts(totalPraktikum, tugasSelesai, tugasMenunggu);
    
    // Initialize dashboard animations
    DashboardUtils.initDashboardAnimations();
});
</script>

<?php
// Selalu tutup koneksi di akhir
$conn->close();
// Panggil Footer
require_once 'templates/footer_mahasiswa.php';
?>