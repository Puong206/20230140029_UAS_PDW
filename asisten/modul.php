<?php
// 1. Definisi Variabel
$pageTitle = 'Modul';
$activePage = 'modul';

// 2. Panggil koneksi TERLEBIH DAHULU sebelum header
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
$sql = "SELECT m.id, m.id_praktikum, m.judul_modul, m.deskripsi_modul, m.file_materi, mp.nama_praktikum 
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

// 4. Panggil Header SETELAH semua logika POST selesai
require_once 'templates/header.php';
?>

<div class="flex flex-col sm:flex-row justify-between items-center mb-6 gap-4">
    <div class="flex items-center gap-2">
        <div class="dropdown dropdown-hover">
            <div tabindex="0" role="button" class="btn">
                <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                </svg>
                <?php 
                if (!empty($filter_praktikum_id)) {
                    $selected_praktikum = $conn->query("SELECT nama_praktikum FROM mata_praktikum WHERE id = $filter_praktikum_id")->fetch_assoc();
                    echo htmlspecialchars($selected_praktikum['nama_praktikum']);
                } else {
                    echo 'Filter Praktikum';
                }
                ?>
                <svg class="w-4 h-4 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </div>
            <ul tabindex="0" class="dropdown-content menu bg-base-100 rounded-box z-[1] w-64 p-2 shadow-sm">
                <li>
                    <a href="modul.php" class="<?php echo empty($filter_praktikum_id) ? 'bg-primary text-white' : ''; ?>">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                        </svg>
                        Semua Praktikum
                    </a>
                </li>
                <?php 
                $praktikums_for_filter->data_seek(0);
                while($p = $praktikums_for_filter->fetch_assoc()): 
                ?>
                    <li>
                        <a href="modul.php?filter_praktikum=<?php echo $p['id']; ?>" class="<?php echo ($filter_praktikum_id == $p['id']) ? 'bg-primary text-white' : ''; ?>">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                            <?php echo htmlspecialchars($p['nama_praktikum']); ?>
                        </a>
                    </li>
                <?php endwhile; ?>
            </ul>
        </div>
        <?php if (!empty($filter_praktikum_id)): ?>
            <a href="modul.php" class="btn btn-ghost btn-sm">
                <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
                Reset
            </a>
        <?php endif; ?>
    </div>
    <button onclick="openModal('create')" class="btn btn-primary">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
        Tambah Modul
    </button>
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
                    <th>Judul Modul</th>
                    <th>Mata Praktikum</th>
                    <th>File Materi</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <tr class="hover">
                            <td>
                                <div class="font-semibold"><?php echo htmlspecialchars($row['judul_modul']); ?></div>
                                <div class="text-sm opacity-70"><?php echo htmlspecialchars($row['deskripsi_modul']); ?></div>
                            </td>
                            <td><?php echo htmlspecialchars($row['nama_praktikum']); ?></td>
                            <td>
                                <?php if($row['file_materi']): ?>
                                    <a href="../uploads/materi/<?php echo htmlspecialchars($row['file_materi']); ?>" target="_blank" class="link link-primary">Lihat File</a>
                                <?php else: ?>
                                    <span class="text-sm opacity-50">Tidak ada</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <div class="flex justify-center gap-2">
                                    <button onclick="openModal('update', this)" 
                                        data-id="<?php echo $row['id']; ?>"
                                        data-id-praktikum="<?php echo $row['id_praktikum']; ?>"
                                        data-judul="<?php echo htmlspecialchars($row['judul_modul']); ?>"
                                        data-deskripsi="<?php echo htmlspecialchars($row['deskripsi_modul']); ?>"
                                        data-file="<?php echo htmlspecialchars($row['file_materi']); ?>"
                                        class="btn btn-sm btn-circle btn-primary">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" /></svg>
                                    </button>
                                    <button onclick="openDeleteModal(<?php echo $row['id']; ?>)" class="btn btn-sm btn-circle btn-error">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" /></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="text-center py-12">
                            <div class="flex flex-col items-center justify-center">
                                <svg class="w-16 h-16 text-accent mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <h3 class="text-lg font-semibold mb-2">Belum ada modul</h3>
                                <p class="opacity-70 mb-4">Data modul tidak ditemukan. Coba reset filter atau tambah modul baru.</p>
                                <button onclick="openModal('create')" class="btn btn-primary btn-sm">Tambah Modul</button>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<dialog id="formModal" class="modal">
    <div class="modal-box">
        <form id="dataForm" action="modul.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" id="formAction">
            <input type="hidden" name="id" id="formId">
            <input type="hidden" name="old_file_materi" id="formOldFile">
            
            <h3 id="modalTitle" class="font-bold text-lg mb-4"></h3>
            
            <div class="space-y-4">
                <div class="form-control">
                    <label class="label">
                        <span class="label-text">Mata Praktikum</span>
                    </label>
                    <div class="dropdown dropdown-hover w-full">
                        <div tabindex="0" role="button" class="btn w-full justify-between" id="praktikumDropdownBtn">
                            <span id="praktikumDropdownText">Pilih Praktikum...</span>
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                        <ul tabindex="0" class="dropdown-content menu bg-base-100 rounded-box z-[1] w-full p-2 shadow-sm max-h-60 overflow-y-auto">
                            <?php 
                            // Reset pointer untuk modal
                            $praktikums_for_modal->data_seek(0);
                            while($p = $praktikums_for_modal->fetch_assoc()): 
                            ?>
                                <li>
                                    <a href="#" onclick="selectPraktikum('<?php echo $p['id']; ?>', '<?php echo htmlspecialchars($p['nama_praktikum']); ?>')" class="praktikum-option" data-value="<?php echo $p['id']; ?>">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                        </svg>
                                        <?php echo htmlspecialchars($p['nama_praktikum']); ?>
                                    </a>
                                </li>
                            <?php endwhile; ?>
                        </ul>
                    </div>
                    <input type="hidden" name="id_praktikum" id="formPraktikum" required>
                </div>
                
                <div class="form-control">
                    <label class="label" for="judul_modul">
                        <span class="label-text">Judul Modul</span>
                    </label>
                    <input type="text" name="judul_modul" id="formJudul" class="input input-bordered" required>
                </div>
                
                <div class="form-control">
                    <label class="label" for="deskripsi_modul">
                        <span class="label-text">Deskripsi</span>
                    </label>
                    <textarea name="deskripsi_modul" id="formDeskripsi" rows="3" class="textarea textarea-bordered"></textarea>
                </div>
                
                <div class="form-control">
                    <label class="label" for="file_materi">
                        <span class="label-text">File Materi (PDF, DOCX)</span>
                    </label>
                    <input type="file" name="file_materi" id="formFile" class="file-input file-input-bordered">
                    <label class="label">
                        <span class="label-text-alt">Kosongkan jika tidak ingin mengubah file yang ada.</span>
                    </label>
                </div>
            </div>
            
            <div class="modal-action">
                <button type="button" onclick="closeModal('formModal')" class="btn btn-ghost">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
    <form method="dialog" class="modal-backdrop">
        <button onclick="closeModal('formModal')">close</button>
    </form>
