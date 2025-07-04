</div> </main> <script>
    // Fungsi untuk menjalankan skrip menu
    function initMobileMenu() {
        const mobileMenuButton = document.getElementById('mobile-menu-button');
        const mobileMenu = document.getElementById('mobile-menu');
        const hamburgerIcon = document.getElementById('hamburger-icon');
        const closeIcon = document.getElementById('close-icon');

        if (mobileMenuButton) {
            mobileMenuButton.addEventListener('click', () => {
                mobileMenu.classList.toggle('hidden');
                hamburgerIcon.classList.toggle('hidden');
                closeIcon.classList.toggle('hidden');
            });
        }
    }

    // Fungsi untuk menjalankan skrip dropdown profil
    function initProfileDropdown() {
        const profileMenuButton = document.getElementById('profile-menu-button');
        const profileDropdown = document.getElementById('profile-dropdown');
        const profileMenuContainer = document.getElementById('profile-menu-container');

        if (profileMenuButton) {
            profileMenuButton.addEventListener('click', (event) => {
                event.stopPropagation();
                profileDropdown.classList.toggle('hidden');
            });

            document.addEventListener('click', (event) => {
                if (profileMenuContainer && !profileMenuContainer.contains(event.target)) {
                    profileDropdown.classList.add('hidden');
                }
            });
        }
    }

    // Inisialisasi Swup.js
    const swup = new Swup();

    // Jalankan skrip menu saat halaman pertama kali dimuat
    document.addEventListener('DOMContentLoaded', () => {
        initMobileMenu();
        initProfileDropdown();
    });

    // Jalankan kembali skrip menu setiap kali Swup selesai memuat halaman baru
    swup.hooks.on('page:view', () => {
        initMobileMenu();
        initProfileDropdown();
    });
</script>

</body>
</html>