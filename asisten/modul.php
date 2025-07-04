<?php
// 1. Definisi Variabel
$pageTitle = 'Modul';
$activePage = 'modul';

// 2. Panggil Header & Koneksi
require_once 'templates/header.php';
require_once __DIR__ . '/../config.php';

// 3. Keamanan & Role Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'asisten') {
    header("Location: ../login.php");
    exit();
}

// --- LOGIKA CRUD (CREATE, UPDATE, DELETE) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $upload_dir = __DIR__ . '/../uploads/materi/';

    // Fungsi untuk mengelola upload file
    function handle_upload($file_input_name, $existing_file = '') {
        global $upload_dir;
        if (isset($_FILES[$file_input_name]) && $_FILES[$file_input_name]['error'] == 0) {
            // Hapus file lama jika ada
            if ($existing_file && file_exists($upload_dir . $existing_file)) {
                unlink($upload_dir . $existing_file);
            }
            // Proses file baru
            $file_name = uniqid() . '-' . basename($_FILES[$file_input_name]['name']);
            move_uploaded_file($_FILES[$file_input_name]['tmp_name'], $upload_dir . $file_name);
            return $file_name;
        }
        return $existing_file; // Kembalikan file lama jika tidak ada upload baru
    }

    // CREATE ACTION
    if ($action === 'create') {
        $file_materi = handle_upload('file_materi');
        $stmt = $conn->prepare("INSERT INTO modul (id_praktikum, judul_modul, deskripsi_modul, file_materi) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $_POST['id_praktikum'], $_POST['judul_modul'], $_POST['deskripsi_modul'], $file_materi);
        $_SESSION['message'] = $stmt->execute() ? ['type' => 'sukses', 'text' => 'Modul berhasil ditambahkan.'] : ['type' => 'error', 'text' => 'Gagal menambahkan modul.'];
        $stmt->close();
    }

    // UPDATE ACTION
    if ($action === 'update') {
        $file_materi = handle_upload('file_materi', $_POST['old_file_materi']);
        $stmt = $conn->prepare("UPDATE modul SET id_praktikum = ?, judul_modul = ?, deskripsi_modul = ?, file_materi = ? WHERE id = ?");
        $stmt->bind_param("isssi", $_POST['id_praktikum'], $_POST['judul_modul'], $_POST['deskripsi_modul'], $file_materi, $_POST['id']);
        $_SESSION['message'] = $stmt->execute() ? ['type' => 'sukses', 'text' => 'Modul berhasil diperbarui.'] : ['type' => 'error', 'text' => 'Gagal memperbarui modul.'];
        $stmt->close();
    }

    // DELETE ACTION
    if ($action === 'delete') {
        // Ambil nama file sebelum menghapus record dari DB
        $stmt_get_file = $conn->prepare("SELECT file_materi FROM modul WHERE id = ?");
        $stmt_get_file->bind_param("i", $_POST['id']);
        $stmt_get_file->execute();
        $file_to_delete = $stmt_get_file->get_result()->fetch_assoc()['file_materi'];
        $stmt_get_file->close();
        
        $stmt = $conn->prepare("DELETE FROM modul WHERE id = ?");
        $stmt->bind_param("i", $_POST['id']);
        if ($stmt->execute()) {
            if ($file_to_delete && file_exists($upload_dir . $file_to_delete)) {
                unlink($upload_dir . $file_to_delete);
            }
            $_SESSION['message'] = ['type' => 'sukses', 'text' => 'Modul berhasil dihapus.'];
        } else {
            $_SESSION['message'] = ['type' => 'error', 'text' => 'Gagal menghapus modul.'];
        }
        $stmt->close();
    }
    
    header("Location: modul.php");
    exit();
}

// --- Mengambil Data untuk Tampilan & Filter ---
$filter_praktikum_id = $_GET['filter_praktikum'] ?? '';
$sql = "SELECT m.id, m.judul_modul, m.deskripsi_modul, m.file_materi, mp.nama_praktikum 
        FROM modul m 
        JOIN mata_praktikum mp ON m.id_praktikum = mp.id";

