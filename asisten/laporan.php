<?php
// 1. Definisi Variabel untuk Template
$pageTitle = 'Laporan Masuk';
$activePage = 'laporan';

// 2. Panggil koneksi TERLEBIH DAHULU sebelum header
require_once __DIR__ . '/../config.php';

// Pastikan yang login adalah asisten
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'asisten') {
    header("Location: ../login.php");
    exit();
}

// --- PROSES SUBMIT NILAI (METHOD POST) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_nilai'])) {
    $laporan_id = $_POST['laporan_id'];
    $nilai = $_POST['nilai'];
    $feedback = $_POST['feedback'];

    $updateSql = "UPDATE laporan SET nilai = ?, feedback = ?, tanggal_nilai = NOW() WHERE id = ?";
    $stmt = $conn->prepare($updateSql);
    $stmt->bind_param("isi", $nilai, $feedback, $laporan_id);

    if ($stmt->execute()) {
        $_SESSION['message'] = ['type' => 'sukses', 'text' => 'Nilai berhasil disimpan.'];
    } else {
        $_SESSION['message'] = ['type' => 'error', 'text' => 'Gagal menyimpan nilai.'];
    }
    $stmt->close();
    header("Location: laporan.php?" . http_build_query($_GET));
    exit();
}

// --- LOGIKA FILTER (METHOD GET) ---
$filter_praktikum_id = $_GET['filter_praktikum'] ?? null;
$filter_modul_id = $_GET['filter_modul'] ?? null;
$filter_mahasiswa_id = $_GET['filter_mahasiswa'] ?? null;
$filter_status = $_GET['filter_status'] ?? null;

$sql = "SELECT l.id as laporan_id, l.tanggal_kumpul, l.nilai, l.file_laporan, l.feedback,
               u.nama as nama_mahasiswa,
               m.id as modul_id, m.judul_modul,
               mp.id as praktikum_id, mp.nama_praktikum
        FROM laporan l
        JOIN users u ON l.id_mahasiswa = u.id
        JOIN modul m ON l.id_modul = m.id
        JOIN mata_praktikum mp ON m.id_praktikum = mp.id";

$where_clauses = [];
$params = [];
$types = "";

if (!empty($filter_modul_id)) {
    $where_clauses[] = "m.id = ?";
    $params[] = $filter_modul_id;
    $types .= "i";
} elseif (!empty($filter_praktikum_id)) {
    $where_clauses[] = "mp.id = ?";
    $params[] = $filter_praktikum_id;
    $types .= "i";
}
if (!empty($filter_mahasiswa_id)) {
    $where_clauses[] = "u.id = ?";
    $params[] = $filter_mahasiswa_id;
    $types .= "i";
}
if (!empty($filter_status)) {
    if ($filter_status == 'dinilai') $where_clauses[] = "l.nilai IS NOT NULL";
    elseif ($filter_status == 'belum_dinilai') $where_clauses[] = "l.nilai IS NULL";
}

if (!empty($where_clauses)) {
    $sql .= " WHERE " . implode(" AND ", $where_clauses);
}

$sql .= " ORDER BY l.tanggal_kumpul DESC";

$stmt_laporan = $conn->prepare($sql);
if (!empty($params)) {
    $stmt_laporan->bind_param($types, ...$params);
}
$stmt_laporan->execute();
$result_laporan = $stmt_laporan->get_result();

// --- AMBIL DATA UNTUK DROPDOWN FILTER ---
$praktikums = $conn->query("SELECT id, nama_praktikum FROM mata_praktikum ORDER BY nama_praktikum");
$moduls = $conn->query("SELECT id, judul_modul FROM modul ORDER BY judul_modul");
$mahasiswas = $conn->query("SELECT id, nama FROM users WHERE role = 'mahasiswa' ORDER BY nama");

// 3. Panggil Header SETELAH semua logika POST selesai
require_once 'templates/header.php';

?>

