<?php
// 1. Definisi Variabel
$pageTitle = 'Akun Pengguna';
$activePage = 'pengguna'; // Sesuaikan agar link aktif di sidebar

// 2. Panggil koneksi TERLEBIH DAHULU sebelum header
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

// 4. Panggil Header SETELAH semua logika POST selesai
require_once 'templates/header.php';

?>

<!-- Tombol Aksi Utama -->
<div class="flex justify-end mb-6">
    <button onclick="openModal('create')" class="btn btn-primary">
        <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM4 19.235v-.11a6.375 6.375 0 0112.75 0v.109A12.318 12.318 0 0110.374 21c-2.331 0-4.512-.645-6.374-1.766z" />
        </svg>
        Tambah Pengguna
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
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-4.67c.12-.241.252-.477.396-.702a4.125 4.125 0 013.472-2.132c.225 0 .445.03.655.084m-6.374 0c-1.132 0-2.186-.223-3.131-.628m-6.374 0c-1.132 0-2.186-.223-3.131-.628m18.536 0c-1.132 0-2.186-.223-3.131-.628m-6.374 0c-1.132 0-2.186-.223-3.131-.628"/>
            </svg>
            Daftar Pengguna
        </h2>
        <div class="overflow-x-auto">
            <table class="table table-zebra">
                <thead>
                    <tr>
                        <th>Nama Pengguna</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                        <tr class="hover">
                            <td>
                                <div class="flex items-center gap-3">
                                    <div class="avatar placeholder">
                                        <div class="bg-neutral text-neutral-content rounded-full w-8">
                                            <span class="text-xs"><?php echo strtoupper(substr($row['nama'], 0, 2)); ?></span>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="font-bold"><?php echo htmlspecialchars($row['nama']); ?></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="text-sm opacity-75"><?php echo htmlspecialchars($row['email']); ?></span>
                            </td>
                            <td>
                                <?php if($row['role'] == 'asisten'): ?>
                                    <span class="badge badge-info badge-sm">Asisten</span>
                                <?php else: ?>
                                    <span class="badge badge-success badge-sm">Mahasiswa</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="flex gap-2">
                                    <button onclick="openModal('update', this)" 
                                        data-id="<?php echo $row['id']; ?>"
                                        data-nama="<?php echo htmlspecialchars($row['nama']); ?>"
                                        data-email="<?php echo htmlspecialchars($row['email']); ?>"
                                        data-role="<?php echo $row['role']; ?>"
                                        class="btn btn-sm btn-primary">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                        </svg>
                                        Edit
                                    </button>
                                    <?php if ($row['id'] != $current_user_id): ?>
                                        <button onclick="openDeleteModal(<?php echo $row['id']; ?>)" class="btn btn-sm btn-error">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/>
                                            </svg>
                                            Hapus
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4">
                                <div class="text-center p-8">
                                    <svg class="w-16 h-16 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="color: oklch(64.8% 0.223 136.073);">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-4.67c.12-.241.252-.477.396-.702a4.125 4.125 0 013.472-2.132c.225 0 .445.03.655.084m-6.374 0c-1.132 0-2.186-.223-3.131-.628m-6.374 0c-1.132 0-2.186-.223-3.131-.628m18.536 0c-1.132 0-2.186-.223-3.131-.628m-6.374 0c-1.132 0-2.186-.223-3.131-.628"/>
                                    </svg>
                                    <div class="text-base-content/70">
                                        <p class="font-semibold">Belum ada pengguna terdaftar!</p>
                                        <p class="text-sm">Tambah pengguna pertama untuk memulai.</p>
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
        <form id="dataForm" action="pengguna.php" method="POST">
            <input type="hidden" name="action" id="formAction">
            <input type="hidden" name="id" id="formId">
            
            <h3 id="modalTitle" class="font-bold text-lg mb-4"></h3>
            
            <div class="space-y-4">
                <div class="form-control">
                    <label class="label" for="nama">
                        <span class="label-text">Nama Lengkap</span>
                    </label>
                    <input type="text" name="nama" id="formNama" class="input input-bordered" required>
                </div>
                
                <div class="form-control">
                    <label class="label" for="email">
                        <span class="label-text">Alamat Email</span>
                    </label>
                    <input type="email" name="email" id="formEmail" class="input input-bordered" required>
                </div>
                
                <div class="form-control">
                    <label class="label" for="password">
                        <span class="label-text">Kata Sandi</span>
                    </label>
                    <input type="password" name="password" id="formPassword" class="input input-bordered">
                    <label class="label">
                        <span class="label-text-alt" id="passwordHelper">Kosongkan jika tidak ingin mengubah kata sandi.</span>
                    </label>
                </div>
                
                <div class="form-control">
                    <label class="label">
                        <span class="label-text">Role</span>
                    </label>
                    <div class="dropdown dropdown-hover w-full">
                        <div tabindex="0" role="button" class="btn w-full justify-between" id="roleDropdownBtn">
                            <span id="roleDropdownText">Pilih Role...</span>
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                        <ul tabindex="0" class="dropdown-content menu bg-base-100 rounded-box z-[1] w-full p-2 shadow-sm">
                            <li>
                                <a href="#" onclick="selectRole('mahasiswa', 'Mahasiswa')" class="role-option" data-value="mahasiswa">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z M12 14l-9-5 9 5z" />
                                    </svg>
                                    Mahasiswa
                                </a>
                            </li>
                            <li>
                                <a href="#" onclick="selectRole('asisten', 'Asisten')" class="role-option" data-value="asisten">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                    </svg>
                                    Asisten
                                </a>
                            </li>
                        </ul>
                    </div>
                    <input type="hidden" name="role" id="formRole" required>
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
        <form action="pengguna.php" method="POST">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" id="deleteId">
            
            <svg class="mx-auto mb-4 text-error w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
            </svg>
            
            <h3 class="text-lg font-bold mb-4">Konfirmasi Hapus</h3>
            <p class="mb-2">Anda yakin ingin menghapus pengguna ini?</p>
            <p class="text-sm opacity-70 mb-6">Semua data terkait (laporan, pendaftaran) akan ikut terhapus.</p>
            
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
function selectRole(value, text) {
    document.getElementById('formRole').value = value;
    document.getElementById('roleDropdownText').textContent = text;
    
    // Update active state
    document.querySelectorAll('.role-option').forEach(option => {
        option.classList.remove('bg-primary', 'text-white');
    });
    document.querySelector(`[data-value="${value}"]`).classList.add('bg-primary', 'text-white');
}