if (!empty($filter_praktikum_id)) {
    $sql .= " WHERE m.id_praktikum = ?";
}
$sql .= " ORDER BY mp.nama_praktikum, m.id";

$stmt_modul = $conn->prepare($sql);
if (!empty($filter_praktikum_id)) {
    $stmt_modul->bind_param("i", $filter_praktikum_id);
}
$stmt_modul->execute();
$result = $stmt_modul->get_result();

$praktikums_for_filter = $conn->query("SELECT id, nama_praktikum FROM mata_praktikum ORDER BY nama_praktikum");
$praktikums_for_modal = $conn->query("SELECT id, nama_praktikum FROM mata_praktikum ORDER BY nama_praktikum");
?>

<div class="flex flex-col sm:flex-row justify-between items-center mb-6 gap-4">
    <form action="modul.php" method="GET" class="flex items-center gap-2">
        <select name="filter_praktikum" onchange="this.form.submit()" class="block w-full sm:w-64 pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-[#093880] focus:border-[#093880] rounded-md shadow-sm">
            <option value="">Filter Semua Praktikum</option>
            <?php while($p = $praktikums_for_filter->fetch_assoc()): ?>
                <option value="<?php echo $p['id']; ?>" <?php echo ($filter_praktikum_id == $p['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($p['nama_praktikum']); ?></option>
            <?php endwhile; ?>
        </select>
        <a href="modul.php" class="text-sm text-gray-600 hover:underline">Reset</a>
    </form>
    <button onclick="openModal('create')" class="w-full sm:w-auto flex items-center bg-[#093880] text-white font-bold py-2 px-4 rounded-lg shadow-lg hover:bg-[#072c66] transition-colors">
        <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
        Tambah Modul
    </button>
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
                <th class="p-4">Judul Modul</th>
                <th class="p-4">Mata Praktikum</th>
                <th class="p-4">File Materi</th>
                <th class="p-4 text-center">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                    <tr class="bg-white hover:shadow-xl hover:-translate-y-1 transform transition-all duration-300">
                        <td class="p-4 rounded-l-lg">
                            <p class="font-semibold text-gray-800"><?php echo htmlspecialchars($row['judul_modul']); ?></p>
                            <p class="text-xs text-gray-500"><?php echo htmlspecialchars($row['deskripsi_modul']); ?></p>
                        </td>
                        <td class="p-4 text-gray-600"><?php echo htmlspecialchars($row['nama_praktikum']); ?></td>
                        <td class="p-4">
                            <?php if($row['file_materi']): ?>
                                <a href="../uploads/materi/<?php echo htmlspecialchars($row['file_materi']); ?>" target="_blank" class="text-blue-600 hover:underline text-sm">Lihat File</a>
                            <?php else: ?>
                                <span class="text-sm text-gray-400">Tidak ada</span>
                            <?php endif; ?>
                        </td>
                        <td class="p-4 text-center rounded-r-lg">
                            <div class="flex justify-center space-x-2">
                                <button onclick="openModal('update', this)" 
                                    data-id="<?php echo $row['id']; ?>"
                                    data-id-praktikum="<?php echo $row['id_praktikum']; ?>"
                                    data-judul="<?php echo htmlspecialchars($row['judul_modul']); ?>"
                                    data-deskripsi="<?php echo htmlspecialchars($row['deskripsi_modul']); ?>"
                                    data-file="<?php echo htmlspecialchars($row['file_materi']); ?>"
                                    class="p-2 bg-blue-100 text-blue-600 hover:bg-blue-200 rounded-full transition-colors">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" /></svg>
                                </button>
                                <button onclick="openDeleteModal(<?php echo $row['id']; ?>)" class="p-2 bg-red-100 text-red-600 hover:bg-red-200 rounded-full transition-colors">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" /></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="4" class="text-center py-10 text-gray-500"><div class="p-6 bg-white rounded-lg shadow-md">Data modul tidak ditemukan. Coba reset filter.</div></td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div id="formModal" class="fixed inset-0 bg-gray-800 bg-opacity-25 backdrop-blur-sm overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 p-6 border w-full max-w-lg shadow-2xl rounded-2xl bg-white/90">
        <form id="dataForm" action="modul.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" id="formAction">
            <input type="hidden" name="id" id="formId">
            <input type="hidden" name="old_file_materi" id="formOldFile">
            <div class="flex justify-between items-center mb-4"><h3 id="modalTitle" class="text-lg font-bold text-gray-900"></h3><button type="button" onclick="closeModal('formModal')" class="text-gray-400 hover:text-gray-600 text-3xl">&times;</button></div>
            <div class="space-y-4">
                <div>
                    <label for="id_praktikum" class="block text-sm font-medium text-gray-700">Mata Praktikum</label>
                    <select name="id_praktikum" id="formPraktikum" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm" required>
                        <option value="">Pilih Praktikum...</option>
                        <?php while($p = $praktikums_for_modal->fetch_assoc()): ?><option value="<?php echo $p['id']; ?>"><?php echo htmlspecialchars($p['nama_praktikum']); ?></option><?php endwhile; ?>
                    </select>
                </div>
                <div>
                    <label for="judul_modul" class="block text-sm font-medium text-gray-700">Judul Modul</label>
                    <input type="text" name="judul_modul" id="formJudul" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm" required>
                </div>
                <div>
                    <label for="deskripsi_modul" class="block text-sm font-medium text-gray-700">Deskripsi</label>
                    <textarea name="deskripsi_modul" id="formDeskripsi" rows="3" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm"></textarea>
                </div>
                <div>
                    <label for="file_materi" class="block text-sm font-medium text-gray-700">File Materi (PDF, DOCX)</label>
                    <input type="file" name="file_materi" id="formFile" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    <p class="text-xs text-gray-500 mt-1">Kosongkan jika tidak ingin mengubah file yang ada.</p>
                </div>
            </div>
            <div class="mt-6 flex justify-end space-x-3"><button type="button" onclick="closeModal('formModal')" class="bg-gray-200 text-gray-800 font-bold py-2 px-4 rounded-lg hover:bg-gray-300">Batal</button><button type="submit" class="bg-[#093880] text-white font-bold py-2 px-4 rounded-lg hover:bg-[#072c66]">Simpan</button></div>
        </form>
    </div>
</div>

<div id="deleteModal" class="fixed inset-0 bg-gray-800 bg-opacity-25 backdrop-blur-sm hidden z-50">
    <div class="relative top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 p-6 border w-full max-w-md shadow-2xl rounded-2xl bg-white/90">
        <form action="modul.php" method="POST">
            <input type="hidden" name="action" value="delete"><input type="hidden" name="id" id="deleteId">
            <div class="text-center">
                <svg class="mx-auto mb-4 text-red-500 w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                <h3 class="mb-5 text-lg font-normal text-gray-600">Anda yakin ingin menghapus modul ini?</h3>
                <button type="submit" class="text-white bg-red-600 hover:bg-red-800 font-medium rounded-lg text-sm inline-flex items-center px-5 py-2.5 text-center mr-2">Ya, Hapus</button>
                <button type="button" onclick="closeModal('deleteModal')" class="text-gray-500 bg-white hover:bg-gray-100 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900">Batal</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal(action, button = null) {
    const modal = document.getElementById('formModal');
    const form = document.getElementById('dataForm');
    const title = document.getElementById('modalTitle');
    form.reset();
    document.getElementById('formAction').value = action;
    
    if (action === 'create') {
        title.textContent = 'Tambah Modul Baru';
        document.getElementById('formId').value = '';
        document.getElementById('formOldFile').value = '';
    } else if (action === 'update') {
        title.textContent = 'Edit Modul';
        document.getElementById('formId').value = button.dataset.id;
        document.getElementById('formPraktikum').value = button.dataset.idPraktikum;
        document.getElementById('formJudul').value = button.dataset.judul;
        document.getElementById('formDeskripsi').value = button.dataset.deskripsi;
        document.getElementById('formOldFile').value = button.dataset.file;
    }
    modal.classList.remove('hidden');
}

function openDeleteModal(id) {
    document.getElementById('deleteId').value = id;
    document.getElementById('deleteModal').classList.remove('hidden');
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.add('hidden');
}
</script>

<?php
// 3. Panggil Footer
$conn->close();
require_once 'templates/footer.php';
?>