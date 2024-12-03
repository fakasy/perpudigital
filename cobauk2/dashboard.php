<?php
session_start();
if (!isset($_SESSION["session_username"])) {
    header("location:login.php");
    exit();
}

include("koneksi.php");

$username = $_SESSION['session_username'];
$role = $_SESSION['session_role'];

// Handle delete request for admins or staff
if (isset($_POST['delete']) && ($role == 'Administrator' || $role == 'Petugas')) {
    $BukuID = $_POST['BukuID'];
    $stmt = mysqli_prepare($koneksi, "DELETE FROM buku WHERE BukuID = ?");
    mysqli_stmt_bind_param($stmt, 'i', $BukuID);
    
    if (mysqli_stmt_execute($stmt)) {
        header("Location: dashboard.php");
        exit();
    } else {
        echo "Error: " . mysqli_error($koneksi);
    }
    mysqli_stmt_close($stmt);
}

// Fetch categories for the search filter
$queryKategori = "SELECT * FROM kategoribuku ORDER BY NamaKategori ASC";
$resultKategori = mysqli_query($koneksi, $queryKategori);

// Handle search query
$search = "";
$category = "";

if (isset($_GET['search'])) {
    $search = mysqli_real_escape_string($koneksi, $_GET['search']);
}

if (isset($_GET['category'])) {
    $category = mysqli_real_escape_string($koneksi, $_GET['category']);
}

// Mengambil data buku dari database, termasuk gambar
$query = "SELECT b.*, k.NamaKategori FROM buku b
        LEFT JOIN kategoribuku_relasi kr ON b.BukuID = kr.BukuID
        LEFT JOIN kategoribuku k ON kr.KategoriID = k.KategoriID
        WHERE 1=1";

// Tambahkan kondisi pencarian dan kategori
if (!empty($search)) {
    $query .= " AND b.judul LIKE '%$search%'";
}
if (!empty($category)) {
    $query .= " AND k.KategoriID = '$category'";
}

$result = mysqli_query($koneksi, $query);

// Fungsi untuk menampilkan gambar
function tampilkanGambar($imagePath) {
    $defaultImage = 'path/to/default/image.png'; // Ganti dengan path gambar default Anda
    if (!empty($imagePath) && file_exists($imagePath)) {
        return '<img src="' . htmlspecialchars($imagePath) . '" alt="Gambar Buku" class="w-16 h-24 object-cover rounded-lg shadow-md">';
    } else {
        return '<img src="' . htmlspecialchars($defaultImage) . '" alt="Gambar Kosong" class="w-16 h-24 object-cover rounded-lg shadow-md">';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <title>Perpustakaan Online - Dashboard</title>
</head>
<style>
    @font-face {
        font-family: 'Vermin Vibes';
        src: url('fonts/vermin_vibes.ttf') format('truetype');
        font-weight: normal;
        font-style: normal;
    }

    /* Gunakan font di seluruh halaman */
span {
        font-family: 'Vermin Vibes', sans-serif;
        font-size: larger;
    }
</style>
<body class="bg-gray-100">
    
<?php include 'navbar.php'; ?>

<div class="container mx-auto py-10 px-4">
    <h1 class="text-3xl font-bold mb-6">Selamat datang, <span class="text-blue-500"><?php echo htmlspecialchars($username); ?></span> </h1>

    <?php if ($role == 'Administrator' || $role == 'Petugas'): ?>
        <h2 class="text-2xl font-semibold mb-4">Panel</h2>
        <div class="space-x-4 mb-6">
            <a href="tambahbuku.php" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">Tambah Buku Baru</a>
            <a href="lapor.php" class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded">Laporan</a>
            <a href="kategori.php" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Kelola Kategori</a>
            <?php if ($role == 'Administrator'): ?>
                <a href="kelola.php" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Kelola Pengguna</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- Search Form -->
    <form method="GET" action="dashboard.php" class="mb-6">
        <div class="flex items-center space-x-4">
            <input type="text" name="search" placeholder="Cari judul buku..." class="border border-gray-400 rounded-lg px-4 py-2" value="<?php echo htmlspecialchars($search); ?>">
            
            <select name="category" class="border border-gray-400 rounded-lg px-4 py-2">
                <option value="">Pilih Kategori</option>
                <?php while ($rowKategori = mysqli_fetch_assoc($resultKategori)): ?>
                    <option value="<?php echo $rowKategori['KategoriID']; ?>" <?php if ($rowKategori['KategoriID'] == $category) echo 'selected'; ?>>
                        <?php echo htmlspecialchars($rowKategori['NamaKategori']); ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <button type="submit" class="bg-gradient-to-r from-blue-400 to-purple-400 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Cari</button>
        </div>
    </form>

    <h2 class="text-2xl font-semibold mb-4">Daftar Buku Perpustakaan</h2>
    <table class="min-w-full bg-white border border-bg-gradient-to-r from-blue-400 to-purple-400 shadow-md rounded-lg">
        <thead>
            <tr class="bg-gray-800 text-white">
                <th class="py-3 px-6 text-left">Gambar</th>
                <th class="py-3 px-6 text-left">Judul</th>
                <th class="py-3 px-6 text-left">Penulis</th>
                <th class="py-3 px-6 text-left">Tahun Terbit</th>
                <th class="py-3 px-6 text-left">Penerbit</th>
                <th class="py-3 px-6 text-left">Info</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($row = mysqli_fetch_assoc($result)): ?>
            <tr class="border-b hover:bg-gray-100">
                <td class="py-3 px-6">
                    <?php echo tampilkanGambar($row['Image']); ?>
                </td>
                <td class="py-3 px-6"><?php echo htmlspecialchars($row['judul']); ?></td>
                <td class="py-3 px-6"><?php echo htmlspecialchars($row['penulis']); ?></td>
                <td class="py-3 px-6"><?php echo htmlspecialchars($row['TahunTerbit']); ?></td>
                <td class="py-3 px-6"><?php echo htmlspecialchars($row['penerbit']); ?></td>
                <td class="py-3 px-6">
                    <?php if ($role == 'Administrator' || $role == 'Petugas'): ?>
                        <a href="editbuku.php?id=<?php echo $row['BukuID']; ?>" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Edit</a>
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="BukuID" value="<?php echo $row['BukuID']; ?>">
                            <button type="submit" name="delete" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded" onclick="return confirm('Anda yakin ingin menghapus buku ini?');">Hapus</button>
                        </form>
                    <?php elseif ($role == 'Peminjam'): ?>
                        <a href="detailbuku.php?id=<?php echo $row['BukuID']; ?>" class="bg-gradient-to-r from-blue-400 to-purple-400 text-white font-bold py-2 px-4 rounded">Lihat</a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>
</body>
</html>
