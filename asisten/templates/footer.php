</main>
        </div>        <div class="drawer-side">
            <label for="my-drawer-2" aria-label="close sidebar" class="drawer-overlay"></label> 
            
            <div class="menu p-6 w-80 min-h-full bg-gradient-to-b from-[oklch(42%_0.199_265.638)] to-[oklch(28%_0.091_267.935)] text-[oklch(100%_0_0)] flex flex-col shadow-2xl border-r border-[oklch(42%_0.199_265.638)]">
                <!-- Header Profile Section -->
                <div class="relative p-6 mb-6 bg-gradient-to-br from-[oklch(28%_0.091_267.935)]/80 via-[oklch(42%_0.199_265.638)]/40 to-[oklch(28%_0.091_267.935)]/60 rounded-2xl border border-[oklch(70%_0.213_47.604)]/30 backdrop-blur-lg shadow-lg overflow-hidden">
                    <!-- Background Decoration -->
                    <div class="absolute inset-0 bg-gradient-to-r from-[oklch(70%_0.213_47.604)]/5 to-transparent opacity-50"></div>
                    <div class="absolute top-0 right-0 w-32 h-32 bg-[oklch(70%_0.213_47.604)]/10 rounded-full -translate-y-16 translate-x-16 blur-2xl"></div>
                    <div class="absolute bottom-0 left-0 w-24 h-24 bg-[oklch(70%_0.213_47.604)]/5 rounded-full translate-y-12 -translate-x-12 blur-xl"></div>
                    
                    <!-- Profile Content -->
                    <div class="relative z-10 flex items-center group cursor-pointer hover:scale-[1.02] transition-all duration-500">
                        <!-- Avatar with Enhanced Effects -->
                        <div class="relative">
                            <div class="absolute inset-0 bg-[oklch(70%_0.213_47.604)]/20 rounded-full blur-lg group-hover:blur-xl transition-all duration-500"></div>
                            <div class="avatar online relative z-10">
                                <div class="w-16 h-16 rounded-full ring-4 ring-[oklch(70%_0.213_47.604)]/80 ring-offset-4 ring-offset-[oklch(28%_0.091_267.935)] transition-all duration-500 group-hover:ring-[oklch(70%_0.213_47.604)] group-hover:ring-offset-2 group-hover:scale-110 shadow-2xl">
                                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($asistenName); ?>&background=b45309&color=fff&bold=true&format=svg&size=128" class="rounded-full" />
                                </div>
                            </div>
                            <!-- Floating Sparkle Effects -->
                            <div class="absolute -top-1 -right-1 w-3 h-3 bg-[oklch(70%_0.213_47.604)] rounded-full animate-ping opacity-75"></div>
                            <div class="absolute top-2 -left-2 w-2 h-2 bg-[oklch(70%_0.213_47.604)]/60 rounded-full animate-pulse"></div>
                        </div>
                        
                        <!-- User Info with Enhanced Typography -->
                        <div class="ml-6 flex-grow space-y-1">
                            <div class="flex items-center gap-2">
                                <h3 class="font-bold text-xl text-[oklch(100%_0_0)] group-hover:text-[oklch(70%_0.213_47.604)] transition-colors duration-300 leading-tight">
                                    <?php echo htmlspecialchars($asistenName); ?>
                                </h3>
                                <div class="badge badge-sm bg-[oklch(70%_0.213_47.604)]/20 text-[oklch(70%_0.213_47.604)] border-[oklch(70%_0.213_47.604)]/30 font-medium">
                                    Online
                                </div>
                            </div>
                            
                            <div class="flex items-center gap-2 text-[oklch(100%_0_0)]/80 group-hover:text-[oklch(100%_0_0)] transition-colors duration-300">
                                <svg class="w-4 h-4 text-[oklch(70%_0.213_47.604)]" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="text-sm font-medium">Asisten Praktikum</span>
                            </div>
                            
                            <div class="flex items-center gap-2 text-xs text-[oklch(100%_0_0)]/60">
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                                </svg>
                                <span>Aktif sejak <?php echo date('H:i'); ?></span>
                            </div>
                        </div>
                        
                        <!-- Status Indicator -->
                        <div class="flex flex-col items-end space-y-2">
                            <div class="flex items-center gap-1">
                                <div class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></div>
                                <span class="text-xs text-green-400 font-medium">Active</span>
                            </div>
                            <svg class="w-5 h-5 text-[oklch(100%_0_0)]/40 group-hover:text-[oklch(70%_0.213_47.604)] transition-all duration-300 group-hover:rotate-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    
                    <!-- Bottom Accent Line -->
                    <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-transparent via-[oklch(70%_0.213_47.604)]/50 to-transparent"></div>
                </div>

                <!-- Navigation Menu -->
                <ul class="flex-grow space-y-2">
                    <!-- Dashboard Section -->
                    <!-- <li class="menu-title">
                        <span class="text-sm font-bold text-[oklch(70%_0.213_47.604)] flex items-center gap-2">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"></path>
                            </svg>
                            Menu Utama
                        </span>
                    </li> -->
                    <li class="<?php echo ($activePage == 'dashboard') ? 'bg-[oklch(70%_0.213_47.604)]/20 rounded-lg' : ''; ?>">
                        <a href="dashboard.php" class="flex items-center gap-3 p-3 rounded-lg transition-all duration-300 hover:bg-[oklch(70%_0.213_47.604)]/10 hover:translate-x-1 group <?php echo ($activePage == 'dashboard') ? 'text-[oklch(70%_0.213_47.604)] font-semibold bg-[oklch(70%_0.213_47.604)]/10' : 'hover:text-[oklch(70%_0.213_47.604)]'; ?>">
                            <div class="p-2 rounded-lg bg-[oklch(70%_0.213_47.604)]/20 group-hover:bg-[oklch(70%_0.213_47.604)]/30 transition-all duration-300">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                                </svg>
                            </div>
                            <span class="font-medium">Dashboard</span>
                            <?php if ($activePage == 'dashboard'): ?>
                                <div class="ml-auto w-2 h-2 bg-[oklch(70%_0.213_47.604)] rounded-full animate-pulse"></div>
                            <?php endif; ?>
                        </a>
                    </li>
                    
                    <!-- Management Section -->
                    <!-- <li class="menu-title mt-6">
                        <span class="text-sm font-bold text-[oklch(100%_0_0)] flex items-center gap-2">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h4a1 1 0 010 2H6.414l2.293 2.293a1 1 0 01-1.414 1.414L5 6.414V8a1 1 0 01-2 0V4zm9 1a1 1 0 010-2h4a1 1 0 011 1v4a1 1 0 01-2 0V6.414l-2.293 2.293a1 1 0 11-1.414-1.414L13.586 5H12zm-9 7a1 1 0 012 0v1.586l2.293-2.293a1 1 0 111.414 1.414L6.414 15H8a1 1 0 010 2H4a1 1 0 01-1-1v-4zm13-1a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 010-2h1.586l-2.293-2.293a1 1 0 111.414-1.414L15 13.586V12a1 1 0 011-1z" clip-rule="evenodd"></path>
                            </svg>
                            Manajemen
                        </span>
                    </li> -->
                    
                    <li class="<?php echo ($activePage == 'praktikum') ? 'bg-[oklch(100%_0_0)]/20 rounded-lg' : ''; ?>">
                        <a href="praktikum.php" class="flex items-center gap-3 p-3 rounded-lg transition-all duration-300 hover:bg-[oklch(100%_0_0)]/10 hover:translate-x-1 group <?php echo ($activePage == 'praktikum') ? 'text-[oklch(100%_0_0)] font-semibold bg-[oklch(100%_0_0)]/10' : 'hover:text-[oklch(100%_0_0)]'; ?>">
                            <div class="p-2 rounded-lg bg-[oklch(100%_0_0)]/20 group-hover:bg-[oklch(100%_0_0)]/30 transition-all duration-300">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                </svg>
                            </div>
                            <span class="font-medium">Mata Praktikum</span>
                            <?php if ($activePage == 'praktikum'): ?>
                                <div class="ml-auto w-2 h-2 bg-[oklch(100%_0_0)] rounded-full animate-pulse"></div>
                            <?php endif; ?>
                        </a>
                    </li>
                    
                    <li class="<?php echo ($activePage == 'modul') ? 'bg-[oklch(100%_0_0)]/20 rounded-lg' : ''; ?>">
                        <a href="modul.php" class="flex items-center gap-3 p-3 rounded-lg transition-all duration-300 hover:bg-[oklch(100%_0_0)]/10 hover:translate-x-1 group <?php echo ($activePage == 'modul') ? 'text-[oklch(100%_0_0)] font-semibold bg-[oklch(100%_0_0)]/10' : 'hover:text-[oklch(100%_0_0)]'; ?>">
                            <div class="p-2 rounded-lg bg-[oklch(100%_0_0)]/20 group-hover:bg-[oklch(100%_0_0)]/30 transition-all duration-300">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/>
                                </svg>
                            </div>
                            <span class="font-medium">Modul</span>
                            <?php if ($activePage == 'modul'): ?>
                                <div class="ml-auto w-2 h-2 bg-[oklch(100%_0_0)] rounded-full animate-pulse"></div>
                            <?php endif; ?>
                        </a>
                    </li>
                    
                    <li class="<?php echo ($activePage == 'laporan') ? 'bg-[oklch(100%_0_0)]/20 rounded-lg' : ''; ?>">
                        <a href="laporan.php" class="flex items-center gap-3 p-3 rounded-lg transition-all duration-300 hover:bg-[oklch(100%_0_0)]/10 hover:translate-x-1 group <?php echo ($activePage == 'laporan') ? 'text-[oklch(100%_0_0)] font-semibold bg-[oklch(100%_0_0)]/10' : 'hover:text-[oklch(100%_0_0)]'; ?>">
                            <div class="p-2 rounded-lg bg-[oklch(100%_0_0)]/20 group-hover:bg-[oklch(100%_0_0)]/30 transition-all duration-300">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75c0-.231-.035-.454-.1-.664M6.75 7.5h1.5M6.75 12h1.5m6.75 0h1.5m-1.5 3h1.5m-1.5 3h1.5M4.5 6.75h1.5v1.5H4.5v-1.5zM4.5 12h1.5v1.5H4.5v-1.5zM4.5 17.25h1.5v1.5H4.5v-1.5z"/>
                                </svg>
                            </div>
                            <span class="font-medium">Laporan Masuk</span>
                            <?php if ($activePage == 'laporan'): ?>
                                <div class="ml-auto w-2 h-2 bg-[oklch(100%_0_0)] rounded-full animate-pulse"></div>
                            <?php endif; ?>
                        </a>
                    </li>
                    
                    <li class="<?php echo ($activePage == 'pengguna') ? 'bg-[oklch(100%_0_0)]/20 rounded-lg' : ''; ?>">
                        <a href="pengguna.php" class="flex items-center gap-3 p-3 rounded-lg transition-all duration-300 hover:bg-[oklch(100%_0_0)]/10 hover:translate-x-1 group <?php echo ($activePage == 'pengguna') ? 'text-[oklch(100%_0_0)] font-semibold bg-[oklch(100%_0_0)]/10' : 'hover:text-[oklch(100%_0_0)]'; ?>">
                            <div class="p-2 rounded-lg bg-[oklch(100%_0_0)]/20 group-hover:bg-[oklch(100%_0_0)]/30 transition-all duration-300">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
                                </svg>
                            </div>
                            <span class="font-medium">Akun Pengguna</span>
                            <?php if ($activePage == 'pengguna'): ?>
                                <div class="ml-auto w-2 h-2 bg-[oklch(100%_0_0)] rounded-full animate-pulse"></div>
                            <?php endif; ?>
                        </a>
                    </li>
                </ul>

                <!-- Footer & Logout -->
                    <!-- Logout Button -->
                    <a href="../logout.php" class="flex items-center gap-3 p-3 rounded-lg transition-all duration-300 hover:bg-red-500/20 hover:translate-x-1 group text-red-400 hover:text-red-300">
                        <div class="p-2 rounded-lg bg-red-500/20 group-hover:bg-red-500/30 transition-all duration-300">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                        </div>
                        <span class="font-medium">Logout</span>
                    </a>
                    
                    <!-- Brand Footer -->
                    <div class="text-center p-4 bg-[oklch(28%_0.091_267.935)]/30 rounded-lg backdrop-blur-sm">
                        <div class="flex items-center justify-center gap-2 mb-2">
                            <svg class="w-5 h-5 text-[oklch(70%_0.213_47.604)]" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <span class="font-bold text-sm text-[oklch(70%_0.213_47.604)]">SIMPRAK</span>
                        </div>
                        <p class="text-xs opacity-60 font-medium text-[oklch(100%_0_0)]">&copy; <?php echo date('Y'); ?> Sistem Pengumpulan Tugas</p>
                        <div class="mt-2 flex justify-center">
                            <div class="badge bg-[oklch(70%_0.213_47.604)] text-[oklch(100%_0_0)] badge-sm border-0">v2.0</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>