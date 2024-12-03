<?php
session_start();
if (!isset($_SESSION['session_username'])) {
    header('location:login.php');
    exit();
}

include("koneksi.php");

// Function to fetch book by ID
function getBookById($koneksi, $BukuID) {
    $query = "SELECT judul, File FROM buku WHERE BukuID = ?";
    $stmt = mysqli_prepare($koneksi, $query);
    mysqli_stmt_bind_param($stmt, 'i', $BukuID);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_assoc($result);
}

// Check if a book ID is provided
if (isset($_GET['id'])) {
    $BukuID = $_GET['id'];
    $book = getBookById($koneksi, $BukuID);

    if (!$book) {
        echo "Buku tidak ditemukan.";
        exit();
    }
} else {
    echo "ID Buku tidak ditemukan.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <title>Baca Buku - <?php echo htmlspecialchars($book['judul']); ?></title>
</head>
<body class="bg-gray-100">
    <?php include 'navbar.php'; ?>
    <div class="container mx-auto py-10 px-4">
        <h1 class="text-3xl font-bold mb-6">Baca Buku: <span class="text-blue-500"><?php echo htmlspecialchars($book['judul']); ?></span></h1>

        <!-- Display Book PDF Link -->
        <div class="bg-white shadow-md rounded-lg p-6 mb-6">
            <?php
            if ($book['File']) {
                // Menampilkan link langsung untuk membuka file PDF
                echo '<a href="' . htmlspecialchars($book['File']) . '" target="_blank" class="text-blue-500 underline">Klik di sini untuk membaca buku PDF</a>';
            } else {
                echo '<p>Buku ini tidak memiliki file untuk dibaca.</p>';
            }
            ?>
        </div>

        <!-- Back Button -->
        <a href="detailbuku.php?id=<?php echo $BukuID; ?>" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">Kembali</a>
    </div>
</body>
</html>
