<?php
// Definisi variabel untuk template
$mahasiswaName = $_SESSION['nama'] ?? '';
$pageTitle = $pageTitle ?? 'Dashboard';
$activePage = $activePage ?? 'dashboard';

// Pastikan session sudah dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cek jika pengguna belum login dan alihkan ke halaman login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id" data-theme="light"> 
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Mahasiswa - <?php echo $pageTitle; ?></title>
    
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.12.10/dist/full.min.css" rel="stylesheet" type="text/css" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --color-base-100: oklch(100% 0 0);
            --color-base-200: oklch(96% 0 0);
            --color-base-300: oklch(42% 0.199 265.638);
            --color-base-content: oklch(30% 0.199 265.638);
            --color-primary: oklch(42% 0.199 265.638);
            --color-primary-content: oklch(100% 0 0);
            --color-secondary: oklch(42% 0.199 265.638);
            --color-secondary-content: oklch(100% 0 0);
            --color-accent: oklch(55% 0.223 136.073);
            --color-accent-content: oklch(100% 0 0);
            --color-neutral: oklch(85% 0 0);
            --color-neutral-content: oklch(30% 0.199 265.638);
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
            background-color: oklch(96% 0 0) !important;
            color: oklch(30% 0.199 265.638) !important;
        }
        
        .btn-primary {
            background-color: oklch(42% 0.199 265.638) !important; /* Blue background */
            color: oklch(100% 0 0) !important; /* White text */
            font-weight: bold;
            border: 2px solid oklch(42% 0.199 265.638) !important;
        }
        
        .btn-primary:hover {
            background-color: oklch(100% 0 0) !important; /* White background on hover */
            color: oklch(42% 0.199 265.638) !important; /* Blue text on hover */
            font-weight: bold !important;
            border: 2px solid oklch(42% 0.199 265.638) !important;
        }

        /* Ensure all children of btn-primary follow the color rules with maximum specificity */
        .btn-primary * {
            color: oklch(100% 0 0) !important; /* Force white for all children */
        }

        .btn-primary:hover * {
            color: oklch(42% 0.199 265.638) !important; /* Force blue for all children on hover */
        }

        /* Override any DaisyUI or Tailwind specific classes */
        .btn.btn-primary span,
        .btn.btn-primary svg {
            color: oklch(100% 0 0) !important;
        }

        .btn.btn-primary:hover span,
        .btn.btn-primary:hover svg {
            color: oklch(42% 0.199 265.638) !important;
        }

        /* Override inline styles on hover */
        .btn.btn-primary:hover {
            color: oklch(42% 0.199 265.638) !important;
        }

        .btn.btn-primary:hover span[style],
        .btn.btn-primary:hover svg[style] {
            color: oklch(42% 0.199 265.638) !important;
        }

        .btn-ghost {
            color: oklch(30% 0.199 265.638) !important; /* Dark text like admin light theme */
        }

        .btn-ghost:hover {
            background-color: oklch(70% 0.213 47.604) !important; /* Primary color background on hover like admin */
            color: oklch(100% 0 0) !important; /* White text on hover like admin */
        }

        .btn-error {
            background-color: oklch(70% 0.213 47.604) !important; /* Error color like admin */
            color: oklch(100% 0 0) !important; /* White text like admin */
        }

        .btn-error:hover {
            background-color: oklch(100% 0 0) !important; /* White background on hover like admin */
            color: oklch(70% 0.213 47.604) !important; /* Error color text on hover like admin */
        }

        .select.select-bordered {
            background-color: oklch(100% 0 0) !important;
            border-color: oklch(85% 0 0) !important;
            color: oklch(30% 0.199 265.638) !important;
        }
        
        .select.select-bordered:focus {
            border-color: oklch(42% 0.199 265.638) !important;
            outline: none !important;
        }

        .dropdown-hover:hover .dropdown-content {
            display: block !important;
        }
        
        .dropdown-content.menu {
            background-color: oklch(100% 0 0) !important;
            border: 0.5px solid oklch(85% 0 0) !important;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1) !important;
        }
        
        .dropdown-content.menu li a {
            background-color: oklch(100% 0 0) !important;
            color: oklch(30% 0.199 265.638) !important;
            transition: all 0.2s ease !important;
        }
        
        .dropdown-content.menu li a:hover {
            background-color: oklch(42% 0.199 265.638) !important;
            color: oklch(100% 0 0) !important;
        }

        .navbar.bg-base-100 {
            background-color: oklch(100% 0 0) !important;
            border-bottom: 1px solid oklch(85% 0 0) !important;
        }

        .stat-title,
        .stat-desc,
        .table th,
        .table td,
        .font-semibold,
        .text-sm,
        .flex-grow {
            color: oklch(30% 0.199 265.638) !important;
        }
        
        .menu li a.active {
            background-color: oklch(42% 0.199 265.638) !important;
            color: oklch(100% 0 0) !important;
            font-weight: 600;
            border-radius: 0.5rem;
        }

        .menu li a.active:hover {
            background-color: oklch(37% 0.199 265.638) !important;
            color: oklch(100% 0 0) !important;
        }
        
        .menu li a:not(.active):hover {
            background-color: oklch(42% 0.199 265.638) !important;
            color: oklch(100% 0 0) !important;
        }
        
        .card.bg-base-100,
        .stats.bg-base-100 {
            background-color: oklch(100% 0 0) !important;
            border: 1px solid oklch(85% 0 0) !important;
        }
        
        /* Animation keyframes */
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        @keyframes fadeInUp {
            from { 
                opacity: 0; 
                transform: translateY(20px); 
            }
            to { 
                opacity: 1; 
                transform: translateY(0); 
            }
        }
        
        @keyframes slideIn {
            from { 
                opacity: 0; 
                transform: translateX(-20px); 
            }
            to { 
                opacity: 1; 
                transform: translateX(0); 
            }
        }
        
        @keyframes bounce {
            0%, 20%, 53%, 80%, 100% {
                transform: translate3d(0,0,0);
            }
            40%, 43% {
                transform: translate3d(0, -30px, 0);
            }
            70% {
                transform: translate3d(0, -15px, 0);
            }
            90% {
                transform: translate3d(0, -4px, 0);
            }
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        @keyframes shimmer {
            0% { background-position: -200% 0; }
            100% { background-position: 200% 0; }
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        
        @keyframes floatDelayed {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-15px); }
        }
        
        @keyframes sparkle {
            0%, 100% { opacity: 0; transform: scale(0); }
            50% { opacity: 1; transform: scale(1); }
        }
        
        @keyframes spinSlow {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        
        /* Animation utility classes */
        .animate-fade-in { animation: fadeIn 0.5s ease-out; }
        .animate-fade-in-up { animation: fadeInUp 0.6s ease-out; }
        .animate-slide-in { animation: slideIn 0.5s ease-out; }
        .animate-bounce-gentle { animation: bounce 2s infinite; }
        .animate-pulse-gentle { animation: pulse 2s infinite; }
        .animate-shimmer { 
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            background-size: 200% 100%;
            animation: shimmer 3s infinite;
        }
        .animate-float { animation: float 3s ease-in-out infinite; }
        .animate-float-delayed { animation: floatDelayed 3s ease-in-out infinite 1.5s; }
        .animate-sparkle { animation: sparkle 2s ease-in-out infinite; }
        .animate-spin-slow { animation: spinSlow 10s linear infinite; }
        
        /* Hover effects */
        .hover-scale:hover {
            transform: scale(1.02);
            transition: transform 0.2s ease;
        }
        
        .hover-lift:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }
        
        .loading-shimmer {
            background: linear-gradient(90deg, oklch(96% 0 0) 25%, oklch(100% 0 0) 50%, oklch(96% 0 0) 75%);
            background-size: 200% 100%;
            animation: shimmer 1.5s infinite;
        }

        /* Responsive improvements */
        @media (max-width: 1024px) {
            .container {
                padding-left: 1rem;
                padding-right: 1rem;
            }
        }
        
        /* Enhanced responsive improvements for dashboard */
        @media (max-width: 640px) {
            .hero-content {
                padding-left: 1rem !important;
                padding-right: 1rem !important;
            }
            
            .card-body {
                padding: 1.5rem !important;
            }
            
            .badge {
                font-size: 0.75rem !important;
                padding: 0.5rem 0.75rem !important;
            }
            
            .hero h1 {
                font-size: 2.5rem !important;
                line-height: 1.1 !important;
            }
            
            .hero h2 {
                font-size: 1.5rem !important;
            }
            
            .hero p {
                font-size: 1rem !important;
                padding: 0 0.5rem !important;
            }
        }
        
        @media (max-width: 768px) {
            .grid {
                gap: 1rem !important;
            }
            
            .stats-card {
                margin: 0 0.5rem !important;
            }
        }
        
        /* Enhanced card hover effects */
        .hover-lift {
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1) !important;
        }
        
        .hover-lift:hover {
            transform: translateY(-8px) scale(1.02) !important;
            box-shadow: 0 20px 40px -12px rgba(0, 0, 0, 0.15) !important;
        }
        
        /* Better spacing for mobile */
        .container-padding {
            padding-left: 1rem;
            padding-right: 1rem;
        }
        
        @media (min-width: 640px) {
            .container-padding {
                padding-left: 1.5rem;
                padding-right: 1.5rem;
            }
        }
        
        @media (min-width: 1024px) {
            .container-padding {
                padding-left: 2rem;
                padding-right: 2rem;
            }
        }
        
        /* Enhanced loading states */
        .loading-state {
            background: linear-gradient(90deg, 
                oklch(96% 0 0) 25%, 
                oklch(100% 0 0) 50%, 
                oklch(96% 0 0) 75%);
            background-size: 200% 100%;
            animation: shimmer 1.5s infinite;
        }
        
        /* Improved button responsiveness */
        .btn-responsive {
            padding: 0.75rem 1rem;
            font-size: 0.875rem;
        }
        
        @media (min-width: 640px) {
            .btn-responsive {
                padding: 1rem 2rem;
                font-size: 1rem;
            }
        }
        
        /* Better chart container responsiveness */
        .chart-container {
            position: relative;
            height: 250px;
        }
        
        @media (min-width: 640px) {
            .chart-container {
                height: 300px;
            }
        }
        
        @media (min-width: 1024px) {
            .chart-container {
                height: 320px;
            }
        }
        
        /* Enhanced gradient animations */
        @keyframes gradientShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        
        .gradient-animate {
            background: linear-gradient(-45deg, 
                oklch(42% 0.199 265.638), 
                oklch(48% 0.211 225.457), 
                oklch(55% 0.223 136.073), 
                oklch(42% 0.199 265.638));
            background-size: 400% 400%;
            animation: gradientShift 8s ease infinite;
        }
        
        /* Button text color fixes */
        .btn-ghost.text-white {
            color: white !important;
        }
        
        .btn-ghost.text-white:hover {
            color: white !important;
        }
        
        .btn-ghost.text-white svg {
            color: white !important;
        }
        
        .btn-ghost.text-white:hover svg {
            color: white !important;
        }
        
        /* Quick action card button styling */
        .card .btn-ghost {
            color: white !important;
            border-color: rgba(255, 255, 255, 0.3) !important;
        }
        
        .card .btn-ghost:hover {
            background-color: rgba(255, 255, 255, 0.2) !important;
            color: white !important;
            border-color: rgba(255, 255, 255, 0.5) !important;
        }
        
        .card .btn-ghost svg {
            color: white !important;
        }
        
        /* Badge text color fixes for hero section */
        .badge.text-white {
            color: white !important;
        }
        
        .badge.text-white span {
            color: white !important;
        }
        
        .badge.text-white svg {
            color: white !important;
        }
        
        .badge:hover.text-white {
            color: white !important;
        }
        
        .badge:hover.text-white span {
            color: white !important;
        }
        
        .badge:hover.text-white svg {
            color: white !important;
        }
        
        /* Ensure hero badges always have white text */
        .hero .badge {
            color: white !important;
        }
        
        .hero .badge svg {
            color: white !important;
        }
        
        .hero .badge span {
            color: white !important;
        }
        
        /* Line clamp utility for text truncation */
        .line-clamp-3 {
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        /* Additional course card enhancements */
        .course-card-hover:hover {
            transform: translateY(-4px) scale(1.01);
            box-shadow: 0 15px 35px -5px rgba(0, 0, 0, 0.1);
        }
    </style>
    
    <script>
        // Animation utilities untuk mahasiswa panel
        const AnimationUtils = {
            // Animate counter numbers
            animateCounter: function(element, target, duration = 1000) {
                const start = parseInt(element.textContent) || 0;
                const range = target - start;
                const startTime = performance.now();
                
                function updateCounter(currentTime) {
                    const elapsed = currentTime - startTime;
                    const progress = Math.min(elapsed / duration, 1);
                    const easeOutQuart = 1 - Math.pow(1 - progress, 4);
                    const current = Math.round(start + (range * easeOutQuart));
                    
                    element.textContent = current;
                    
                    if (progress < 1) {
                        requestAnimationFrame(updateCounter);
                    }
                }
                
                requestAnimationFrame(updateCounter);
            },
            
            // Create sparkle effect
            createSparkle: function(element) {
                const sparkle = document.createElement('div');
                sparkle.className = 'absolute w-1 h-1 bg-blue-400 rounded-full animate-ping';
                sparkle.style.left = Math.random() * 100 + '%';
                sparkle.style.top = Math.random() * 100 + '%';
                
                element.style.position = 'relative';
                element.appendChild(sparkle);
                
                setTimeout(() => sparkle.remove(), 1000);
            },
            
            // Add ripple effect to buttons
            addRippleEffect: function(button, event) {
                const rect = button.getBoundingClientRect();
                const ripple = document.createElement('span');
                const size = Math.max(rect.width, rect.height);
                const x = event.clientX - rect.left - size / 2;
                const y = event.clientY - rect.top - size / 2;
                
                ripple.style.width = ripple.style.height = size + 'px';
                ripple.style.left = x + 'px';
                ripple.style.top = y + 'px';
                ripple.className = 'absolute bg-white/30 rounded-full animate-ping pointer-events-none';
                
                button.style.position = 'relative';
                button.style.overflow = 'hidden';
                button.appendChild(ripple);
                
                setTimeout(() => ripple.remove(), 600);
            },
            
            // Enhanced counter animation function for dashboard
            animateCounterEnhanced: function(element, target, duration = 2000) {
                const start = parseInt(element.textContent) || 0;
                const increment = target / (duration / 16);
                let current = start;
                
                const timer = setInterval(() => {
                    current += increment;
                    if (current >= target) {
                        element.textContent = target;
                        clearInterval(timer);
                    } else {
                        element.textContent = Math.floor(current);
                    }
                }, 16);
            }
        };
        
        // Dashboard specific utilities
        const DashboardUtils = {
            // Enhanced feature exploration function
            exploreFeatures: function() {
                // Smooth scroll to stats section with highlighting effect
                const statsSection = document.getElementById('statsSection');
                if (!statsSection) return;
                
                // Add highlighting effect
                statsSection.style.transform = 'scale(1.02)';
                statsSection.style.transition = 'all 0.5s ease';
                
                // Scroll to section
                statsSection.scrollIntoView({ 
                    behavior: 'smooth',
                    block: 'center'
                });
                
                // Reset transform after scroll
                setTimeout(() => {
                    statsSection.style.transform = 'scale(1)';
                    
                    // Add sparkle effect to each card
                    document.querySelectorAll('#statsSection .card').forEach((card, index) => {
                        setTimeout(() => {
                            card.classList.add('animate-pulse');
                            
                            // Create sparkle elements
                            for(let i = 0; i < 5; i++) {
                                const sparkle = document.createElement('div');
                                sparkle.className = 'absolute w-1 h-1 bg-primary rounded-full animate-ping pointer-events-none z-50';
                                sparkle.style.left = Math.random() * 100 + '%';
                                sparkle.style.top = Math.random() * 100 + '%';
                                sparkle.style.animationDuration = (0.5 + Math.random() * 0.5) + 's';
                                
                                card.style.position = 'relative';
                                card.appendChild(sparkle);
                                
                                setTimeout(() => sparkle.remove(), 1000);
                            }
                            
                            setTimeout(() => card.classList.remove('animate-pulse'), 2000);
                        }, index * 200);
                    });
                }, 1000);
            },
            
            // Initialize dashboard charts
            initCharts: function(totalPraktikum, tugasSelesai, tugasMenunggu) {
                // Hide chart loaders after a delay
                setTimeout(() => {
                    const progressLoader = document.getElementById('progressChartLoader');
                    const activityLoader = document.getElementById('activityChartLoader');
                    if (progressLoader) progressLoader.style.display = 'none';
                    if (activityLoader) activityLoader.style.display = 'none';
                }, 1500);
                
                // Enhanced Progress Doughnut Chart
                const progressCtx = document.getElementById('progressChart');
                if (progressCtx && typeof Chart !== 'undefined') {
                    const progressChart = new Chart(progressCtx.getContext('2d'), {
                        type: 'doughnut',
                        data: {
                            labels: ['Tugas Selesai', 'Tugas Menunggu', 'Belum Ada Tugas'],
                            datasets: [{
                                data: [
                                    tugasSelesai,
                                    tugasMenunggu,
                                    Math.max(0, totalPraktikum * 2 - tugasSelesai - tugasMenunggu)
                                ],
                                backgroundColor: [
                                    'oklch(62.705% 0.169 149.213)', // Success color
                                    'oklch(66.584% 0.157 58.318)',  // Warning color
                                    'oklch(88% 0.02 240)'           // Neutral color
                                ],
                                borderWidth: 0,
                                hoverOffset: 15,
                                hoverBorderWidth: 3,
                                hoverBorderColor: ['#ffffff', '#ffffff', '#ffffff']
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: true,
                            aspectRatio: 1,
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: {
                                        padding: 25,
                                        usePointStyle: true,
                                        pointStyle: 'circle',
                                        font: {
                                            size: 14,
                                            family: 'Plus Jakarta Sans',
                                            weight: '600'
                                        },
                                        color: 'oklch(30% 0.199 265.638)'
                                    }
                                },
                                tooltip: {
                                    backgroundColor: 'rgba(0, 0, 0, 0.9)',
                                    titleFont: {
                                        family: 'Plus Jakarta Sans',
                                        size: 16,
                                        weight: 'bold'
                                    },
                                    bodyFont: {
                                        family: 'Plus Jakarta Sans',
                                        size: 14
                                    },
                                    cornerRadius: 12,
                                    displayColors: true,
                                    callbacks: {
                                        afterLabel: function(context) {
                                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                            const percentage = ((context.raw / total) * 100).toFixed(1);
                                            return `${percentage}% dari total`;
                                        }
                                    }
                                }
                            },
                            cutout: '65%',
                            animation: {
                                animateRotate: true,
                                duration: 2500,
                                easing: 'easeInOutQuart'
                            },
                            interaction: {
                                intersect: false,
                                mode: 'nearest'
                            }
                        }
                    });
                }

                // Enhanced Activity Bar Chart
                const activityCtx = document.getElementById('activityChart');
                if (activityCtx && typeof Chart !== 'undefined') {
                    const activityChart = new Chart(activityCtx.getContext('2d'), {
                        type: 'bar',
                        data: {
                            labels: ['Praktikum\nDiikuti', 'Tugas\nSelesai', 'Tugas\nMenunggu'],
                            datasets: [{
                                label: 'Jumlah',
                                data: [totalPraktikum, tugasSelesai, tugasMenunggu],
                                backgroundColor: [
                                    'oklch(42% 0.199 265.638)',      // Primary color
                                    'oklch(62.705% 0.169 149.213)',  // Success color
                                    'oklch(66.584% 0.157 58.318)'    // Warning color
                                ],
                                borderRadius: 12,
                                borderSkipped: false,
                                hoverBackgroundColor: [
                                    'oklch(38% 0.199 265.638)',
                                    'oklch(58% 0.169 149.213)',
                                    'oklch(62% 0.157 58.318)'
                                ],
                                borderWidth: 2,
                                borderColor: [
                                    'oklch(42% 0.199 265.638)',
                                    'oklch(62.705% 0.169 149.213)',
                                    'oklch(66.584% 0.157 58.318)'
                                ]
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: true,
                            aspectRatio: 1.5,
                            plugins: {
                                legend: {
                                    display: false
                                },
                                tooltip: {
                                    backgroundColor: 'rgba(0, 0, 0, 0.9)',
                                    titleFont: {
                                        family: 'Plus Jakarta Sans',
                                        size: 16,
                                        weight: 'bold'
                                    },
                                    bodyFont: {
                                        family: 'Plus Jakarta Sans',
                                        size: 14
                                    },
                                    cornerRadius: 12,
                                    displayColors: true
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        stepSize: 1,
                                        font: {
                                            family: 'Plus Jakarta Sans',
                                            size: 12,
                                            weight: '500'
                                        },
                                        color: 'oklch(60% 0.1 240)'
                                    },
                                    grid: {
                                        color: 'oklch(90% 0.02 240)',
                                        lineWidth: 1
                                    },
                                    border: {
                                        display: false
                                    }
                                },
                                x: {
                                    ticks: {
                                        font: {
                                            family: 'Plus Jakarta Sans',
                                            size: 12,
                                            weight: '600'
                                        },
                                        color: 'oklch(50% 0.1 240)'
                                    },
                                    grid: {
                                        display: false
                                    },
                                    border: {
                                        display: false
                                    }
                                }
                            },
                            animation: {
                                duration: 2500,
                                easing: 'easeOutBounce'
                            },
                            interaction: {
                                intersect: false,
                                mode: 'index'
                            }
                        }
                    });
                }
            },
            
            // Initialize dashboard animations
            initDashboardAnimations: function() {
                // Animate counters with staggered delay
                document.querySelectorAll('[data-counter]').forEach((counter, index) => {
                    setTimeout(() => {
                        const target = parseInt(counter.getAttribute('data-counter'));
                        AnimationUtils.animateCounterEnhanced(counter, target, 2000 + (index * 200));
                    }, 500 + (index * 100));
                });

                // Progress bars animation with enhanced timing
                setTimeout(() => {
                    document.querySelectorAll('.progress').forEach((progress, index) => {
                        setTimeout(() => {
                            const value = progress.getAttribute('value');
                            progress.style.setProperty('--value', value);
                            progress.style.transition = 'all 1.5s cubic-bezier(0.4, 0, 0.2, 1)';
                        }, index * 300);
                    });
                }, 1500);

                // Enhanced sparkle effect on card hover with responsive adjustments
                document.querySelectorAll('.hover-lift').forEach(card => {
                    card.addEventListener('mouseenter', function() {
                        // Only add sparkle effect on non-touch devices
                        if (!('ontouchstart' in window)) {
                            // Create multiple sparkle effects
                            for(let i = 0; i < 4; i++) {
                                setTimeout(() => {
                                    const sparkle = document.createElement('div');
                                    sparkle.className = 'absolute w-2 h-2 sm:w-3 sm:h-3 rounded-full pointer-events-none z-10';
                                    sparkle.style.background = 'radial-gradient(circle, rgba(66, 101, 155, 0.8) 0%, rgba(66, 101, 155, 0) 70%)';
                                    sparkle.style.left = Math.random() * 100 + '%';
                                    sparkle.style.top = Math.random() * 100 + '%';
                                    sparkle.style.animation = `sparkleFloat ${1 + Math.random()}s ease-out forwards`;
                                    
                                    this.style.position = 'relative';
                                    this.appendChild(sparkle);
                                    
                                    setTimeout(() => sparkle.remove(), 1500);
                                }, i * 100);
                            }
                        }
                    });
                });

                // Add floating animation keyframes
                if (!document.getElementById('dashboard-animations')) {
                    const style = document.createElement('style');
                    style.id = 'dashboard-animations';
                    style.textContent = `
                        @keyframes sparkleFloat {
                            0% { 
                                opacity: 0; 
                                transform: translateY(0) scale(0); 
                            }
                            50% { 
                                opacity: 1; 
                                transform: translateY(-20px) scale(1); 
                            }
                            100% { 
                                opacity: 0; 
                                transform: translateY(-40px) scale(0.5); 
                            }
                        }
                        
                        /* Enhanced responsive adjustments */
                        @media (max-width: 640px) {
                            .hero-content h1 {
                                background-size: 150% 150% !important;
                            }
                            
                            .card-title {
                                font-size: 1rem !important;
                            }
                            
                            .text-8xl {
                                font-size: 4rem !important;
                            }
                        }
                    `;
                    document.head.appendChild(style);
                }
                
                // Handle window resize for chart responsiveness
                let resizeTimeout;
                window.addEventListener('resize', function() {
                    clearTimeout(resizeTimeout);
                    resizeTimeout = setTimeout(function() {
                        // Charts will auto-resize due to responsive: true option
                    }, 100);
                });
            }
        };
        
        // Global functions for dashboard
        window.exploreFeatures = function() {
            DashboardUtils.exploreFeatures();
        };
        
        window.scrollToStats = function() {
            DashboardUtils.exploreFeatures();
        };
        
        // Initialize animations when page loads
        document.addEventListener('DOMContentLoaded', function() {
            // Animate stats counters if they exist
            document.querySelectorAll('[data-counter]').forEach(counter => {
                const target = parseInt(counter.dataset.counter);
                AnimationUtils.animateCounter(counter, target);
            });
            
            // Add hover effects to cards
            document.querySelectorAll('.card').forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.classList.add('hover-lift');
                });
                card.addEventListener('mouseleave', function() {
                    this.classList.remove('hover-lift');
                });
            });

            // Add ripple effect to buttons
            document.querySelectorAll('.btn').forEach(button => {
                button.addEventListener('click', function(e) {
                    AnimationUtils.addRippleEffect(this, e);
                });
            });
        });
    </script>