</dialog>

<dialog id="deleteModal" class="modal">
    <div class="modal-box text-center">
        <form action="modul.php" method="POST">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" id="deleteId">
            
            <svg class="mx-auto mb-4 text-error w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
            </svg>
            
            <h3 class="text-lg font-bold mb-4">Konfirmasi Hapus</h3>
            <p class="mb-6 opacity-70">Anda yakin ingin menghapus modul ini? Tindakan ini tidak dapat dibatalkan.</p>
            
            <div class="modal-action justify-center">
                <button type="button" onclick="closeModal('deleteModal')" class="btn btn-ghost">Batal</button>
                <button type="submit" class="btn btn-error">Ya, Hapus</button>
            </div>
        </form>
    </div>
    <form method="dialog" class="modal-backdrop">
        <button onclick="closeModal('deleteModal')">close</button>
    </form>
</dialog>

<script>
function selectPraktikum(value, text) {
    document.getElementById('formPraktikum').value = value;
    document.getElementById('praktikumDropdownText').textContent = text;
    
    // Update active state
    document.querySelectorAll('.praktikum-option').forEach(option => {
        option.classList.remove('bg-primary', 'text-white');
    });
    document.querySelector(`[data-value="${value}"]`).classList.add('bg-primary', 'text-white');
}

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
        
        // Reset dropdown to default
        document.getElementById('praktikumDropdownText').textContent = 'Pilih Praktikum...';
        document.getElementById('formPraktikum').value = '';
        document.querySelectorAll('.praktikum-option').forEach(option => {
            option.classList.remove('bg-primary', 'text-white');
        });
    } else if (action === 'update') {
        title.textContent = 'Edit Modul';
        document.getElementById('formId').value = button.dataset.id;
        document.getElementById('formJudul').value = button.dataset.judul;
        document.getElementById('formDeskripsi').value = button.dataset.deskripsi;
        document.getElementById('formOldFile').value = button.dataset.file;
        
        // Set praktikum dropdown
        const praktikumId = button.dataset.idPraktikum;
        const praktikumOption = document.querySelector(`[data-value="${praktikumId}"]`);
        if (praktikumOption) {
            const praktikumText = praktikumOption.textContent.trim();
            selectPraktikum(praktikumId, praktikumText);
        }
    }
    modal.showModal();
}

function openDeleteModal(id) {
    document.getElementById('deleteId').value = id;
    document.getElementById('deleteModal').showModal();
}

function closeModal(modalId) {
    document.getElementById(modalId).close();
}
</script>

<?php
// 3. Panggil Footer
$conn->close();
require_once 'templates/footer.php';
?>