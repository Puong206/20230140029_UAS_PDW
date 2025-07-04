<?php
// 1. Definisi Variabel untuk Template
$pageTitle = 'Mata Praktikum';
$activePage = 'praktikum'; // Sesuaikan dengan nama file agar link aktif di sidebar

// 2. Panggil koneksi TERLEBIH DAHULU sebelum header
require_once __DIR__ . '/../config.php';



// Pastikan yang login adalah asisten
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'asisten') {
    header("Location: ../login.php");
    exit();
}

// --- LOGIKA CRUD (CREATE, UPDATE, DELETE) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // CREATE ACTION
    if ($action === 'create') {
        $nama = $_POST['nama_praktikum'];
        $deskripsi = $_POST['deskripsi'];
        $stmt = $conn->prepare("INSERT INTO mata_praktikum (nama_praktikum, deskripsi) VALUES (?, ?)");
        $stmt->bind_param("ss", $nama, $deskripsi);
        if ($stmt->execute()) {
            $_SESSION['message'] = ['type' => 'sukses', 'text' => 'Mata praktikum berhasil ditambahkan.'];
        } else {
            $_SESSION['message'] = ['type' => 'error', 'text' => 'Gagal menambahkan data.'];
        }
        $stmt->close();
    }

    // UPDATE ACTION
    if ($action === 'update') {
        $id = $_POST['id'];
        $nama = $_POST['nama_praktikum'];
        $deskripsi = $_POST['deskripsi'];
        $stmt = $conn->prepare("UPDATE mata_praktikum SET nama_praktikum = ?, deskripsi = ? WHERE id = ?");
        $stmt->bind_param("ssi", $nama, $deskripsi, $id);
        if ($stmt->execute()) {
            $_SESSION['message'] = ['type' => 'sukses', 'text' => 'Data berhasil diperbarui.'];
        } else {
            $_SESSION['message'] = ['type' => 'error', 'text' => 'Gagal memperbarui data.'];
        }
        $stmt->close();
    }

    // DELETE ACTION
    if ($action === 'delete') {
        $id = $_POST['id'];
        // PENTING: Hapus dulu relasi di tabel lain untuk menghindari error foreign key
        $conn->query("DELETE FROM pendaftaran_praktikum WHERE id_praktikum = $id");
        $conn->query("DELETE FROM modul WHERE id_praktikum = $id");
        
        $stmt = $conn->prepare("DELETE FROM mata_praktikum WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $_SESSION['message'] = ['type' => 'sukses', 'text' => 'Data berhasil dihapus.'];
        } else {
            $_SESSION['message'] = ['type' => 'error', 'text' => 'Gagal menghapus data.'];
        }
        $stmt->close();
    }

    header("Location: praktikum.php");
    exit();
}

// --- Mengambil semua data praktikum untuk ditampilkan ---
$result = $conn->query("SELECT * FROM mata_praktikum ORDER BY created_at DESC");

// 3. Panggil Header SETELAH semua logika POST selesai
require_once 'templates/header.php';

?>

<!-- Tombol Aksi Utama -->
<div class="flex justify-end mb-6">
    <button onclick="openModal('create')" class="btn btn-primary">
        <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
        </svg>
        Tambah Mata Praktikum
    </button>
</div>

<!-- Notifikasi -->
<?php
if (isset($_SESSION['message'])) {
    $alert_class = $_SESSION['message']['type'] === 'sukses' ? 'alert-success' : 'alert-error';
    echo '<div class="alert ' . $alert_class . ' mb-6"><span>' . htmlspecialchars($_SESSION['message']['text']) . '</span></div>';
    unset($_SESSION['message']);
}
?>

