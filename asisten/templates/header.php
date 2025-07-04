<?php
// 2. DEFINISI VARIABEL: Menyiapkan variabel untuk digunakan di template
$asistenName = $_SESSION['nama'] ?? '';
$pageTitle = $pageTitle ?? 'Dashboard';
$activePage = $activePage ?? 'dashboard';

?>
<!DOCTYPE html>
<html lang="id" data-theme="arya"> 
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Asisten - <?php echo $pageTitle; ?></title>
    
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
            --radius-selector: 1rem;
            --radius-field: 1rem;
            --radius-box: 1rem;
            --size-selector: 0.25rem;
            --size-field: 0.25rem;
            --border: 1px;
            --depth: 1;
            --noise: 0;
        }
        
        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background-color: oklch(37% 0.146 265.522) !important; /* New background color */
            color: oklch(84.955% 0 0) !important; /* Arya base-content text */
        }
        
        .btn-primary {
            background-color: oklch(100% 0 0) !important; /* Primary color */
            color: oklch(42% 0.199 265.638) !important; /* White text */
            font-weight: bold
        }
        
        .btn-primary:hover {
            background-color: oklch(42% 0.199 265.638) !important; /* Swap to dark background */
            color: oklch(100% 0 0) !important; /* Swap to white text */
            font-weight: bold !important; /* Bold text */
        }

        .btn-ghost {
            color: oklch(84.955% 0 0) !important; /* Arya base-content text */
        }

        .btn-ghost:hover {
            background-color: oklch(70% 0.213 47.604) !important; /* Primary color background on hover */
            color: oklch(100% 0 0) !important; /* White text on hover */
        }

        .btn-error {
            background-color: oklch(70% 0.213 47.604) !important; /* Error color */
            color: oklch(100% 0 0) !important; /* White text */
        }

        .btn-error:hover {
            background-color: oklch(100% 0 0) !important; /* Darker error color on hover */
            color: oklch(70% 0.213 47.604) !important; /* White text on hover */
        }

        /* Custom select styling */
        .select.select-bordered {
            background-color: oklch(28% 0.091 267.935) !important; /* Custom select background */
            border-color: oklch(42% 0.199 265.638) !important; /* Custom border color */
            color: oklch(100% 0 0) !important; /* White text */
        }
        
        .select.select-bordered:focus {
            border-color: oklch(70% 0.213 47.604) !important; /* Primary color border on focus */
            outline: none !important;
        }

        /* Custom dropdown styling */
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

        /* Custom navbar background */
        .navbar.bg-base-100 {
            background-color: oklch(28% 0.091 267.935) !important; /* Custom navbar color */
        }
        
        /* Custom sidebar background */
        .drawer-side .menu.bg-base-100 {
            background-color: oklch(28% 0.091 267.935) !important; /* Custom sidebar color */
        }

        .stat-title,
        .stat-desc,
        .table th,
        .table td,
        .font-semibold,
        .text-sm,
        .flex-grow {
            color: oklch(100% 0 0) !important; /* White text for stat titles */
        }
        
        /* Active menu styling */
        .menu li.active > a {
            background-color: oklch(70% 0.213 47.604) !important; /* Primary color */
            color: oklch(100% 0 0) !important; /* White text */
            font-weight: 600;
            border-radius: 0.5rem;
        }
        

        .menu li.active > a:hover {
            background-color: oklch(65% 0.213 47.604) !important; /* Slightly darker primary */
        }
        
        /* Hover effect for non-active menu items */
        .menu li:not(.active) > a:hover {
            background-color: oklch(84.955% 0 0) !important; /* White background */
            color: oklch(32% 0.1 265.522) !important; /* Dark text */
        }
        
        /* Custom card background */
        .card.bg-base-100,
        .stats.bg-base-100 {
            background-color: oklch(28% 0.091 267.935) !important; /* Custom card color */
        }
    </style>
</head>
<body class="bg-base-200">

<div class="drawer lg:drawer-open">
    <input id="my-drawer-2" type="checkbox" class="drawer-toggle" />
    <div class="drawer-content flex flex-col items-start justify-start">
        <div class="navbar bg-base-100 lg:hidden">
            <div class="flex-none">
                <label for="my-drawer-2" class="btn btn-square btn-ghost drawer-button">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                </label>
            </div>
            <div class="flex-1">
                <a class="btn btn-ghost text-xl">SIMPRAK</a>
            </div>
        </div>
        <main class="flex-1 p-6 lg:p-8 w-full">
            <h1 class="text-3xl font-bold mb-6" style="color: oklch(100% 0 0);"><?php echo $pageTitle; ?></h1>