</head>
<body class="bg-base-200">

<nav class="navbar bg-base-100 shadow-lg sticky top-0 z-50 border-b border-[oklch(85%_0_0)]">
    <div class="navbar-start">
        <!-- Mobile menu button -->
        <div class="dropdown">
            <div tabindex="0" role="button" class="btn btn-ghost lg:hidden">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                </svg>
            </div>
            <ul tabindex="0" class="menu menu-sm dropdown-content mt-3 z-[1] p-2 shadow bg-base-100 rounded-box w-52">
                <li><a href="dashboard.php" class="<?php echo ($activePage == 'dashboard') ? 'active' : ''; ?>">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5a2 2 0 012-2h4a2 2 0 012 2v3H8V5z"></path>
                    </svg>
                    Dashboard
                </a></li>
                <li><a href="my_courses.php" class="<?php echo ($activePage == 'my_courses') ? 'active' : ''; ?>">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                    Praktikum Saya
                </a></li>
                <li><a href="courses.php" class="<?php echo ($activePage == 'courses') ? 'active' : ''; ?>">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    Cari Praktikum
                </a></li>
            </ul>
        </div>

        <!-- Brand logo and name -->
        <div class="flex items-center ml-2 lg:ml-0">
            <svg class="w-8 h-8 text-[oklch(42%_0.199_265.638)] mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a.999.999 0 01.356-.257l4-1.714a1 1 0 11.788 1.838L7.667 9.088l1.94.831a1 1 0 00.787 0l7-3a1 1 0 000-1.838l-7-3zM3.31 9.397L5 10.12v4.102a8.969 8.969 0 00-1.05-.174 1 1 0 01-.89-.89 11.115 11.115 0 01.25-3.762zM9.3 16.573A9.026 9.026 0 007 14.935v-3.957l1.818.78a3 3 0 002.364 0l5.508-2.361a11.026 11.026 0 01.25 3.762 1 1 0 01-.89.89 8.968 8.968 0 00-5.35 2.524 1 1 0 01-1.4 0zM6 18a1 1 0 001-1v-2.065a8.935 8.935 0 00-2-.712V17a1 1 0 001 1z"></path>
            </svg>
            <span class="text-xl lg:text-2xl font-bold text-[oklch(30%_0.199_265.638)]">SIMPRAK</span>
        </div>
    </div>
    
    <!-- Desktop menu in center -->
    <div class="navbar-center hidden lg:flex">
        <ul class="menu menu-horizontal px-1 space-x-2">
            <li>
                <a href="dashboard.php" class="<?php echo ($activePage == 'dashboard') ? 'active' : ''; ?> flex items-center gap-2 px-4 py-2 rounded-lg transition-all duration-300">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5a2 2 0 012-2h4a2 2 0 012 2v3H8V5z"></path>
                    </svg>
                    Dashboard
                </a>
            </li>
            <li>
                <a href="my_courses.php" class="<?php echo ($activePage == 'my_courses') ? 'active' : ''; ?> flex items-center gap-2 px-4 py-2 rounded-lg transition-all duration-300">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                    Praktikum Saya
                </a>
            </li>
            <li>
                <a href="courses.php" class="<?php echo ($activePage == 'courses') ? 'active' : ''; ?> flex items-center gap-2 px-4 py-2 rounded-lg transition-all duration-300">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    Cari Praktikum
                </a>
            </li>
        </ul>
    </div>
    
    <!-- Profile dropdown on the right -->
    <div class="navbar-end">
        <div class="dropdown dropdown-end">
            <div tabindex="0" role="button" class="btn btn-ghost btn-circle avatar">
                <div class="w-10 rounded-full ring-2 ring-[oklch(42%_0.199_265.638)] ring-offset-2">
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($mahasiswaName); ?>&background=42659B&color=fff" alt="Avatar" />
                </div>
            </div>
            <ul tabindex="0" class="menu menu-sm dropdown-content mt-3 z-[1] p-2 shadow bg-base-100 rounded-box w-52">
                <li class="menu-title">
                    <span class="text-[oklch(42%_0.199_265.638)] font-semibold"><?php echo htmlspecialchars($mahasiswaName); ?></span>
                </li>
                <li><a class="text-xs opacity-60">Mahasiswa - SIMPRAK</a></li>
                <li><hr class="my-2"></li>
                <li>
                    <a href="../logout.php" class="text-red-500 hover:bg-red-50">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                        Logout
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- Main Content Container with Responsive Padding and Better Spacing -->
<main class="min-h-screen bg-base-200">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 xl:px-12 2xl:px-16 py-6 lg:py-8 max-w-7xl">