<div class="card bg-base-100 shadow-lg mb-6">
    <div class="card-body">
        <h2 class="card-title mb-4">Filter Laporan</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 items-end">
            <!-- Filter Praktikum -->
            <div class="dropdown dropdown-hover">
                <div tabindex="0" role="button" class="btn w-full justify-between">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                        <span>
                            <?php 
                            if (!empty($filter_praktikum_id)) {
                                $selected_praktikum = $conn->query("SELECT nama_praktikum FROM mata_praktikum WHERE id = $filter_praktikum_id")->fetch_assoc();
                                echo htmlspecialchars($selected_praktikum['nama_praktikum']);
                            } else {
                                echo 'Praktikum';
                            }
                            ?>
                        </span>
                    </div>
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </div>
                <ul tabindex="0" class="dropdown-content menu bg-base-100 rounded-box z-[1] w-64 p-2 shadow-sm">
                    <li>
                        <a href="laporan.php?<?php echo http_build_query(array_merge($_GET, ['filter_praktikum' => ''])); ?>" class="<?php echo empty($filter_praktikum_id) ? 'bg-primary text-white' : ''; ?>">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                            </svg>
                            Semua Praktikum
                        </a>
                    </li>
                    <?php 
                    $praktikums->data_seek(0);
                    while($p = $praktikums->fetch_assoc()): 
                    ?>
                        <li>
                            <a href="laporan.php?<?php echo http_build_query(array_merge($_GET, ['filter_praktikum' => $p['id']])); ?>" class="<?php echo ($filter_praktikum_id == $p['id']) ? 'bg-primary text-white' : ''; ?>">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                </svg>
                                <?php echo htmlspecialchars($p['nama_praktikum']); ?>
                            </a>
                        </li>
                    <?php endwhile; ?>
                </ul>
            </div>

            <!-- Filter Modul -->
            <div class="dropdown dropdown-hover">
                <div tabindex="0" role="button" class="btn w-full justify-between">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <span>
                            <?php 
                            if (!empty($filter_modul_id)) {
                                $selected_modul = $conn->query("SELECT judul_modul FROM modul WHERE id = $filter_modul_id")->fetch_assoc();
                                echo htmlspecialchars($selected_modul['judul_modul']);
                            } else {
                                echo 'Modul';
                            }
                            ?>
                        </span>
                    </div>
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </div>
                <ul tabindex="0" class="dropdown-content menu bg-base-100 rounded-box z-[1] w-64 p-2 shadow-sm">
                    <li>
                        <a href="laporan.php?<?php echo http_build_query(array_merge($_GET, ['filter_modul' => ''])); ?>" class="<?php echo empty($filter_modul_id) ? 'bg-primary text-white' : ''; ?>">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                            </svg>
                            Semua Modul
                        </a>
                    </li>
                    <?php 
                    $moduls->data_seek(0);
                    while($m = $moduls->fetch_assoc()): 
                    ?>
                        <li>
                            <a href="laporan.php?<?php echo http_build_query(array_merge($_GET, ['filter_modul' => $m['id']])); ?>" class="<?php echo ($filter_modul_id == $m['id']) ? 'bg-primary text-white' : ''; ?>">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <?php echo htmlspecialchars($m['judul_modul']); ?>
                            </a>
                        </li>
                    <?php endwhile; ?>
                </ul>
            </div>

            <!-- Filter Mahasiswa -->
            <div class="dropdown dropdown-hover">
                <div tabindex="0" role="button" class="btn w-full justify-between">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        <span>
                            <?php 
                            if (!empty($filter_mahasiswa_id)) {
                                $selected_mahasiswa = $conn->query("SELECT nama FROM users WHERE id = $filter_mahasiswa_id")->fetch_assoc();
                                echo htmlspecialchars($selected_mahasiswa['nama']);
                            } else {
                                echo 'Mahasiswa';
                            }
                            ?>
                        </span>
                    </div>
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </div>
                <ul tabindex="0" class="dropdown-content menu bg-base-100 rounded-box z-[1] w-64 p-2 shadow-sm">
                    <li>
                        <a href="laporan.php?<?php echo http_build_query(array_merge($_GET, ['filter_mahasiswa' => ''])); ?>" class="<?php echo empty($filter_mahasiswa_id) ? 'bg-primary text-white' : ''; ?>">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                            </svg>
                            Semua Mahasiswa
                        </a>
                    </li>
                    <?php 
                    $mahasiswas->data_seek(0);
                    while($mhs = $mahasiswas->fetch_assoc()): 
                    ?>
                        <li>
                            <a href="laporan.php?<?php echo http_build_query(array_merge($_GET, ['filter_mahasiswa' => $mhs['id']])); ?>" class="<?php echo ($filter_mahasiswa_id == $mhs['id']) ? 'bg-primary text-white' : ''; ?>">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                <?php echo htmlspecialchars($mhs['nama']); ?>
                            </a>
                        </li>
                    <?php endwhile; ?>
                </ul>
            </div>

            <!-- Filter Status -->
            <div class="dropdown dropdown-hover">
                <div tabindex="0" role="button" class="btn w-full justify-between">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>
                            <?php 
                            if ($filter_status == 'dinilai') {
                                echo 'Sudah Dinilai';
                            } elseif ($filter_status == 'belum_dinilai') {
                                echo 'Belum Dinilai';
                            } else {
                                echo 'Status';
                            }
                            ?>
                        </span>
                    </div>
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </div>
                <ul tabindex="0" class="dropdown-content menu bg-base-100 rounded-box z-[1] w-64 p-2 shadow-sm">
                    <li>
                        <a href="laporan.php?<?php echo http_build_query(array_merge($_GET, ['filter_status' => ''])); ?>" class="<?php echo empty($filter_status) ? 'bg-primary text-white' : ''; ?>">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                            </svg>
                            Semua Status
                        </a>
                    </li>
                    <li>
                        <a href="laporan.php?<?php echo http_build_query(array_merge($_GET, ['filter_status' => 'dinilai'])); ?>" class="<?php echo ($filter_status == 'dinilai') ? 'bg-primary text-white' : ''; ?>">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Sudah Dinilai
                        </a>
                    </li>
                    <li>
                        <a href="laporan.php?<?php echo http_build_query(array_merge($_GET, ['filter_status' => 'belum_dinilai'])); ?>" class="<?php echo ($filter_status == 'belum_dinilai') ? 'bg-primary text-white' : ''; ?>">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Belum Dinilai
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Reset Button -->
            <div class="flex justify-center">
                <?php if (!empty($filter_praktikum_id) || !empty($filter_modul_id) || !empty($filter_mahasiswa_id) || !empty($filter_status)): ?>
                    <a href="laporan.php" class="btn btn-ghost w-full">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        Reset Filter
                    </a>
                <?php else: ?>
                    <div class="btn btn-ghost w-full opacity-50 cursor-not-allowed">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        Reset Filter
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
if (isset($_SESSION['message'])) {
    $alert_class = $_SESSION['message']['type'] === 'sukses' ? 'alert-success' : 'alert-error';
    echo '<div class="alert ' . $alert_class . ' mb-6"><span>' . htmlspecialchars($_SESSION['message']['text']) . '</span></div>';
    unset($_SESSION['message']);
}
?>