<!-- Tabel Data -->
<div class="card bg-base-100 shadow-xl">
    <div class="card-body">
        <h2 class="card-title text-base-content" style="color: oklch(100% 0 0);">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/>
            </svg>
            Daftar Mata Praktikum
        </h2>
        <div class="overflow-x-auto">
            <table class="table table-zebra">
                <thead>
                    <tr>
                        <th>Nama Praktikum</th>
                        <th>Deskripsi</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                        <tr class="hover">
                            <td>
                                <div class="font-bold"><?php echo htmlspecialchars($row['nama_praktikum']); ?></div>
                            </td>
                            <td>
                                <span class="text-sm opacity-75"><?php echo nl2br(htmlspecialchars($row['deskripsi'])); ?></span>
                            </td>
                            <td>
                                <div class="flex gap-2">
                                    <button onclick="openModal('update', this)" 
                                        data-id="<?php echo $row['id']; ?>"
                                        data-nama="<?php echo htmlspecialchars($row['nama_praktikum']); ?>"
                                        data-deskripsi="<?php echo htmlspecialchars($row['deskripsi']); ?>"
                                        class="btn btn-sm btn-primary">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                        </svg>
                                        Edit
                                    </button>
                                    <button onclick="openDeleteModal(<?php echo $row['id']; ?>)" class="btn btn-sm btn-error">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                        </svg>
                                        Hapus
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3">
                                <div class="text-center p-8">
                                    <svg class="w-16 h-16 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="color: oklch(64.8% 0.223 136.073);">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75c0-.231-.035-.454-.1-.664M6.75 7.5h1.5M6.75 12h1.5m6.75 0h1.5m-1.5 3h1.5m-1.5 3h1.5M4.5 6.75h1.5v1.5H4.5v-1.5zM4.5 12h1.5v1.5H4.5v-1.5zM4.5 17.25h1.5v1.5H4.5v-1.5z"/>
                                    </svg>
                                    <div class="text-base-content/70">
                                        <p class="font-semibold">Belum ada mata praktikum!</p>
                                        <p class="text-sm">Tambah mata praktikum pertama untuk memulai.</p>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tambah/Edit -->
<dialog id="formModal" class="modal">
    <div class="modal-box">
        <form id="dataForm" action="praktikum.php" method="POST">
            <input type="hidden" name="action" id="formAction">
            <input type="hidden" name="id" id="formId">
            
            <h3 id="modalTitle" class="font-bold text-lg mb-4"></h3>
            
            <div class="space-y-4">
                <div class="form-control">
                    <label class="label" for="nama_praktikum">
                        <span class="label-text">Nama Mata Praktikum</span>
                    </label>
                    <input type="text" name="nama_praktikum" id="formNama" class="input input-bordered" required>
                </div>
                
                <div class="form-control">
                    <label class="label" for="deskripsi">
                        <span class="label-text">Deskripsi</span>
                    </label>
                    <textarea name="deskripsi" id="formDeskripsi" rows="4" class="textarea textarea-bordered"></textarea>
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

<!-- Modal Konfirmasi Hapus -->
<dialog id="deleteModal" class="modal">
    <div class="modal-box text-center">
        <form action="praktikum.php" method="POST">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" id="deleteId">
            
            <svg class="mx-auto mb-4 text-error w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
            </svg>
            
            <h3 class="text-lg font-bold mb-4">Konfirmasi Hapus</h3>
            <p class="mb-2">Anda yakin ingin menghapus data ini?</p>
            <p class="text-sm opacity-70 mb-6">Semua modul dan data pendaftaran terkait akan ikut terhapus.</p>
            
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
function openModal(action, button = null) {
    const modal = document.getElementById('formModal');
    const form = document.getElementById('dataForm');
    const title = document.getElementById('modalTitle');
    
    // Reset form
    form.reset();
    document.getElementById('formAction').value = action;

    if (action === 'create') {
        title.textContent = 'Tambah Mata Praktikum Baru';
        document.getElementById('formId').value = '';
    } else if (action === 'update') {
        title.textContent = 'Edit Mata Praktikum';
        const id = button.dataset.id;
        const nama = button.dataset.nama;
        const deskripsi = button.dataset.deskripsi;
        
        document.getElementById('formId').value = id;
        document.getElementById('formNama').value = nama;
        document.getElementById('formDeskripsi').value = deskripsi;
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