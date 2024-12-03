<?php
session_start();
if (!isset($_SESSION['session_username'])) {
    header('location:login.php');
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Peminjaman Buku</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">

    <?php include 'navbar.php'; ?>

    <div class="container mx-auto mt-10">
        <h1 class="text-3xl font-bold text-center text-gray-800 mb-6">Laporan Peminjaman Buku</h1>

        <form method="POST" class="mb-4">
            <label for="filterDate" class="block text-gray-700">Filter Tanggal:</label>
            <select id="filterDate" name="filterDate" class="border border-gray-300 p-2 rounded">
                <option value="">Semua Historis</option>
                <option value="<?php echo date('Y-m-d'); ?>">Hari Ini</option>
                <option value="<?php echo date('Y-m-d', strtotime('-1 day')); ?>">Kemarin</option>
            </select>
            <button type="submit" class="bg-blue-500 text-white font-bold py-2 px-4 rounded ml-2">Filter</button>
        </form>

        <div class="bg-white shadow-md rounded-lg p-6">
            <h2 class="text-2xl font-bold text-gray-800 mb-4">Data Peminjaman</h2>
            <table class="min-w-full table-auto mb-4">
                <thead>
                    <tr class="bg-gray-200 text-gray-700">
                        <th class="px-4 py-2">PeminjamanID</th>
                        <th class="px-4 py-2">UserID</th>
                        <th class="px-4 py-2">BukuID</th>
                        <th class="px-4 py-2">Tanggal Peminjaman</th>
                        <th class="px-4 py-2">Tanggal Pengembalian</th>
                        <th class="px-4 py-2">Status Peminjaman</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    include 'koneksi.php';

                    // Get the filter date from the form or set to today's date
                    $filterDate = isset($_POST['filterDate']) ? $_POST['filterDate'] : '';

                    // Build the query based on the filter
                    if ($filterDate) {
                        $query = "SELECT * FROM peminjaman WHERE TanggalPeminjaman = '$filterDate'";
                    } else {
                        $query = "SELECT * FROM peminjaman";
                    }
                    $result = mysqli_query($koneksi, $query);

                    if (mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                            echo "<tr class='bg-white border-b'>";
                            echo "<td class='px-4 py-2'>" . $row['PeminjamanID'] . "</td>";
                            echo "<td class='px-4 py-2'>" . $row['UserID'] . "</td>";
                            echo "<td class='px-4 py-2'>" . $row['BukuID'] . "</td>";
                            echo "<td class='px-4 py-2'>" . $row['TanggalPeminjaman'] . "</td>";
                            echo "<td class='px-4 py-2'>" . $row['TanggalPengembalian'] . "</td>";
                            echo "<td class='px-4 py-2'>" . $row['StatusPeminjaman'] . "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='6' class='text-center py-4'>No data available</td></tr>";
                    }

                    mysqli_close($koneksi);
                    ?>
                </tbody>
            </table>
        </div>

    </div>
</body>
</html>
