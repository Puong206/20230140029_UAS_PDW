<?php
// 1. Definisi Variabel untuk Template
$pageTitle = 'Laporan Masuk';
$activePage = 'laporan';

// 2. Panggil Header dan koneksi
require_once 'templates/header.php';
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

?>

<div class="bg-white p-6 rounded-xl shadow-lg mb-6">
    <h2 class="text-xl font-bold text-gray-800 mb-4">Filter Laporan</h2>
    <form action="laporan.php" method="GET" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 items-end">
        <div>
            <label for="filter_praktikum" class="block text-sm font-medium text-gray-700">Praktikum</label>
            <select name="filter_praktikum" id="filter_praktikum" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-[#093880] focus:border-[#093880] sm:text-sm rounded-md shadow-sm">
                <option value="">Semua</option>
                <?php while($p = $praktikums->fetch_assoc()): ?><option value="<?php echo $p['id']; ?>" <?php echo ($filter_praktikum_id == $p['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($p['nama_praktikum']); ?></option><?php endwhile; ?>
            </select>
        </div>
        <div>
            <label for="filter_modul" class="block text-sm font-medium text-gray-700">Modul</label>
            <select name="filter_modul" id="filter_modul" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-[#093880] focus:border-[#093880] sm:text-sm rounded-md shadow-sm">
                <option value="">Semua</option>
                 <?php while($m = $moduls->fetch_assoc()): ?><option value="<?php echo $m['id']; ?>" <?php echo ($filter_modul_id == $m['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($m['judul_modul']); ?></option><?php endwhile; ?>
            </select>
        </div>
        <div>
            <label for="filter_mahasiswa" class="block text-sm font-medium text-gray-700">Mahasiswa</label>
            <select name="filter_mahasiswa" id="filter_mahasiswa" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-[#093880] focus:border-[#093880] sm:text-sm rounded-md shadow-sm">
                <option value="">Semua</option>
                <?php while($mhs = $mahasiswas->fetch_assoc()): ?><option value="<?php echo $mhs['id']; ?>" <?php echo ($filter_mahasiswa_id == $mhs['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($mhs['nama']); ?></option><?php endwhile; ?>
            </select>
        </div>
        <div>
            <label for="filter_status" class="block text-sm font-medium text-gray-700">Status</label>
            <select name="filter_status" id="filter_status" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-[#093880] focus:border-[#093880] sm:text-sm rounded-md shadow-sm">
                <option value="">Semua</option>
                <option value="dinilai" <?php echo ($filter_status == 'dinilai') ? 'selected' : ''; ?>>Sudah Dinilai</option>
                <option value="belum_dinilai" <?php echo ($filter_status == 'belum_dinilai') ? 'selected' : ''; ?>>Belum Dinilai</option>
            </select>
        </div>
        <div class="flex space-x-2">
            <button type="submit" class="w-full inline-flex justify-center py-2 px-4 shadow-sm text-sm font-medium rounded-md text-white bg-[#093880] hover:bg-[#072c66] transition-colors">Filter</button>
            <a href="laporan.php" class="w-full inline-flex justify-center py-2 px-4 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-gray-200 hover:bg-gray-300 transition-colors">Reset</a>
        </div>
    </form>
</div>

<?php
if (isset($_SESSION['message'])) {
    $message_type = $_SESSION['message']['type'] === 'sukses' ? 'bg-green-100 border-green-400 text-green-700' : 'bg-red-100 border-red-400 text-red-700';
    echo '<div class="border ' . $message_type . ' px-4 py-3 rounded-lg relative mb-6" role="alert"><span class="block sm:inline">' . htmlspecialchars($_SESSION['message']['text']) . '</span></div>';
    unset($_SESSION['message']);
}
?>

<div class="w-full">
    <table class="w-full text-left" style="border-collapse: separate; border-spacing: 0 .75rem;">
        <thead class="text-xs text-gray-500 uppercase tracking-wider">
            <tr>
                <th class="p-4">Mahasiswa</th>
                <th class="p-4">Detail Laporan</th>
                <th class="p-4">Tanggal Kumpul</th>
                <th class="p-4">Status</th>
                <th class="p-4 text-center">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result_laporan->num_rows > 0): ?>
                <?php while($row = $result_laporan->fetch_assoc()): ?>
                    <tr class="bg-white hover:shadow-xl hover:-translate-y-1 transform transition-all duration-300">
                        <td class="p-4 rounded-l-lg"><?php echo htmlspecialchars($row['nama_mahasiswa']); ?></td>
                        <td class="p-4">
                            <p class="font-semibold text-gray-800"><?php echo htmlspecialchars($row['judul_modul']); ?></p>
                            <p class="text-xs text-gray-500"><?php echo htmlspecialchars($row['nama_praktikum']); ?></p>
                        </td>
                        <td class="p-4"><?php echo date('d M Y, H:i', strtotime($row['tanggal_kumpul'])); ?></td>
                        <td class="p-4">
                            <?php if($row['nilai'] !== null): ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800"><span class="w-2 h-2 mr-1.5 bg-green-500 rounded-full"></span>Sudah Dinilai</span>
                            <?php else: ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800"><span class="w-2 h-2 mr-1.5 bg-yellow-500 rounded-full"></span>Belum Dinilai</span>
                            <?php endif; ?>
                        </td>
                        <td class="p-4 text-center rounded-r-lg">
                            <button onclick="openModal(this)"
                                data-laporan-id="<?php echo $row['laporan_id']; ?>"
                                data-mahasiswa="<?php echo htmlspecialchars($row['nama_mahasiswa']); ?>"
                                data-modul="<?php echo htmlspecialchars($row['judul_modul']); ?>"
                                data-file-laporan="<?php echo htmlspecialchars($row['file_laporan']); ?>"
                                data-nilai="<?php echo htmlspecialchars($row['nilai']); ?>"
                                data-feedback="<?php echo htmlspecialchars($row['feedback']); ?>"
                                class="bg-[#eb8317] hover:bg-[#c87011] text-white font-bold py-2 px-4 rounded-lg transition-colors text-sm">
                                Nilai
                            </button>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" class="text-center py-10 text-gray-500">
                        <div class="p-6 bg-white rounded-lg shadow-md">Tidak ada laporan ditemukan.</div>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div id="nilaiModal" class="fixed inset-0 bg-gray-800 bg-opacity-25 backdrop-blur-sm overflow-y-auto h-full w-full hidden z-50 transition-opacity">
    <div class="relative top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 p-6 border w-full max-w-lg shadow-2xl rounded-2xl bg-white/90">
        <div class="mt-3">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg leading-6 font-bold text-gray-900" id="modalTitle">Beri Nilai Laporan</h3>
                <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600 text-3xl">&times;</button>
            </div>
            <div class="text-sm text-gray-600 mb-4 p-4 bg-gray-50 rounded-lg">
                <p><strong>Mahasiswa:</strong> <span id="modalMahasiswa"></span></p>
                <p><strong>Modul:</strong> <span id="modalModul"></span></p>
                <p><a id="modalDownloadLink" href="#" target="_blank" class="text-blue-600 hover:underline font-semibold">Unduh File Laporan</a></p>
            </div>
            <form action="laporan.php?<?php echo http_build_query($_GET); ?>" method="POST">
                <input type="hidden" name="laporan_id" id="modalLaporanId">
                <div class="mb-4">
                    <label for="nilai" class="block text-sm font-medium text-gray-700">Nilai (0-100)</label>
                    <input type="number" name="nilai" id="modalNilai" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-[#093880] focus:border-[#093880] sm:text-sm" required>
                </div>
                <div>
                    <label for="feedback" class="block text-sm font-medium text-gray-700">Feedback</label>
                    <textarea name="feedback" id="modalFeedback" rows="4" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-[#093880] focus:border-[#093880] sm:text-sm"></textarea>
                </div>
                <div class="mt-6 flex justify-end space-x-3">
                    <button type="button" onclick="closeModal()" class="bg-gray-200 text-gray-800 font-bold py-2 px-4 rounded-lg hover:bg-gray-300 transition-colors">Batal</button>
                    <button type="submit" name="submit_nilai" class="bg-[#093880] text-white font-bold py-2 px-4 rounded-lg hover:bg-[#072c66] transition-colors">Simpan Nilai</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// JavaScript tetap sama, tidak perlu diubah
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
    document.getElementById('nilaiModal').classList.remove('hidden');
}
function closeModal() {
    document.getElementById('nilaiModal').classList.add('hidden');
}
</script>

<?php
// Footer
$stmt_laporan->close();
$conn->close();
require_once 'templates/footer.php';
?>