<?php
// Pastikan session sudah dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cek jika pengguna belum login dan alihkan ke halaman login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Ambil nama pengguna dari session untuk ditampilkan, dengan fallback 'Mahasiswa'
$userName = $_SESSION['user_name'] ?? 'Mahasiswa';
$userEmail = $_SESSION['user_email'] ?? '';

// Warna dari palet
$primaryColor = '093880'; // Primary
$darkColor = '0a1f33';    // Dark text
$avatarBgColor = $primaryColor;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Mahasiswa - <?php echo $pageTitle ?? 'Dashboard'; ?></title>
    
    <script src="https://cdn.tailwindcss.com"></script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/swup@4" defer></script>

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        /* Animasi Slide untuk Swup.js */
        .transition-slide {
            transition: opacity 0.4s, transform 0.4s;
            transform: translateX(0);
            opacity: 1;
        }
        html.is-leaving .transition-slide {
            opacity: 0;
            transform: translateX(20px);
        }
        html.is-rendering .transition-slide {
            opacity: 0;
            transform: translateX(-20px);
        }
    </style>
</head>
<body class="bg-[#F9FAFB]">

    <nav class="bg-white shadow-md sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                
                <div class="flex items-center">
                    <div class="flex-shrink-0 flex items-center">
                        <svg class="h-8 w-8 text-[#<?php echo $primaryColor; ?>]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v11.494m-5.45-6.894l5.45 6.894 5.45-6.894M12 3.75L6.55 9.106l5.45 6.894 5.45-6.894L12 3.75z" />
                        </svg>
                        <span class="text-[#<?php echo $darkColor; ?>] text-2xl font-bold ml-2">SIMPRAK</span>
                    </div>
                    <div class="hidden md:block">
                        <div class="ml-10 flex items-baseline space-x-4">
                            <?php 
                                $activeClass = 'bg-[#'.$primaryColor.'] text-white';
                                $inactiveClass = 'text-[#6B7280] hover:bg-gray-100 hover:text-[#'.$darkColor.']';
                            ?>
                            <a href="dashboard.php" class="<?php echo ($activePage == 'dashboard') ? $activeClass : $inactiveClass; ?> px-3 py-2 rounded-md text-sm font-medium transition-colors">Dashboard</a>
                            <a href="my_courses.php" class="<?php echo ($activePage == 'my_courses') ? $activeClass : $inactiveClass; ?> px-3 py-2 rounded-md text-sm font-medium transition-colors">Praktikum Saya</a>
                            <a href="courses.php" class="<?php echo ($activePage == 'courses') ? $activeClass : $inactiveClass; ?> px-3 py-2 rounded-md text-sm font-medium transition-colors">Cari Praktikum</a>
                        </div>
                    </div>
                </div>

                <div class="hidden md:block">
                    <div class="ml-4 flex items-center md:ml-6">
                        
                        <div class="relative" id="profile-menu-container">
                            <button type="button" id="profile-menu-button" class="flex items-center max-w-xs bg-gray-100 rounded-full text-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#<?php echo $primaryColor; ?>] p-1">
                                <span class="sr-only">Buka menu pengguna</span>
                                <span class="mx-2 font-semibold text-[#<?php echo $darkColor; ?>]"><?php echo htmlspecialchars($userName); ?></span>
                                <img class="h-8 w-8 rounded-full" src="https://ui-avatars.com/api/?name=<?php echo urlencode($userName); ?>&background=<?php echo $avatarBgColor; ?>&color=fff" alt="Avatar">
                            </button>
                            <div id="profile-dropdown" class="origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg py-1 bg-white ring-1 ring-black ring-opacity-5 focus:outline-none hidden" role="menu">
                                <a href="../logout.php" class="block px-4 py-2 text-sm text-[#<?php echo $darkColor; ?>] hover:bg-gray-100" role="menuitem">Logout dari menu</a>
                            </div>
                        </div>

                        <a href="../logout.php" class="ml-4 bg-[#eb8317] text-white font-bold py-2 px-4 rounded-md hover:bg-opacity-90 transition-colors duration-300">
                            Logout
                        </a>
                        
                    </div>
                </div>

                <div class="-mr-2 flex md:hidden">
                    <button type="button" id="mobile-menu-button" class="bg-gray-100 inline-flex items-center justify-center p-2 rounded-md text-gray-500 hover:text-[#<?php echo $darkColor; ?>] hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#<?php echo $primaryColor; ?>]">
                        <span class="sr-only">Buka menu utama</span>
                        <svg class="h-6 w-6" id="hamburger-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
                        <svg class="h-6 w-6 hidden" id="close-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

            </div>
        </div>

        <div class="md:hidden hidden" id="mobile-menu">
            <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3">
                <a href="dashboard.php" class="<?php echo ($activePage == 'dashboard') ? 'bg-[#'.$primaryColor.'] text-white' : 'text-[#'.$darkColor.'] hover:bg-gray-100'; ?> block px-3 py-2 rounded-md text-base font-medium transition-colors">Dashboard</a>
                <a href="my_courses.php" class="<?php echo ($activePage == 'my_courses') ? 'bg-[#'.$primaryColor.'] text-white' : 'text-[#'.$darkColor.'] hover:bg-gray-100'; ?> block px-3 py-2 rounded-md text-base font-medium transition-colors">Praktikum Saya</a>
                <a href="courses.php" class="<?php echo ($activePage == 'courses') ? 'bg-[#'.$primaryColor.'] text-white' : 'text-[#'.$darkColor.'] hover:bg-gray-100'; ?> block px-3 py-2 rounded-md text-base font-medium transition-colors">Cari Praktikum</a>
            </div>
            <div class="pt-4 pb-3 border-t border-gray-200">
                <div class="flex items-center px-5">
                    <div class="flex-shrink-0">
                         <img class="h-10 w-10 rounded-full" src="https://ui-avatars.com/api/?name=<?php echo urlencode($userName); ?>&background=<?php echo $avatarBgColor; ?>&color=fff" alt="Avatar">
                    </div>
                    <div class="ml-3">
                        <div class="text-base font-semibold leading-none text-[#<?php echo $darkColor; ?>]"><?php echo htmlspecialchars($userName); ?></div>
                        <div class="text-sm font-medium leading-none text-[#6B7280]"><?php echo htmlspecialchars($userEmail); ?></div>
                    </div>
                </div>
                <div class="mt-3 px-2 space-y-1">
                    <a href="../logout.php" class="block px-3 py-2 rounded-md text-base font-medium text-[#<?php echo $darkColor; ?>] hover:bg-gray-100 transition-colors">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <main id="swup" class="transition-slide">
        <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
            