<?php
session_start();
if (!isset($_SESSION['session_username'])) {
    header('location:login.php');
    exit();
}

include("koneksi.php");

$username = $_SESSION['session_username'];
$role = $_SESSION['session_role'];

// Fetch the UserID based on the session username
$user_query = "SELECT UserID FROM user WHERE username = '$username'";
$user_result = mysqli_query($koneksi, $user_query);
$user = mysqli_fetch_assoc($user_result);
$UserID = $user['UserID']; // This is the actual UserID

// Fetch book details with status
if (isset($_GET['id'])) {
    $BukuID = $_GET['id'];

    // Query to fetch book details
    $query = "
        SELECT buku.*, peminjaman.StatusPeminjaman, peminjaman.UserID 
        FROM buku 
        LEFT JOIN peminjaman ON buku.BukuID = peminjaman.BukuID 
        WHERE buku.BukuID = '$BukuID'
        ORDER BY peminjaman.TanggalPeminjaman DESC 
        LIMIT 1";
    $result = mysqli_query($koneksi, $query);
    $book = mysqli_fetch_assoc($result);

    // Query to fetch reviews related to this book
    $review_query = "
        SELECT ulasanbuku.*, user.username 
        FROM ulasanbuku
        JOIN user ON ulasanbuku.UserID = user.UserID
        WHERE ulasanbuku.BukuID = '$BukuID'";
    $review_result = mysqli_query($koneksi, $review_query);
} else {
    echo "No book selected.";
    exit();
}

// Handle delete review request
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'hapus_ulasan') {
    $UlasanID = $_POST['UlasanID'];

    // Delete the review only if it belongs to the logged-in user
    $delete_query = "DELETE FROM ulasanbuku WHERE UlasanID = '$UlasanID' AND UserID = '$UserID'";
    mysqli_query($koneksi, $delete_query);

    if (mysqli_affected_rows($koneksi) > 0) {
        echo "<script>alert('Ulasan berhasil dihapus!'); window.location='dashboard.php';</script>";
    } else {
        echo "<script>alert('Gagal menghapus ulasan.');</script>";
    }
}



// Handle pinjam request
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'pinjam') {
    if ($role == 'Peminjam') {
        // Check if the book is already borrowed by the user
        $check_query = "SELECT * FROM peminjaman WHERE UserID = '$UserID' AND BukuID = '$BukuID' AND StatusPeminjaman = 'dipinjam'";
        $check_result = mysqli_query($koneksi, $check_query);

        if (mysqli_num_rows($check_result) > 0) {
            echo "<script>alert('Anda sudah meminjam buku ini!');</script>";
        } else {
            $TanggalPeminjaman = date('Y-m-d');
            $TanggalPengembalian = date('Y-m-d', strtotime('+14 days'));

            // Insert into the peminjaman table with the correct UserID
            $pinjam_query = "INSERT INTO peminjaman (UserID, BukuID, TanggalPeminjaman, TanggalPengembalian, StatusPeminjaman) 
                            VALUES ('$UserID', '$BukuID', '$TanggalPeminjaman', '$TanggalPengembalian', 'dipinjam')";
            mysqli_query($koneksi, $pinjam_query);

            echo "<script>alert('Buku berhasil dipinjam!'); window.location='dashboard.php';</script>";
        }
    } else {
        echo "<script>alert('Hanya peminjam yang dapat meminjam buku.');</script>";
    }
}

// Handle return book request
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'kembalikan') {
    $TanggalPengembalian = date('Y-m-d');

    // Update the peminjaman status to 'dikembalikan'
    $return_query = "UPDATE peminjaman 
                    SET StatusPeminjaman = 'dikembalikan', TanggalPengembalian = '$TanggalPengembalian' 
                    WHERE BukuID = '$BukuID' AND UserID = '$UserID' AND StatusPeminjaman = 'dipinjam'";
    $update_result = mysqli_query($koneksi, $return_query);

    // Check if the update was successful
    if (mysqli_affected_rows($koneksi) > 0) {
        echo "<script>alert('Buku berhasil dikembalikan!'); window.location='dashboard.php';</script>";
    } else {
        echo "<script>alert('Gagal mengembalikan buku. Pastikan Anda meminjam buku ini.');</script>";
    }
}

// Handle review submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'review') {
    $ulasan = $_POST['ulasan'];
    $rating = $_POST['rating'];

    $review_query = "INSERT INTO ulasanbuku (UserID, BukuID, ulasan, rating) 
                    VALUES ('$UserID', '$BukuID', '$ulasan', '$rating')";
    mysqli_query($koneksi, $review_query);

    echo "<script>alert('Ulasan berhasil ditambahkan!'); window.location='dashboard.php';</script>";
}

