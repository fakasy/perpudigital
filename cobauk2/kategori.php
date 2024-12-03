<?php
session_start();
if (!isset($_SESSION['session_username'])) {
    header('location:login.php');
    exit();
}
include 'koneksi.php';

// Tambah kategori
if (isset($_POST['tambah'])) {
    $namaKategori = $_POST['namaKategori'];
    $sql = "INSERT INTO kategoribuku (NamaKategori) VALUES ('$namaKategori')";
    mysqli_query($koneksi, $sql);
}

// Hapus kategori
if (isset($_GET['hapus'])) {
    $kategoriID = $_GET['hapus'];
    $sql = "DELETE FROM kategoribuku WHERE KategoriID = $kategoriID";
    mysqli_query($koneksi, $sql);
}

// Edit kategori
if (isset($_POST['edit'])) {
    $kategoriID = $_POST['kategoriID'];
    $namaKategori = $_POST['namaKategori'];
    $sql = "UPDATE kategoribuku SET NamaKategori = '$namaKategori' WHERE KategoriID = $kategoriID";
    mysqli_query($koneksi, $sql);
}

// Ambil semua data kategori
$sql = "SELECT * FROM kategoribuku";
$result = mysqli_query($koneksi, $sql);

// Ambil data kategori yang akan diedit (jika ada)
$editData = null;
if (isset($_GET['editKategori'])) {
    $kategoriID = $_GET['editKategori'];
    $editSql = "SELECT * FROM kategoribuku WHERE KategoriID = $kategoriID";
    $editResult = mysqli_query($koneksi, $editSql);
    $editData = mysqli_fetch_assoc($editResult);
}
?>

<!DOCTYPE html>
<lang="en">
    <head>
        <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Kategori Buku</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<?php  include 'navbar.php';  ?>

<body class="bg-gray-100 ">
    <div class="max-w-3xl mx-auto bg-white shadow-md rounded-lg p-8 m-16 items-center justify_center">
        <h1 class="text-2xl font-bold mb-6 text-center">Kelola Kategori Buku</h1>

        <!-- Form Tambah Kategori -->
        <form method="POST" action="" class="mb-4">
            <div class="flex items-center gap-4">
                <input type="text" name="namaKategori" placeholder="Nama Kategori" class="w-full p-2 border border-gray-300 rounded-lg" required>
                <button type="submit" name="tambah" class="bg-green-500 text-white px-4 py-2 rounded-lg">
                    Tambah
                </button>
            </div>
        </form>

        <!-- Form Edit Kategori (disembunyikan jika tidak ada edit kategori) -->
        <?php if ($editData) { ?>
        <form method="POST" action="kategori.php" class="mb-4">
            <input type="hidden" name="kategoriID" value="<?= $editData['KategoriID'] ?>">
            <div class="flex items-center gap-4">
                <input type="text" name="namaKategori" value="<?= $editData['NamaKategori'] ?>" class="w-full p-2 border border-gray-300 rounded-lg" required>
                <button type="submit" name="edit" class="bg-blue-500 text-white px-4 py-2 rounded-lg">
                    Update
                </button>
            </div>
        </form>
        <?php } ?>

        <!-- Tabel Kategori -->
        <table class="table-auto w-full bg-white rounded-lg shadow-lg">
            <thead>
                <tr class="bg-gray-200 text-left">
                    <th class="px-4 py-2 hidden">Kategori ID</th>
                    <th class="px-4 py-2">Nama Kategori</th>
                    <th class="px-4 py-2">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                <tr class="border-t">
                    <td class="px-4 py-2 hidden"><?= $row['KategoriID'] ?></td>
                    <td class="px-4 py-2"><?= $row['NamaKategori'] ?></td>
                    <td class="px-4 py-2">
                        <a href="kategori.php?editKategori=<?= $row['KategoriID'] ?>" class="bg-blue-500 text-white px-3 py-1 rounded-lg mr-2">Edit</a>
                        <a href="kategori.php?hapus=<?= $row['KategoriID'] ?>" class="bg-red-500 text-white px-3 py-1 rounded-lg" onclick="return confirm('Yakin ingin menghapus kategori ini?')">Hapus</a>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</body>
</html>
