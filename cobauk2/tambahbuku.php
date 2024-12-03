<?php
include("koneksi.php");
session_start();
if (!isset($_SESSION['session_username'])) {
    header('location:login.php');
    exit();
}

function getKategori() {
    global $koneksi;
    $query = "SELECT * FROM kategoribuku ORDER BY NamaKategori ASC";
    $result = mysqli_query($koneksi, $query);
    $kategori = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $kategori[] = $row;
    }
    return $kategori;
}

function tambahBuku($judul, $penulis, $penerbit, $TahunTerbit, $KategoriID, $imagePath, $filePath) {
    global $koneksi;
    
    $query = "INSERT INTO buku (judul, penulis, penerbit, TahunTerbit, Image, File) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $koneksi->prepare($query);
    $stmt->bind_param("ssssss", $judul, $penulis, $penerbit, $TahunTerbit, $imagePath, $filePath);
    
    if ($stmt->execute()) {
        $BukuID = $stmt->insert_id; // Mengambil ID buku yang baru ditambahkan
        
        // Menyimpan relasi buku dengan kategori
        $relasiQuery = "INSERT INTO kategoribuku_relasi (BukuID, KategoriID) VALUES (?, ?)";
        $relasiStmt = $koneksi->prepare($relasiQuery);
        $relasiStmt->bind_param("ii", $BukuID, $KategoriID);
        $relasiStmt->execute();
        
        return $BukuID; // Mengembalikan ID buku
    } else {
        return false;
    }
}

if (isset($_POST["tambahBuku"])) {
    $judul = trim($_POST["judul"]);
    $penulis = trim($_POST["penulis"]);
    $penerbit = trim($_POST["penerbit"]);
    $TahunTerbit = intval($_POST["TahunTerbit"]);
    $KategoriID = intval($_POST["KategoriID"]);
    
    // Mengatur path untuk menyimpan gambar dan file
    $imagePath = '';
    $filePath = '';

    // Proses upload gambar
    if ($_FILES['image']['error'] == UPLOAD_ERR_OK) {
        $imageTmpPath = $_FILES['image']['tmp_name'];
        $imageName = $_FILES['image']['name'];
        $imagePath = 'uploads/' . basename($imageName);
        move_uploaded_file($imageTmpPath, $imagePath);
    }

    // Proses upload file PDF
    if ($_FILES['file']['error'] == UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['file']['tmp_name'];
        $fileName = $_FILES['file']['name'];
        $filePath = 'uploads/' . basename($fileName);
        move_uploaded_file($fileTmpPath, $filePath);
    }

    if (empty($judul) || empty($penulis) || empty($penerbit) || $TahunTerbit <= 0 || $KategoriID <= 0) {
        $_SESSION["pesan"] = "Semua field harus diisi dengan benar!";
    } else {
        $result = tambahBuku($judul, $penulis, $penerbit, $TahunTerbit, $KategoriID, $imagePath, $filePath);
        if ($result) {
            $_SESSION["pesan"] = "Buku berhasil ditambahkan dengan ID: " . $result;
        } else {
            $_SESSION["pesan"] = "Gagal menambahkan buku. Silakan coba lagi.";
        }
    }
    
    header("Location: tambahbuku.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Buku Baru</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <?php include('navbar.php'); ?>
    <div class="container mx-auto my-10 p-6 bg-white rounded-lg shadow-lg">
        <h2 class="mb-4 text-center text-2xl font-semibold text-green-600">Tambah Buku Baru</h2>
        <?php
        if (isset($_SESSION["pesan"])) {
            $alertClass = strpos($_SESSION["pesan"], "berhasil") !== false ? "bg-green-500" : "bg-red-500";
            echo "<div class='text-white p-4 mb-4 rounded " . $alertClass . "'>" . $_SESSION["pesan"] . "</div>";
            unset($_SESSION["pesan"]);
        }
        ?>
        <form method="post" enctype="multipart/form-data">
            <div class="mb-4">
                <label for="judul" class="block text-gray-700">Judul:</label>
                <input type="text" class="w-full p-2 border border-gray-300 rounded" id="judul" name="judul" required>
            </div>
            <div class="mb-4">
                <label for="penulis" class="block text-gray-700">Penulis:</label>
                <input type="text" class="w-full p-2 border border-gray-300 rounded" id="penulis" name="penulis" required>
            </div>
            <div class="mb-4">
                <label for="penerbit" class="block text-gray-700">Penerbit:</label>
                <input type="text" class="w-full p-2 border border-gray-300 rounded" id="penerbit" name="penerbit" required>
            </div>
            <div class="mb-4">
                <label for="TahunTerbit" class="block text-gray-700">Tahun Terbit:</label>
                <input type="number" class="w-full p-2 border border-gray-300 rounded" id="TahunTerbit" name="TahunTerbit" required min="1800" max="<?php echo date('Y'); ?>">
            </div>
            <div class="mb-4">
                <label for="KategoriID" class="block text-gray-700">Kategori:</label>
                <select id="KategoriID" name="KategoriID" class="w-full p-2 border border-gray-300 rounded" required>
                    <option value="">Pilih Kategori</option>
                    <?php
                    $kategoriList = getKategori();
                    foreach ($kategoriList as $kategori) {
                        echo "<option value='{$kategori['KategoriID']}'>{$kategori['NamaKategori']}</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="mb-4">
                <label for="image" class="block text-gray-700">Gambar/cover buku:</label>
                <input type="file" class="w-full p-2 border border-gray-300 rounded" id="image" name="image" accept="image/*" required>
            </div>
            <div class="mb-4">
                <label for="file" class="block text-gray-700">File PDF buku:</label>
                <input type="file" class="w-full p-2 border border-gray-300 rounded" id="file" name="file" accept=".pdf" required>
            </div>
            <div class="text-center">
                <button type="submit" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded" name="tambahBuku">Tambah Buku</button>
                <a href="dashboard.php" class="bg-gray-400 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded ml-2">Kembali ke Dashboard</a>
            </div>
        </form>
    </div>
</body>
</html>
