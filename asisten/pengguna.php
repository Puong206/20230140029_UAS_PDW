<?php
// 1. Definisi Variabel
$pageTitle = 'Akun Pengguna';
$activePage = 'pengguna'; // Sesuaikan agar link aktif di sidebar

// 2. Panggil Header & Koneksi
require_once 'templates/header.php';
require_once __DIR__ . '/../config.php';

// 3. Keamanan & Role Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'asisten') {
    header("Location: ../login.php");
    exit();
}
$current_user_id = $_SESSION['user_id'];

// --- LOGIKA CRUD (CREATE, UPDATE, DELETE) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // CREATE ACTION
    if ($action === 'create') {
        $nama = $_POST['nama'];
        $email = $_POST['email'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $role = $_POST['role'];
        
        $stmt = $conn->prepare("INSERT INTO users (nama, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $nama, $email, $password, $role);
        $_SESSION['message'] = $stmt->execute() ? ['type' => 'sukses', 'text' => 'Pengguna baru berhasil ditambahkan.'] : ['type' => 'error', 'text' => 'Gagal menambahkan pengguna (email mungkin sudah terdaftar).'];
        $stmt->close();
    }

    // UPDATE ACTION
    if ($action === 'update') {
        $id = $_POST['id'];
        $nama = $_POST['nama'];
        $email = $_POST['email'];
        $role = $_POST['role'];
        $password = $_POST['password'];

        if (!empty($password)) {
            // Jika password diisi, update semua termasuk password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET nama = ?, email = ?, password = ?, role = ? WHERE id = ?");
            $stmt->bind_param("ssssi", $nama, $email, $hashed_password, $role, $id);
        } else {
            // Jika password kosong, update tanpa mengubah password
            $stmt = $conn->prepare("UPDATE users SET nama = ?, email = ?, role = ? WHERE id = ?");
            $stmt->bind_param("sssi", $nama, $email, $role, $id);
        }
        $_SESSION['message'] = $stmt->execute() ? ['type' => 'sukses', 'text' => 'Data pengguna berhasil diperbarui.'] : ['type' => 'error', 'text' => 'Gagal memperbarui data.'];
        $stmt->close();
    }

    // DELETE ACTION
    if ($action === 'delete') {
        $id = $_POST['id'];
        if ($id != $current_user_id) { // Pencegahan hapus diri sendiri
            $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
            $stmt->bind_param("i", $id);
            $_SESSION['message'] = $stmt->execute() ? ['type' => 'sukses', 'text' => 'Pengguna berhasil dihapus.'] : ['type' => 'error', 'text' => 'Gagal menghapus pengguna.'];
            $stmt->close();
        } else {
            $_SESSION['message'] = ['type' => 'error', 'text' => 'Anda tidak dapat menghapus akun Anda sendiri.'];
        }
    }

    header("Location: pengguna.php");
    exit();
}

// --- Mengambil semua data pengguna untuk ditampilkan ---
$result = $conn->query("SELECT id, nama, email, role FROM users ORDER BY role, nama");

?>

<!-- Tombol Aksi Utama -->
<div class="flex justify-end mb-6">
    <button onclick="openModal('create')" class="flex items-center bg-[#093880] text-white font-bold py-2 px-4 rounded-lg shadow-lg hover:bg-[#072c66] transition-colors">
        <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM4 19.235v-.11a6.375 6.375 0 0112.75 0v.109A12.318 12.318 0 0110.374 21c-2.331 0-4.512-.645-6.374-1.766z" /></svg>
        Tambah Pengguna
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
                <th class="p-4">Nama Pengguna</th>
                <th class="p-4">Email</th>
                <th class="p-4">Role</th>
                <th class="p-4 text-center">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                    <tr class="bg-white hover:shadow-xl hover:-translate-y-1 transform transition-all duration-300">
                        <td class="p-4 rounded-l-lg font-semibold text-gray-800"><?php echo htmlspecialchars($row['nama']); ?></td>
                        <td class="p-4 text-gray-600"><?php echo htmlspecialchars($row['email']); ?></td>
                        <td class="p-4">
                            <?php if($row['role'] == 'asisten'): ?>
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">Asisten</span>
                            <?php else: ?>
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-teal-100 text-teal-800">Mahasiswa</span>
                            <?php endif; ?>
                        </td>
                        <td class="p-4 text-center rounded-r-lg">
                            <div class="flex justify-center space-x-2">
                                <button onclick="openModal('update', this)" 
                                    data-id="<?php echo $row['id']; ?>"
                                    data-nama="<?php echo htmlspecialchars($row['nama']); ?>"
                                    data-email="<?php echo htmlspecialchars($row['email']); ?>"
                                    data-role="<?php echo $row['role']; ?>"
                                    class="p-2 bg-blue-100 text-blue-600 hover:bg-blue-200 rounded-full transition-colors">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" /></svg>
                                </button>
                                <?php if ($row['id'] != $current_user_id): // Tombol hapus non-aktif untuk diri sendiri ?>
                                    <button onclick="openDeleteModal(<?php echo $row['id']; ?>)" class="p-2 bg-red-100 text-red-600 hover:bg-red-200 rounded-full transition-colors">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" /></svg>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="4" class="text-center py-10 text-gray-500"><div class="p-6 bg-white rounded-lg shadow-md">Belum ada data pengguna terdaftar.</div></td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Modal Tambah/Edit -->
<div id="formModal" class="fixed inset-0 bg-gray-800 bg-opacity-25 backdrop-blur-sm overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 p-6 border w-full max-w-lg shadow-2xl rounded-2xl bg-white/90">
        <form id="dataForm" action="pengguna.php" method="POST">
            <input type="hidden" name="action" id="formAction">
            <input type="hidden" name="id" id="formId">
            <div class="flex justify-between items-center mb-4"><h3 id="modalTitle" class="text-lg font-bold text-gray-900"></h3><button type="button" onclick="closeModal('formModal')" class="text-gray-400 hover:text-gray-600 text-3xl">&times;</button></div>
            <div class="space-y-4">
                <div>
                    <label for="nama" class="block text-sm font-medium text-gray-700">Nama Lengkap</label>
                    <input type="text" name="nama" id="formNama" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm" required>
                </div>
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Alamat Email</label>
                    <input type="email" name="email" id="formEmail" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm" required>
                </div>
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">Kata Sandi</label>
                    <input type="password" name="password" id="formPassword" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm">
                    <p class="text-xs text-gray-500 mt-1" id="passwordHelper">Kosongkan jika tidak ingin mengubah kata sandi.</p>
                </div>
                 <div>
                    <label for="role" class="block text-sm font-medium text-gray-700">Role</label>
                    <select name="role" id="formRole" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm" required>
                        <option value="mahasiswa">Mahasiswa</option>
                        <option value="asisten">Asisten</option>
                    </select>
                </div>
            </div>
            <div class="mt-6 flex justify-end space-x-3"><button type="button" onclick="closeModal('formModal')" class="bg-gray-200 text-gray-800 font-bold py-2 px-4 rounded-lg hover:bg-gray-300">Batal</button><button type="submit" class="bg-[#093880] text-white font-bold py-2 px-4 rounded-lg hover:bg-[#072c66]">Simpan</button></div>
        </form>
    </div>
</div>

<!-- Modal Konfirmasi Hapus -->
<div id="deleteModal" class="fixed inset-0 bg-gray-800 bg-opacity-25 backdrop-blur-sm hidden z-50">
    <div class="relative top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 p-6 border w-full max-w-md shadow-2xl rounded-2xl bg-white/90">
        <form action="pengguna.php" method="POST">
            <input type="hidden" name="action" value="delete"><input type="hidden" name="id" id="deleteId">
            <div class="text-center">
                <svg class="mx-auto mb-4 text-red-500 w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                <h3 class="mb-5 text-lg font-normal text-gray-600">Anda yakin ingin menghapus pengguna ini?</h3>
                <p class="text-xs text-gray-500 mb-5">Semua data terkait (laporan, pendaftaran) akan ikut terhapus.</p>
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
    const passwordInput = document.getElementById('formPassword');
    const passwordHelper = document.getElementById('passwordHelper');
    const roleSelect = document.getElementById('formRole');
    
    form.reset();
    document.getElementById('formAction').value = action;

    if (action === 'create') {
        title.textContent = 'Tambah Pengguna Baru';
        document.getElementById('formId').value = '';
        passwordInput.required = true;
        passwordHelper.style.display = 'none';
        roleSelect.disabled = false;
    } else if (action === 'update') {
        title.textContent = 'Edit Data Pengguna';
        const id = button.dataset.id;
        document.getElementById('formId').value = id;
        document.getElementById('formNama').value = button.dataset.nama;
        document.getElementById('formEmail').value = button.dataset.email;
        roleSelect.value = button.dataset.role;
        passwordInput.required = false;
        passwordHelper.style.display = 'block';

        // Mencegah asisten mengubah role diri sendiri
        if (id == <?php echo $current_user_id; ?>) {
            roleSelect.disabled = true;
        } else {
            roleSelect.disabled = false;
        }
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