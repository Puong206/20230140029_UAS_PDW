<?php
require_once 'config.php';

$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama = trim($_POST['nama']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $role = trim($_POST['role']);

    // Validasi sederhana
    if (empty($nama) || empty($email) || empty($password) || empty($role)) {
        $message = "Semua field harus diisi!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Format email tidak valid!";
    } elseif (!in_array($role, ['mahasiswa', 'asisten'])) {
        $message = "Peran tidak valid!";
    } else {
        // Cek apakah email sudah terdaftar
        $sql = "SELECT id FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $message = "Email sudah terdaftar. Silakan gunakan email lain.";
        } else {
            // Hash password untuk keamanan
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);

            // Simpan ke database
            $sql_insert = "INSERT INTO users (nama, email, password, role) VALUES (?, ?, ?, ?)";
            $stmt_insert = $conn->prepare($sql_insert);
            $stmt_insert->bind_param("ssss", $nama, $email, $hashed_password, $role);

            if ($stmt_insert->execute()) {
                header("Location: login.php?status=registered");
                exit();
            } else {
                $message = "Terjadi kesalahan. Silakan coba lagi.";
            }
            $stmt_insert->close();
        }
        $stmt->close();
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="id" data-theme="arya">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi - SIMPRAK</title>
    
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.12.10/dist/full.min.css" rel="stylesheet" type="text/css" />
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --color-base-100: oklch(42% 0.199 265.638);
            --color-base-200: oklch(37% 0.146 265.522);
            --color-base-300: oklch(100% 0 0);
            --color-base-content: oklch(84.955% 0 0);
            --color-primary: oklch(70% 0.213 47.604);
            --color-primary-content: oklch(100% 0 0);
            --color-secondary: oklch(100% 0 0);
            --color-secondary-content: oklch(37% 0.146 265.522);
            --color-accent: oklch(64.8% 0.223 136.073);
            --color-accent-content: oklch(100% 0 0);
            --color-neutral: oklch(24.371% 0.046 65.681);
            --color-neutral-content: oklch(84.874% 0.009 65.681);
            --color-info: oklch(54.615% 0.215 262.88);
            --color-info-content: oklch(90.923% 0.043 262.88);
            --color-success: oklch(62.705% 0.169 149.213);
            --color-success-content: oklch(100% 0 0);
            --color-warning: oklch(66.584% 0.157 58.318);
            --color-warning-content: oklch(100% 0 0);
            --color-error: oklch(57% 0.245 27.325);
            --color-error-content: oklch(100% 0 0);
        }
        
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: linear-gradient(135deg, oklch(42% 0.199 265.638), oklch(28% 0.091 267.935));
            min-height: 100vh;
        }
        
        .register-container {
            backdrop-filter: blur(20px);
            background: linear-gradient(135deg, oklch(42% 0.199 265.638)/80, oklch(28% 0.091 267.935)/60);
            border: 1px solid oklch(70% 0.213 47.604)/30;
        }
        
        .floating-decoration {
            position: absolute;
            border-radius: 50%;
            background: oklch(70% 0.213 47.604)/10;
            filter: blur(40px);
            animation: float 6s ease-in-out infinite;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
        }
        
        .input-glow:focus {
            box-shadow: 0 0 0 2px oklch(70% 0.213 47.604)/50, 0 0 20px oklch(70% 0.213 47.604)/20;
        }
        
        .pulse-border {
            animation: pulse-border 2s infinite;
        }
        
        @keyframes pulse-border {
            0%, 100% { border-color: oklch(70% 0.213 47.604)/30; }
            50% { border-color: oklch(70% 0.213 47.604)/70; }
        }

        .dropdown-hover:hover .dropdown-content {
            display: block !important;
        }
        
        .dropdown-content.menu {
            background-color: oklch(28% 0.091 267.935) !important; /* Custom dropdown background */
            border: 0.5px solid oklch(42% 0.199 265.638) !important; /* Custom border */
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.3) !important; /* Enhanced shadow */
        }
        
        .dropdown-content.menu li a {
            background-color: oklch(28% 0.091 267.935) !important; /* Custom item background */
            color: oklch(100% 0 0) !important; /* White text */
            transition: all 0.2s ease !important;
        }
        
        .dropdown-content.menu li a:hover {
            background-color: oklch(70% 0.213 47.604) !important; /* Primary color background on hover */
            color: oklch(100% 0 0) !important; /* White text on hover */
        }
        
        .dropdown .btn {
            background-color: oklch(28% 0.091 267.935) !important; /* Custom button background */
            border-color: oklch(42% 0.199 265.638) !important; /* Custom border color */
            color: oklch(100% 0 0) !important; /* White text */
        }
        
        .dropdown .btn:hover {
            background-color: oklch(70% 0.213 47.604) !important; /* Primary color background on hover */
            border-color: oklch(70% 0.213 47.604) !important; /* Primary color border on hover */
            color: oklch(100% 0 0) !important; /* White text on hover */
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4 relative overflow-hidden">
    <!-- Background Decorations -->
    <div class="floating-decoration w-96 h-96 top-0 left-0 -translate-x-1/2 -translate-y-1/2" style="animation-delay: 0s;"></div>
    <div class="floating-decoration w-80 h-80 top-1/2 right-0 translate-x-1/2 -translate-y-1/2" style="animation-delay: 2s;"></div>
    <div class="floating-decoration w-64 h-64 bottom-0 left-1/3 translate-y-1/2" style="animation-delay: 4s;"></div>
    
    <!-- Register Card -->
    <div class="register-container w-full max-w-md relative z-10 rounded-3xl shadow-2xl p-8 pulse-border">
        <!-- Header -->
        <div class="text-center mb-8">
            <div class="flex items-center justify-center gap-3 mb-4">
                <div class="w-12 h-12 bg-[oklch(70%_0.213_47.604)] rounded-2xl flex items-center justify-center shadow-lg">
                    <svg class="w-7 h-7 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M8 9a3 3 0 100-6 3 3 0 000 6zM8 11a6 6 0 016 6H2a6 6 0 016-6zM16 7a1 1 0 10-2 0v1h-1a1 1 0 100 2h1v1a1 1 0 102 0v-1h1a1 1 0 100-2h-1V7z"></path>
                    </svg>
                </div>
                <h1 class="text-3xl font-bold text-[oklch(100%_0_0)]">SIMPRAK</h1>
            </div>
            <h2 class="text-xl font-semibold text-[oklch(100%_0_0)] mb-2">Buat Akun Baru</h2>
            <p class="text-[oklch(100%_0_0)]/70">Daftarkan diri Anda untuk memulai</p>
        </div>

        <!-- Status Messages -->
        <?php if (!empty($message)): ?>
            <div class="alert alert-error mb-6">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <span><?php echo htmlspecialchars($message); ?></span>
            </div>
        <?php endif; ?>

        <!-- Register Form -->
        <form action="register.php" method="post" class="space-y-6">
            <div class="form-control">
                <label class="label" for="nama">
                    <span class="label-text text-[oklch(100%_0_0)] font-medium flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        Nama Lengkap
                    </span>
                </label>
                <input type="text" 
                       id="nama" 
                       name="nama" 
                       required 
                       class="input input-bordered w-full bg-[oklch(28%_0.091_267.935)]/50 border-[oklch(70%_0.213_47.604)]/30 text-[oklch(100%_0_0)] placeholder:text-[oklch(100%_0_0)]/50 input-glow transition-all duration-300 focus:border-[oklch(70%_0.213_47.604)]" 
                       placeholder="Masukkan nama lengkap Anda">
            </div>

            <div class="form-control">
                <label class="label" for="email">
                    <span class="label-text text-[oklch(100%_0_0)] font-medium flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                        </svg>
                        Email Address
                    </span>
                </label>
                <input type="email" 
                       id="email" 
                       name="email" 
                       required 
                       class="input input-bordered w-full bg-[oklch(28%_0.091_267.935)]/50 border-[oklch(70%_0.213_47.604)]/30 text-[oklch(100%_0_0)] placeholder:text-[oklch(100%_0_0)]/50 input-glow transition-all duration-300 focus:border-[oklch(70%_0.213_47.604)]" 
                       placeholder="nama@email.com">
            </div>

            <div class="form-control">
                <label class="label" for="password">
                    <span class="label-text text-[oklch(100%_0_0)] font-medium flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                        Password
                    </span>
                </label>
                <input type="password" 
                       id="password" 
                       name="password" 
                       required 
                       class="input input-bordered w-full bg-[oklch(28%_0.091_267.935)]/50 border-[oklch(70%_0.213_47.604)]/30 text-[oklch(100%_0_0)] placeholder:text-[oklch(100%_0_0)]/50 input-glow transition-all duration-300 focus:border-[oklch(70%_0.213_47.604)]" 
                       placeholder="••••••••">
            </div>

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

            <button type="submit" class="btn w-full bg-[oklch(70%_0.213_47.604)] hover:bg-[oklch(100%_0_0)] border-2 border-[oklch(70%_0.213_47.604)] hover:border-[oklch(70%_0.213_47.604)] text-[oklch(100%_0_0)] hover:text-[oklch(70%_0.213_47.604)] font-semibold text-lg py-3 h-auto transition-all duration-300 hover:scale-[1.02] hover:shadow-lg group">
                <svg class="w-5 h-5 transition-colors duration-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                </svg>
                Daftar Akun
            </button>
        </form>

        <!-- Login Link -->
        <div class="divider text-[oklch(100%_0_0)]/50">atau</div>
        <div class="text-center">
            <p class="text-[oklch(100%_0_0)]/70">Sudah punya akun?</p>
            <a href="login.php" class="link link-hover text-[oklch(70%_0.213_47.604)] font-semibold text-lg hover:text-[oklch(100%_0_0)] transition-colors duration-300">
                Login di sini
            </a>
        </div>
        
        <!-- Footer -->
        <div class="text-center mt-8 pt-6 border-t border-[oklch(70%_0.213_47.604)]/20">
            <p class="text-xs text-[oklch(100%_0_0)]/50">&copy; <?php echo date('Y'); ?> SIMPRAK - Sistem Pengumpulan Tugas</p>
        </div>
    </div>
</body>
</html>