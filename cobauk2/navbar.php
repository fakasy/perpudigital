<style>
    @font-face {
        font-family: 'Vermin Vibes';
        src: url('fonts/vermin_vibes.ttf') format('truetype');
        font-weight: normal;
        font-style: normal;
    }

    /* Gunakan font di seluruh halaman */
    w {
        font-family: 'Vermin Vibes', sans-serif;
    }
</style>
<nav class="bg-white shadow-md">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <!-- Logo Section -->
            <div class="flex-shrink-0 flex items-center">
                <w><a href="#" class="text-2xl font-bold text-gray-900">Myperpus</a></w>
            </div>

            <!-- Navigation Links -->
            <div class="hidden sm:flex sm:space-x-8 items-center">
                <a href="dashboard.php" class="text-gray-900 hover:text-blue-600 px-3 py-2 text-sm font-medium">Home</a>
                <a href="koleksi.php" class="text-gray-900 hover:text-blue-600 px-3 py-2 text-sm font-medium">Koleksi</a>
                <a href="pinjam.php" class="text-gray-900 hover:text-blue-600 px-3 py-2 text-sm font-medium">Dipinjam</a>
            </div>
            
            <!-- Log Out Button -->
            <div class="flex items-center">
                <a href="logout.php" class="bg-gradient-to-r from-blue-400 to-purple-400 border rounded-lg text-white hover:text-white-800 px-3 py-2 text-sm font-medium">Log Out</a>
            </div>
        </div>
    </div>
</nav>