<div class="card bg-base-100 shadow-lg">
    <div class="overflow-x-auto">
        <table class="table table-zebra">
            <thead>
                <tr>
                    <th>Mahasiswa</th>
                    <th>Detail Laporan</th>
                    <th>Tanggal Kumpul</th>
                    <th>Status</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result_laporan->num_rows > 0): ?>
                    <?php while($row = $result_laporan->fetch_assoc()): ?>
                        <tr class="hover">
                            <td>
                                <div class="font-semibold"><?php echo htmlspecialchars($row['nama_mahasiswa']); ?></div>
                            </td>
                            <td>
                                <div class="font-semibold"><?php echo htmlspecialchars($row['judul_modul']); ?></div>
                                <div class="text-sm opacity-70"><?php echo htmlspecialchars($row['nama_praktikum']); ?></div>
                            </td>
                            <td><?php echo date('d M Y, H:i', strtotime($row['tanggal_kumpul'])); ?></td>
                            <td>
                                <?php if($row['nilai'] !== null): ?>
                                    <div class="badge badge-success gap-2">
                                        <div class="w-2 h-2 bg-success-content rounded-full"></div>
                                        Sudah Dinilai
                                    </div>
                                <?php else: ?>
                                    <div class="badge badge-warning gap-2">
                                        <div class="w-2 h-2 bg-warning-content rounded-full"></div>
                                        Belum Dinilai
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <button onclick="openModal(this)"
                                    data-laporan-id="<?php echo $row['laporan_id']; ?>"
                                    data-mahasiswa="<?php echo htmlspecialchars($row['nama_mahasiswa']); ?>"
                                    data-modul="<?php echo htmlspecialchars($row['judul_modul']); ?>"
                                    data-file-laporan="<?php echo htmlspecialchars($row['file_laporan']); ?>"
                                    data-nilai="<?php echo htmlspecialchars($row['nilai']); ?>"
                                    data-feedback="<?php echo htmlspecialchars($row['feedback']); ?>"
                                    class="btn btn-sm btn-primary">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    Nilai
                                </button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center py-12">
                            <div class="flex flex-col items-center justify-center">
                                <svg class="w-16 h-16 text-accent mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <h3 class="text-lg font-semibold mb-2">Belum ada laporan</h3>
                                <p class="opacity-70 mb-4">Tidak ada laporan ditemukan. Coba ubah filter atau tunggu mahasiswa mengumpulkan laporan.</p>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<dialog id="nilaiModal" class="modal">
    <div class="modal-box">
        <h3 class="font-bold text-lg mb-4" id="modalTitle">Beri Nilai Laporan</h3>
        
        <div class="bg-base-200 p-4 rounded-lg mb-4">
            <div class="space-y-2">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    <span class="font-semibold">Mahasiswa:</span>
                    <span id="modalMahasiswa"></span>
                </div>
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                    <span class="font-semibold">Modul:</span>
                    <span id="modalModul"></span>
                </div>
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <a id="modalDownloadLink" href="#" target="_blank" class="link link-primary font-semibold">Unduh File Laporan</a>
                </div>
            </div>
        </div>
        
        <form action="laporan.php?<?php echo http_build_query($_GET); ?>" method="POST">
            <input type="hidden" name="laporan_id" id="modalLaporanId">
            
            <div class="form-control mb-4">
                <label class="label" for="nilai">
                    <span class="label-text">Nilai (0-100)</span>
                </label>
                <input type="number" name="nilai" id="modalNilai" class="input input-bordered" min="0" max="100" required>
            </div>
            
            <div class="form-control mb-6">
                <label class="label" for="feedback">
                    <span class="label-text">Feedback</span>
                </label>
                <textarea name="feedback" id="modalFeedback" rows="4" class="textarea textarea-bordered" placeholder="Berikan feedback untuk mahasiswa..."></textarea>
            </div>
            
            <div class="modal-action">
                <button type="button" onclick="closeModal()" class="btn btn-ghost">Batal</button>
                <button type="submit" name="submit_nilai" class="btn btn-primary">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Simpan Nilai
                </button>
            </div>
        </form>
    </div>
    <form method="dialog" class="modal-backdrop">
        <button onclick="closeModal()">close</button>
    </form>
</dialog>

<script>
function openModal(button) {
    document.getElementById('modalLaporanId').value = button.dataset.laporanId;
    document.getElementById('modalMahasiswa').textContent = button.dataset.mahasiswa;
    document.getElementById('modalModul').textContent = button.dataset.modul;
    document.getElementById('modalDownloadLink').href = `../uploads/laporan/${button.dataset.fileLaporan}`; 
    document.getElementById('modalNilai').value = button.dataset.nilai;
    document.getElementById('modalFeedback').value = button.dataset.feedback;

    if(button.dataset.nilai) {
        document.getElementById('modalTitle').textContent = 'Edit Nilai Laporan';
    } else {
        document.getElementById('modalTitle').textContent = 'Beri Nilai Laporan';
    }
    document.getElementById('nilaiModal').showModal();
}
function closeModal() {
    document.getElementById('nilaiModal').close();
}
</script>

<?php
// Footer
$stmt_laporan->close();
$conn->close();
require_once 'templates/footer.php';
?>