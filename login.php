<?php
require_once 'config.php';

// Jika sudah login, redirect ke halaman yang sesuai
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] == 'asisten') {
        header("Location: asisten/dashboard.php");
    } elseif ($_SESSION['role'] == 'mahasiswa') {
        header("Location: mahasiswa/dashboard.php");
    }
    exit();
}

$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (empty($email) || empty($password)) {
        $message = "Email dan password harus diisi!";
    } else {
        $sql = "SELECT id, nama, email, password, role FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            // Verifikasi password
            if (password_verify($password, $user['password'])) {
                // Password benar, simpan semua data penting ke session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['nama'] = $user['nama'];
                $_SESSION['role'] = $user['role'];

                // ====== INI BAGIAN YANG DIUBAH ======
                // Logika untuk mengarahkan pengguna berdasarkan peran (role)
                if ($user['role'] == 'asisten') {
                    header("Location: asisten/dashboard.php");
                    exit();
                } elseif ($user['role'] == 'mahasiswa') {
                    header("Location: mahasiswa/dashboard.php");
                    exit();
                } else {
                    // Fallback jika peran tidak dikenali
                    $message = "Peran pengguna tidak valid.";
                }
                // ====== AKHIR DARI BAGIAN YANG DIUBAH ======

            } else {
                $message = "Password yang Anda masukkan salah.";
            }
        } else {
            $message = "Akun dengan email tersebut tidak ditemukan.";
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
    <title>Login - SIMPRAK</title>
    
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
        
        .login-container {
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
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4 relative overflow-hidden">
    <!-- Background Decorations -->
    <div class="floating-decoration w-96 h-96 top-0 left-0 -translate-x-1/2 -translate-y-1/2" style="animation-delay: 0s;"></div>
    <div class="floating-decoration w-80 h-80 top-1/2 right-0 translate-x-1/2 -translate-y-1/2" style="animation-delay: 2s;"></div>
    <div class="floating-decoration w-64 h-64 bottom-0 left-1/3 translate-y-1/2" style="animation-delay: 4s;"></div>
    
    <!-- Login Card -->
    <div class="login-container w-full max-w-md relative z-10 rounded-3xl shadow-2xl p-8 pulse-border">
        <!-- Header -->
        <div class="text-center mb-8">
            <div class="flex items-center justify-center gap-3 mb-4">
                <div class="w-12 h-12 bg-[oklch(70%_0.213_47.604)] rounded-2xl flex items-center justify-center shadow-lg">
                    <svg class="w-7 h-7 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <h1 class="text-3xl font-bold text-[oklch(100%_0_0)]">SIMPRAK</h1>
            </div>
            <h2 class="text-xl font-semibold text-[oklch(100%_0_0)] mb-2">Selamat Datang</h2>
            <p class="text-[oklch(100%_0_0)]/70">Masuk ke akun Anda untuk melanjutkan</p>
        </div>

        <!-- Status Messages -->
        <?php 
            if (isset($_GET['status']) && $_GET['status'] == 'registered') {
                echo '<div class="alert alert-success mb-6">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>Registrasi berhasil! Silakan login.</span>
                      </div>';
            }
            if (!empty($message)) {
                echo '<div class="alert alert-error mb-6">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <span>' . htmlspecialchars($message) . '</span>
                      </div>';
            }
        ?>

        <!-- Login Form -->
        <form action="login.php" method="post" class="space-y-6">
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

            <button type="submit" class="btn w-full bg-[oklch(70%_0.213_47.604)] hover:bg-[oklch(100%_0_0)] border-2 border-[oklch(70%_0.213_47.604)] hover:border-[oklch(70%_0.213_47.604)] text-[oklch(100%_0_0)] hover:text-[oklch(70%_0.213_47.604)] font-semibold text-lg py-3 h-auto transition-all duration-300 hover:scale-[1.02] hover:shadow-lg group">
                <svg class="w-5 h-5 transition-colors duration-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                </svg>
                Masuk ke Akun
            </button>
        </form>

        <!-- Register Link -->
        <div class="divider text-[oklch(100%_0_0)]/50">atau</div>
        <div class="text-center">
            <p class="text-[oklch(100%_0_0)]/70">Belum punya akun?</p>
            <a href="register.php" class="link link-hover text-[oklch(70%_0.213_47.604)] font-semibold text-lg hover:text-[oklch(100%_0_0)] transition-colors duration-300">
                Daftar di sini
            </a>
        </div>
        
        <!-- Footer -->
        <div class="text-center mt-8 pt-6 border-t border-[oklch(70%_0.213_47.604)]/20">
            <p class="text-xs text-[oklch(100%_0_0)]/50">&copy; <?php echo date('Y'); ?> SIMPRAK - Sistem Pengumpulan Tugas</p>
        </div>
    </div>
</body>
</html>