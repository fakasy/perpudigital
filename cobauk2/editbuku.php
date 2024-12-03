<?php
// Include the database connection file
session_start();
if (!isset($_SESSION['session_username'])) {
    header('location:login.php');
    exit();
}
include 'koneksi.php';

// Function to get categories
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

// Check if the form is submitted
if (isset($_POST['update'])) {
    $BukuId = $_POST['BukuId'];
    $judul = $_POST['judul'];
    $penulis = $_POST['penulis'];
    $penerbit = $_POST['penerbit'];
    $tahunterbit = $_POST['tahunterbit'];
    $KategoriID = $_POST['KategoriID'];

    // Fetch existing image and file paths
    $stmt = mysqli_prepare($koneksi, "SELECT Image, File FROM buku WHERE Bukuid = ?");
    mysqli_stmt_bind_param($stmt, 'i', $BukuId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $existingImagePath, $existingFilePath);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);

    // Handle image upload
    if (!empty($_FILES['image']['name'])) {
        $imagePath = 'uploads/' . basename($_FILES['image']['name']);
        move_uploaded_file($_FILES['image']['tmp_name'], $imagePath);
    } else {
        $imagePath = $existingImagePath; // Retain existing image if no new upload
    }

    // Handle PDF file upload
    if (!empty($_FILES['file']['name'])) {
        $fileType = mime_content_type($_FILES['file']['tmp_name']);
        if ($fileType === 'application/pdf') {
            $filePath = 'uploads/' . basename($_FILES['file']['name']);
            move_uploaded_file($_FILES['file']['tmp_name'], $filePath);
        } else {
            echo "Error: Only PDF files are allowed.";
            exit();
        }
    } else {
        $filePath = $existingFilePath; // Retain existing file if no new upload
    }

    // Update book details in the database
    $stmt = mysqli_prepare($koneksi, "UPDATE buku SET judul = ?, penulis = ?, penerbit = ?, tahunterbit = ?, Image = ?, File = ? WHERE Bukuid = ?");
    mysqli_stmt_bind_param($stmt, 'ssssssi', $judul, $penulis, $penerbit, $tahunterbit, $imagePath, $filePath, $BukuId);

    if (mysqli_stmt_execute($stmt)) {
        // Update the category relation
        $relasiStmt = mysqli_prepare($koneksi, "UPDATE kategoribuku_relasi SET KategoriID = ? WHERE BukuID = ?");
        mysqli_stmt_bind_param($relasiStmt, 'ii', $KategoriID, $BukuId);
        
        if (mysqli_stmt_execute($relasiStmt)) {
            echo "Buku dan kategori berhasil diupdate.";
            header("Location: dashboard.php"); // Redirect to the dashboard after update
            exit();
        } else {
            echo "Error updating category: " . mysqli_stmt_error($relasiStmt);
        }
    } else {
        echo "Error updating book: " . mysqli_stmt_error($stmt);
    }

    // Close the statements
    mysqli_stmt_close($stmt);
    mysqli_stmt_close($relasiStmt);
} else {
    // Fetch book details to display in the form
    if (isset($_GET['id'])) {
        $BukuId = $_GET['id'];

        // Fetch book details
        $stmt = mysqli_prepare($koneksi, "SELECT judul, penulis, penerbit, tahunterbit, Image, File FROM buku WHERE Bukuid = ?");
        mysqli_stmt_bind_param($stmt, 'i', $BukuId);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $judul, $penulis, $penerbit, $tahunterbit, $existingImagePath, $existingFilePath);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);

        // Fetch current category relation
        $kategoriStmt = mysqli_prepare($koneksi, "SELECT KategoriID FROM kategoribuku_relasi WHERE BukuID = ?");
        mysqli_stmt_bind_param($kategoriStmt, 'i', $BukuId);
        mysqli_stmt_execute($kategoriStmt);
        mysqli_stmt_bind_result($kategoriStmt, $currentKategoriID);
        mysqli_stmt_fetch($kategoriStmt);
        mysqli_stmt_close($kategoriStmt);
    } else {
        echo "BukuId tidak ditemukan.";
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Buku</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<?php include 'navbar.php'; ?>
<body class="bg-gray-100 min-h-screen  items-center justify-center px-4">
    <div class=" w-full bg-white  rounded-lg shadow-md p-24">
        <h1 class="text-2xl font-bold text-center text-gray-800 mb-6">Edit Buku</h1>

        <form method="POST" action="editbuku.php" enctype="multipart/form-data">
            <input type="hidden" name="BukuId" value="<?php echo htmlspecialchars($BukuId); ?>">

            <div class="mb-4">
                <label for="judul" class="block text-gray-700 text-sm font-bold mb-2">Judul:</label>
                <input type="text" id="judul" name="judul" value="<?php echo htmlspecialchars($judul); ?>" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="mb-4">
                <label for="penulis" class="block text-gray-700 text-sm font-bold mb-2">Penulis:</label>
                <input type="text" id="penulis" name="penulis" value="<?php echo htmlspecialchars($penulis); ?>" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="mb-4">
                <label for="penerbit" class="block text-gray-700 text-sm font-bold mb-2">Penerbit:</label>
                <input type="text" id="penerbit" name="penerbit" value="<?php echo htmlspecialchars($penerbit); ?>" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="mb-6">
                <label for="tahunterbit" class="block text-gray-700 text-sm font-bold mb-2">Tahun Terbit:</label>
                <input type="number" id="tahunterbit" name="tahunterbit" value="<?php echo htmlspecialchars($tahunterbit); ?>" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="mb-4">
                <label for="image" class="block text-gray-700 text-sm font-bold mb-2">Image:</label>
                <input type="file" id="image" name="image" accept="image/*"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="mb-4">
                <label for="file" class="block text-gray-700 text-sm font-bold mb-2">File (PDF):</label>
                <input type="file" id="file" name="file" accept=".pdf"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="mb-6">
                <label for="KategoriID" class="block text-gray-700 text-sm font-bold mb-2">Kategori:</label>
                <select id="KategoriID" name="KategoriID" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <?php
                    $kategoriList = getKategori();
                    foreach ($kategoriList as $kategori) {
                        $selected = ($kategori['KategoriID'] == $currentKategoriID) ? "selected" : "";
                        echo "<option value='{$kategori['KategoriID']}' $selected>{$kategori['NamaKategori']}</option>";
                    }
                    ?>
                </select>
            </div>

            <button type="submit" name="update"
                    class="w-full bg-blue-500 text-white font-bold py-2 rounded-md hover:bg-blue-600 transition duration-200">Update Buku</button>
        </form>
    </div>
</body>
</html>
