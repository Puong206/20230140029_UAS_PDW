<?php
// Pastikan session sudah dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cek jika pengguna belum login atau bukan asisten
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'asisten') {
    header("Location: ../login.php");
    exit();
}

// Mengambil data pengguna dari session
$asistenName = $_SESSION['user_name'] ?? 'Asisten';
$pageTitle = $pageTitle ?? 'Dashboard';
$activePage = $activePage ?? 'dashboard';

// Warna dari palet (DISAMAKAN DENGAN HEADER MAHASISWA)
$primaryColor = '093880'; // Primary Blue
$bgColor = '093880';      // Background
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Asisten - <?php echo $pageTitle; ?></title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <script src="https://unpkg.com/swup@4" defer></script>

    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }

        /* CSS untuk Efek "Scooped" */
        .active-scoop {
            position: relative;
            background-color: white;
            color: #<?php echo $bgColor; ?>;
            font-weight: 600;
            border-top-left-radius: 9999px;
            border-bottom-left-radius: 9999px;
        }
        .active-scoop::before,
        .active-scoop::after {
            content: '';
            position: absolute;
            right: 0;
            width: 2rem; /* 32px */
            height: 2rem; /* 32px */
            background-color: transparent;
        }
        .active-scoop::before {
            top: -2rem; /* -32px */
            border-bottom-right-radius: 9999px;
            box-shadow: 0 1rem 0 0 white; /* 16px */
        }
        .active-scoop::after {
            bottom: -2rem; /* -32px */
            border-top-right-radius: 9999px;
            box-shadow: 0 -1rem 0 0 white; /* -16px */
        }

        /* CSS untuk Animasi Perpindahan Halaman */
        .transition-slide {
            transition: opacity 0.3s, transform 0.3s;
            transform: translateX(0);
            opacity: 1;
        }
        html.is-leaving .transition-slide {
            opacity: 0;
            transform: translateY(15px);
        }
        html.is-rendering .transition-slide {
            opacity: 0;
            transform: translateY(-15px);
        }
    </style>
</head>
<body class="bg-gray-100">

<div class="flex h-screen bg-gray-100">
    <aside class="w-72 bg-[#<?php echo $bgColor; ?>] text-white hidden lg:flex flex-col">
        <div class="h-24 flex items-center justify-center">
            <h2 class="text-3xl font-bold text-white">SIMPRAK</h2>
        </div>
        
        <nav class="flex-grow">
            <ul class="space-y-1 py-4">
                <?php 
                    $activeClass = 'active-scoop'; // Class baru untuk efek scooped
                    $inactiveClass = 'text-blue-100 hover:bg-white/10 mx-6 rounded-full';
                ?>
                <li class="relative"><a href="dashboard.php" class="<?php echo ($activePage == 'dashboard') ? $activeClass : $inactiveClass; ?> flex items-center pl-8 pr-6 py-3 transition-all duration-300"><svg class="w-6 h-6 mr-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" /></svg><span>Dashboard</span></a></li>
                <li class="relative"><a href="praktikum.php" class="<?php echo ($activePage == 'praktikum') ? $activeClass : $inactiveClass; ?> flex items-center pl-8 pr-6 py-3 transition-all duration-300"><svg class="w-6 h-6 mr-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 9.75h16.5m-16.5 4.5h16.5m-16.5 4.5h16.5m-16.5-13.5h16.5" /></svg><span>Mata Praktikum</span></a></li>
                <li class="relative"><a href="modul.php" class="<?php echo ($activePage == 'modul') ? $activeClass : $inactiveClass; ?> flex items-center pl-8 pr-6 py-3 transition-all duration-300"><svg class="w-6 h-6 mr-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/></svg><span>Modul</span></a></li>
                <li class="relative"><a href="laporan.php" class="<?php echo ($activePage == 'laporan') ? $activeClass : $inactiveClass; ?> flex items-center pl-8 pr-6 py-3 transition-all duration-300"><svg class="w-6 h-6 mr-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75c0-.231-.035-.454-.1-.664M6.75 7.5h1.5M6.75 12h1.5m6.75 0h1.5m-1.5 3h1.5m-1.5 3h1.5M4.5 6.75h1.5v1.5H4.5v-1.5zM4.5 12h1.5v1.5H4.5v-1.5zM4.5 17.25h1.5v1.5H4.5v-1.5z"/></svg><span>Laporan Masuk</span></a></li>
                <li class="relative"><a href="pengguna.php" class="<?php echo ($activePage == 'pengguna') ? $activeClass : $inactiveClass; ?> flex items-center pl-8 pr-6 py-3 transition-all duration-300"><svg class="w-6 h-6 mr-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-4.67c.12-.241.252-.477.396-.702a4.125 4.125 0 013.472-2.132c.225 0 .445.03.655.084m-6.374 0c-1.132 0-2.186-.223-3.131-.628m-6.374 0c-1.132 0-2.186-.223-3.131-.628m18.536 0c-1.132 0-2.186-.223-3.131-.628m-6.374 0c-1.132 0-2.186-.223-3.131-.628" /></svg><span>Akun Pengguna</span></a></li>
            </ul>
        </nav>
        
        <div class="p-6 mt-auto text-center">
            <a href="../logout.php" class="text-sm text-blue-200 hover:underline">Logout</a>
        </div>
    </aside>

    <div id="swup" class="transition-slide flex-1 flex flex-col overflow-hidden">
        <header class="lg:hidden bg-white shadow-md">
            <div class="flex items-center justify-between px-4 py-3">
                <h2 class="text-xl font-bold text-gray-800">SIMPRAK</h2>
                <button id="mobile-menu-button" class="text-gray-500 focus:outline-none focus:text-gray-700">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
            </div>
        </header>

        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100">
            <div class="container mx-auto px-6 py-8">
                <h1 class="text-3xl font-bold text-gray-800 mb-6"><?php echo $pageTitle; ?></h1>
                ```