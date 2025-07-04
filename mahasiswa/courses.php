<?php
$pageTitle = 'Cari Praktikum';
$activePage = 'courses';

// Menggunakan __DIR__ untuk path yang lebih andal
require_once __DIR__ . '/templates/header_mahasiswa.php';
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

<div class="px-4">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-[#0a1f33]">Katalog Praktikum</h1>
        <p class="text-lg text-[#6B7280] mt-1">Daftarkan diri Anda pada praktikum yang tersedia di bawah ini.</p>
    </div>

    <?php
    // Tampilkan pesan notifikasi jika ada
    if (isset($_SESSION['message'])) {
        $message_type = $_SESSION['message']['type'] === 'sukses' ? 'bg-green-100 border-green-400 text-green-700' : 'bg-red-100 border-red-400 text-red-700';
        echo '<div class="border ' . $message_type . ' px-4 py-3 rounded-lg relative mb-6" role="alert">';
        echo '<span class="block sm:inline">' . htmlspecialchars($_SESSION['message']['text']) . '</span>';
        echo '</div>';
        unset($_SESSION['message']); // Hapus pesan setelah ditampilkan
    }
    ?>

    <?php if ($all_courses_result && $all_courses_result->num_rows > 0): ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php while ($course = $all_courses_result->fetch_assoc()): ?>
                <div class="bg-white rounded-xl shadow-lg flex flex-col justify-between">
                    <div class="p-6">
                        <h2 class="text-xl font-bold text-[#093880] mb-2"><?php echo htmlspecialchars($course['nama_praktikum']); ?></h2>
                        <p class="text-[#6B7280] text-sm leading-relaxed">
                            <?php echo nl2br(htmlspecialchars($course['deskripsi'])); ?>
                        </p>
                    </div>
                    <div class="bg-gray-50 p-4 rounded-b-xl">
                        <?php if (in_array($course['id'], $enrolled_courses_ids)): ?>
                            <div class="text-center font-semibold text-green-600 py-2 px-4">
                                ✔ Telah Terdaftar
                            </div>
                        <?php else: ?>
                            <form method="POST" action="courses.php">
                                <input type="hidden" name="praktikum_id" value="<?php echo $course['id']; ?>">
                                <button type="submit" name="daftar_praktikum" class="w-full bg-[#093880] text-white font-bold py-2 px-4 rounded-md hover:bg-opacity-90 transition-colors duration-300">
                                    Daftar Praktikum
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="bg-white text-center p-12 rounded-xl shadow-md">
            <h3 class="mt-2 text-xl font-medium text-[#0a1f33]">Belum Ada Praktikum Tersedia</h3>
            <p class="mt-1 text-base text-[#6B7280]">Saat ini belum ada data praktikum yang dibuka oleh asisten. Silakan cek kembali nanti.</p>
        </div>
    <?php endif; ?>
</div>

<?php
// Panggil Footer
require_once 'templates/footer_mahasiswa.php';
?>