// Handle koleksi (add to personal collection)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'koleksi') {
    // Check if the book is already in the user's collection
    $check_koleksi_query = "SELECT * FROM koleksipribadi WHERE UserID = '$UserID' AND BukuID = '$BukuID'";
    $check_koleksi_result = mysqli_query($koneksi, $check_koleksi_query);

    if (mysqli_num_rows($check_koleksi_result) > 0) {
        echo "<script>alert('Buku sudah ada di koleksi Anda!');</script>";
    } else {
        $koleksi_query = "INSERT INTO koleksipribadi (UserID, BukuID) VALUES ('$UserID', '$BukuID')";
        mysqli_query($koneksi, $koleksi_query);

        echo "<script>alert('Buku berhasil ditambahkan ke koleksi!'); window.location='dashboard.php';</script>";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <title>Detail Buku - <?php echo htmlspecialchars($book['judul']); ?></title>
</head>
<body class="bg-gray-100">
    <?php include 'navbar.php'; ?>
    <div class="container mx-auto py-10 px-4">

        <h1 class="text-3xl font-bold mb-6">Detail untuk Buku: </h1>

        <!-- Book Details -->
        <div class="bg-gradient-to-r from-blue-400 to-purple-400 text-white shadow-md rounded-lg p-6 mb-6">
            <h2 class="text-2xl font-bold mb-4"><?php echo htmlspecialchars($book['judul']); ?></h2>
            <p><strong>Penulis:</strong> <?php echo htmlspecialchars($book['penulis']); ?></p>
            <p><strong>Tahun Terbit:</strong> <?php echo htmlspecialchars($book['TahunTerbit']); ?></p>
            <p><strong>Penerbit:</strong> <?php echo htmlspecialchars($book['penerbit']); ?></p>
            <p><strong>Status:</strong> 
            <?php echo isset($book['StatusPeminjaman']) ? htmlspecialchars($book['StatusPeminjaman']) : 'Tersedia'; ?>
            </p>
        </div>

        <!-- Action Buttons -->
        <div class="space-x-4 mb-6">
            <!-- Pinjam Buku Button (only for Peminjam role) -->
            <?php if ($role == 'Peminjam'): ?>
                <form method="post" class="inline-block">
                    <input type="hidden" name="action" value="pinjam">
                    <button type="submit" class="bg-yellow-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded" 
                    <?php echo ($book['StatusPeminjaman'] == 'dipinjam') ? 'disabled' : ''; ?>>Pinjam Buku</button>
                </form>
            <?php endif; ?>

            <!-- Kembalikan Buku Button (only if the book is borrowed) -->
            <?php if ($role == 'Peminjam' && $book['StatusPeminjaman'] == 'dipinjam'): ?>
                <form method="post" class="inline-block">
                    <input type="hidden" name="action" value="kembalikan">
                    <button type="submit" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">Kembalikan Buku</button>
                </form>
            <?php endif; ?>

            <!-- Add to Koleksi -->
            <form method="post" class="inline-block">
                <input type="hidden" name="action" value="koleksi">
                <button type="submit" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">Tambahkan ke Koleksi</button>
            </form>

            <!-- Baca Buku Button -->
            <?php if ($book['StatusPeminjaman'] == 'dipinjam'): ?>
                <a href="bacabuku.php?id=<?php echo $BukuID; ?>" class="bg-black hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Baca Buku</a>
            <?php else: ?>
                <button class="bg-blue-300 text-white font-bold py-2 px-4 rounded cursor-not-allowed" disabled>Baca Buku (Pinjam Dulu)</button>
            <?php endif; ?>
        </div>

        <!-- Review Section -->
        <h2 class="text-xl font-bold mb-4">Ulasan Buku</h2>
        <form method="post" class="mb-6">
            <input type="hidden" name="action" value="review">
            <textarea name="ulasan" class="border rounded p-2 w-full mb-4" placeholder="Tulis ulasan di sini..." required></textarea>
            <select name="rating" class="border rounded p-2 mb-4" required>
                <option value="">Pilih Rating</option>
                <option value="1">1</option>
                <option value="2">2</option>
                <option value="3">3</option>
                <option value="4">4</option>
                <option value="5">5</option>
            </select>
            <button type="submit" class="bg-gradient-to-r from-blue-400 to-purple-400 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Kirim Ulasan</button>
        </form>

        <!-- Display Reviews -->
        <?php if (mysqli_num_rows($review_result) > 0): ?>
            <h3 class="text-lg font-bold mb-2">Daftar Ulasan:</h3>
            <?php while ($review = mysqli_fetch_assoc($review_result)): ?>
                <div class="relative bg-gray-200 p-4 rounded mb-4">
                    <p><strong><?php echo htmlspecialchars($review['username']); ?></strong> (Rating: <?php echo htmlspecialchars($review['rating']); ?>)</p>
                    <p><?php echo htmlspecialchars($review['ulasan']); ?></p>

                    <!-- Only show the delete button if the review belongs to the logged-in user -->
                    <?php if ($review['UserID'] == $UserID): ?>
                        <form method="post" class="absolute top-0 right-0 m-2">
                            <input type="hidden" name="action" value="hapus_ulasan">
                            <input type="hidden" name="UlasanID" value="<?php echo $review['UlasanID']; ?>">
                            <button type="submit" class="bg-gradient-to-r from-blue-400 to-purple-400 hover:bg-red-700 text-white font-bold py-1 px-3 rounded">Hapus</button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>Tidak ada ulasan untuk buku ini.</p>
        <?php endif; ?>
    </div>
</body>

</html>