function openModal(action, button = null) {
    const modal = document.getElementById('formModal');
    const form = document.getElementById('dataForm');
    const title = document.getElementById('modalTitle');
    const passwordInput = document.getElementById('formPassword');
    const passwordHelper = document.getElementById('passwordHelper');
    const roleDropdown = document.getElementById('roleDropdownBtn');
    
    form.reset();
    document.getElementById('formAction').value = action;

    if (action === 'create') {
        title.textContent = 'Tambah Pengguna Baru';
        document.getElementById('formId').value = '';
        passwordInput.required = true;
        passwordHelper.style.display = 'none';
        roleDropdown.style.pointerEvents = 'auto';
        roleDropdown.style.opacity = '1';
        
        // Reset dropdown to default
        document.getElementById('roleDropdownText').textContent = 'Pilih Role...';
        document.getElementById('formRole').value = '';
        document.querySelectorAll('.role-option').forEach(option => {
            option.classList.remove('bg-primary', 'text-white');
        });
    } else if (action === 'update') {
        title.textContent = 'Edit Data Pengguna';
        const id = button.dataset.id;
        document.getElementById('formId').value = id;
        document.getElementById('formNama').value = button.dataset.nama;
        document.getElementById('formEmail').value = button.dataset.email;
        passwordInput.required = false;
        passwordHelper.style.display = 'block';

        // Set role dropdown
        const role = button.dataset.role;
        const roleText = role === 'asisten' ? 'Asisten' : 'Mahasiswa';
        selectRole(role, roleText);

        // Mencegah asisten mengubah role diri sendiri
        if (id == <?php echo $current_user_id; ?>) {
            roleDropdown.style.pointerEvents = 'none';
            roleDropdown.style.opacity = '0.5';
        } else {
            roleDropdown.style.pointerEvents = 'auto';
            roleDropdown.style.opacity = '1';
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