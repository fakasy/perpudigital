<?php
session_start();
if (!isset($_SESSION['session_username'])) {
    header('location:login.php');
    exit();
}

include("koneksi.php");

$username = $_SESSION['session_username'];

// Fetch the UserID based on the session username
$user_query = "SELECT UserID FROM user WHERE username = '$username'";
$user_result = mysqli_query($koneksi, $user_query);
$user = mysqli_fetch_assoc($user_result);
$UserID = $user['UserID'];

// Fetch books in user's collection
$koleksi_query = "
    SELECT buku.BukuID, buku.judul, buku.penulis, buku.penerbit, buku.TahunTerbit 
    FROM koleksipribadi
    JOIN buku ON koleksipribadi.BukuID = buku.BukuID
    WHERE koleksipribadi.UserID = '$UserID'
";
$koleksi_result = mysqli_query($koneksi, $koleksi_query);

// Handle removal of books from the collection
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'hapus') {
    $BukuID = $_POST['BukuID'];
    $hapus_query = "DELETE FROM koleksipribadi WHERE BukuID = '$BukuID' AND UserID = '$UserID'";
    mysqli_query($koneksi, $hapus_query);

    // Redirect after deletion to avoid resubmitting the form
    echo "<script>alert('Buku berhasil dihapus dari koleksi!'); window.location='koleksi.php';</script>";
}

// Handle pinjam request
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'pinjam') {
    $BukuID = $_POST['BukuID'];
    
    // Check if the book is already borrowed by the user
    $check_borrow_query = "SELECT * FROM peminjaman WHERE UserID = '$UserID' AND BukuID = '$BukuID' AND StatusPeminjaman = 'dipinjam'";
    $check_borrow_result = mysqli_query($koneksi, $check_borrow_query);
    
    if (mysqli_num_rows($check_borrow_result) == 0) {
        $TanggalPeminjaman = date('Y-m-d');
        $TanggalPengembalian = date('Y-m-d', strtotime('+14 days'));

        // Insert into the peminjaman table
        $pinjam_query = "INSERT INTO peminjaman (UserID, BukuID, TanggalPeminjaman, TanggalPengembalian, StatusPeminjaman) 
                        VALUES ('$UserID', '$BukuID', '$TanggalPeminjaman', '$TanggalPengembalian', 'dipinjam')";
        mysqli_query($koneksi, $pinjam_query);

        echo "<script>alert('Buku berhasil dipinjam!'); window.location='koleksi.php';</script>";
    } else {
        echo "<script>alert('Anda sudah meminjam buku ini.'); window.location='koleksi.php';</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <title>Koleksi Buku Saya</title>
</head>
<body class="bg-gray-100">
    <?php include 'navbar.php'; ?>
    <div class="container mx-auto py-10 px-4">
        <h1 class="text-3xl font-bold mb-6">Koleksi Buku Saya</h1>

        <?php if (mysqli_num_rows($koleksi_result) > 0): ?>
            <div class="bg-white shadow-md rounded-lg p-6">
                <table class="min-w-full bg-white border border-gray-300">
                    <thead>
                        <tr>
                            <th class="py-2 px-4 border-b">Judul</th>
                            <th class="py-2 px-4 border-b">Penulis</th>
                            <th class="py-2 px-4 border-b">Penerbit</th>
                            <th class="py-2 px-4 border-b">Tahun Terbit</th>
                            <th class="py-2 px-4 border-b">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($book = mysqli_fetch_assoc($koleksi_result)): ?>
                            <tr>
                                <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($book['judul']); ?></td>
                                <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($book['penulis']); ?></td>
                                <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($book['penerbit']); ?></td>
                                <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($book['TahunTerbit']); ?></td>
                                <td class="py-2 px-4 border-b">
                                    <form method="post" class="inline-block">
                                        <input type="hidden" name="action" value="hapus">
                                        <input type="hidden" name="BukuID" value="<?php echo $book['BukuID']; ?>">
                                        <button type="submit" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">Hapus</button>
                                    </form>
                                    
                                    <?php
                                    // Check if the book is already borrowed by the user
                                    $check_borrow_query = "SELECT * FROM peminjaman WHERE UserID = '$UserID' AND BukuID = '".$book['BukuID']."' AND StatusPeminjaman = 'dipinjam'";
                                    $check_borrow_result = mysqli_query($koneksi, $check_borrow_query);
                                    $is_borrowed = mysqli_num_rows($check_borrow_result) > 0;
                                    ?>

                                    <form method="post" class="inline-block">
                                        <input type="hidden" name="action" value="pinjam">
                                        <input type="hidden" name="BukuID" value="<?php echo $book['BukuID']; ?>">
                                        <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded cursor" <?php echo $is_borrowed ? 'disabled' : ''; ?>>
                                            <?php echo $is_borrowed ? 'Dipinjam' : 'Pinjam'; ?>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="text-gray-700">Anda belum memiliki buku dalam koleksi pribadi.</p>
        <?php endif; ?>
    </div>
</body>
</html>
