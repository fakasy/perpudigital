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
$UserID = $user['UserID'];

// Fetch borrowed books
$query = "
    SELECT peminjaman.*, buku.judul, buku.penulis, buku.File
    FROM peminjaman 
    JOIN buku ON peminjaman.BukuID = buku.BukuID
    WHERE peminjaman.UserID = '$UserID' AND peminjaman.StatusPeminjaman = 'dipinjam'";
$result = mysqli_query($koneksi, $query);

// Handle delete (return) book request
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'hapus') {
    $PeminjamanID = $_POST['PeminjamanID'];

    $delete_query = "UPDATE peminjaman SET StatusPeminjaman = 'dikembalikan', TanggalPengembalian = CURDATE() WHERE PeminjamanID = '$PeminjamanID'";
    mysqli_query($koneksi, $delete_query);

    echo "<script>alert('Buku berhasil dikembalikan!'); window.location='pinjam.php';</script>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <title>Pinjaman Buku</title>
</head>
<body class="bg-gray-100">
    <?php include 'navbar.php'; ?>

    <div class="container mx-auto py-10 px-4">
        <h1 class="text-3xl font-bold mb-6">Buku yang Dipinjam</h1>

        <?php if (mysqli_num_rows($result) > 0): ?>
            <table class="min-w-full bg-white shadow-md rounded-lg">
                <thead>
                    <tr>
                        <th class="px-6 py-3 border-b-2 border-gray-300 text-left text-xs leading-4 font-medium text-gray-700 uppercase tracking-wider">Judul</th>
                        <th class="px-6 py-3 border-b-2 border-gray-300 text-left text-xs leading-4 font-medium text-gray-700 uppercase tracking-wider">Penulis</th>
                        <th class="px-6 py-3 border-b-2 border-gray-300 text-left text-xs leading-4 font-medium text-gray-700 uppercase tracking-wider">Tanggal Peminjaman</th>
                        <th class="px-6 py-3 border-b-2 border-gray-300 text-left text-xs leading-4 font-medium text-gray-700 uppercase tracking-wider">Tanggal Pengembalian</th>
                        <th class="px-6 py-3 border-b-2 border-gray-300 text-left text-xs leading-4 font-medium text-gray-700 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td class="px-6 py-4 border-b border-gray-200"><?php echo htmlspecialchars($row['judul']); ?></td>
                            <td class="px-6 py-4 border-b border-gray-200"><?php echo htmlspecialchars($row['penulis']); ?></td>
                            <td class="px-6 py-4 border-b border-gray-200"><?php echo htmlspecialchars($row['TanggalPeminjaman']); ?></td>
                            <td class="px-6 py-4 border-b border-gray-200">
                                <?php echo $row['TanggalPengembalian'] ? htmlspecialchars($row['TanggalPengembalian']) : '-'; ?>
                            </td>
                            <td class="px-6 py-4 border-b border-gray-200">
                                <form method="post" class="inline-block">
                                    <input type="hidden" name="PeminjamanID" value="<?php echo $row['PeminjamanID']; ?>">
                                    <input type="hidden" name="action" value="hapus">
                                    <button type="submit" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">Kembalikan</button>
                                </form>
                                <?php if ($row['File']): ?>
                                    <a href="bacabuku.php?id=<?php echo $row['BukuID']; ?>" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Baca</a>
                                <?php else: ?>
                                    <span class="text-gray-500">Tidak ada file</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="text-gray-500">Tidak ada buku yang sedang dipinjam.</p>
        <?php endif; ?>
    </div>
</body>
</html>
