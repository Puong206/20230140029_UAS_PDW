<?php
// 1. Definisi Variabel untuk Template
$pageTitle = 'Mata Praktikum';
$activePage = 'praktikum'; // Sesuaikan dengan nama file agar link aktif di sidebar

// 2. Panggil Header dan koneksi
require_once 'templates/header.php';
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

?>

<!-- Tombol Aksi Utama -->
<div class="flex justify-end mb-6">
    <button onclick="openModal('create')" class="flex items-center bg-[#093880] text-white font-bold py-2 px-4 rounded-lg shadow-lg hover:bg-[#072c66] transition-colors">
        <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
        Tambah Mata Praktikum
    </button>
</div>

<!-- Notifikasi -->
<?php
if (isset($_SESSION['message'])) {
    $message_type = $_SESSION['message']['type'] === 'sukses' ? 'bg-green-100 border-green-400 text-green-700' : 'bg-red-100 border-red-400 text-red-700';
    echo '<div class="border ' . $message_type . ' px-4 py-3 rounded-lg relative mb-6" role="alert"><span class="block sm:inline">' . htmlspecialchars($_SESSION['message']['text']) . '</span></div>';
    unset($_SESSION['message']);
}
?>

<!-- Tabel Data -->
<div class="w-full">
    <table class="w-full text-left" style="border-collapse: separate; border-spacing: 0 .75rem;">
        <thead class="text-xs text-gray-500 uppercase tracking-wider">
            <tr>
                <th class="p-4">Nama Praktikum</th>
                <th class="p-4">Deskripsi</th>
                <th class="p-4 text-center">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                    <tr class="bg-white hover:shadow-xl hover:-translate-y-1 transform transition-all duration-300">
                        <td class="p-4 rounded-l-lg font-semibold text-gray-800"><?php echo htmlspecialchars($row['nama_praktikum']); ?></td>
                        <td class="p-4 text-gray-600"><?php echo nl2br(htmlspecialchars($row['deskripsi'])); ?></td>
                        <td class="p-4 text-center rounded-r-lg">
                            <div class="flex justify-center space-x-2">
                                <button onclick="openModal('update', this)" 
                                    data-id="<?php echo $row['id']; ?>"
                                    data-nama="<?php echo htmlspecialchars($row['nama_praktikum']); ?>"
                                    data-deskripsi="<?php echo htmlspecialchars($row['deskripsi']); ?>"
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
                <tr><td colspan="3" class="text-center py-10 text-gray-500"><div class="p-6 bg-white rounded-lg shadow-md">Belum ada data mata praktikum.</div></td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Modal untuk Tambah/Edit Data -->
<div id="formModal" class="fixed inset-0 bg-gray-800 bg-opacity-25 backdrop-blur-sm overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 p-6 border w-full max-w-lg shadow-2xl rounded-2xl bg-white/90">
        <form id="dataForm" action="praktikum.php" method="POST">
            <input type="hidden" name="action" id="formAction">
            <input type="hidden" name="id" id="formId">
            <div class="flex justify-between items-center mb-4">
                <h3 id="modalTitle" class="text-lg leading-6 font-bold text-gray-900"></h3>
                <button type="button" onclick="closeModal('formModal')" class="text-gray-400 hover:text-gray-600 text-3xl">&times;</button>
            </div>
            <div class="space-y-4">
                <div>
                    <label for="nama_praktikum" class="block text-sm font-medium text-gray-700">Nama Mata Praktikum</label>
                    <input type="text" name="nama_praktikum" id="formNama" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-[#093880] focus:border-[#093880]" required>
                </div>
                <div>
                    <label for="deskripsi" class="block text-sm font-medium text-gray-700">Deskripsi</label>
                    <textarea name="deskripsi" id="formDeskripsi" rows="4" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-[#093880] focus:border-[#093880]"></textarea>
                </div>
            </div>
            <div class="mt-6 flex justify-end space-x-3">
                <button type="button" onclick="closeModal('formModal')" class="bg-gray-200 text-gray-800 font-bold py-2 px-4 rounded-lg hover:bg-gray-300 transition-colors">Batal</button>
                <button type="submit" class="bg-[#093880] text-white font-bold py-2 px-4 rounded-lg hover:bg-[#072c66] transition-colors">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal untuk Konfirmasi Hapus -->
<div id="deleteModal" class="fixed inset-0 bg-gray-800 bg-opacity-25 backdrop-blur-sm overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 p-6 border w-full max-w-md shadow-2xl rounded-2xl bg-white/90">
        <form action="praktikum.php" method="POST">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" id="deleteId">
            <div class="text-center">
                <svg class="mx-auto mb-4 text-red-500 w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                <h3 class="mb-5 text-lg font-normal text-gray-600">Anda yakin ingin menghapus data ini?</h3>
                <p class="text-xs text-gray-500 mb-5">Semua modul dan data pendaftaran terkait akan ikut terhapus.</p>
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