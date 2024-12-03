<?php
// Include your database connection file
include 'koneksi.php';

// Get filter date if exists
$filterDate = isset($_POST['filterDate']) ? $_POST['filterDate'] : '';

// Prepare the query to fetch borrowing history
$query = "SELECT * FROM peminjaman" . ($filterDate ? " WHERE TanggalPeminjaman = '$filterDate'" : "");
$result = mysqli_query($koneksi, $query);

// Check for results
if (!$result) {
    die('Query Failed: ' . mysqli_error($koneksi));
}

// Set header for PDF output
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="laporan_peminjaman.pdf"');

// Start output buffering
ob_start();

// Create PDF content header
$pdf = "%PDF-1.4\n";
$pdf .= "1 0 obj\n<< /Title (Laporan Peminjaman Buku) /Author (Your Name) >>\nendobj\n";
$pdf .= "2 0 obj\n<< /Type /Page /MediaBox [0 0 612 792] /Contents 3 0 R >>\nendobj\n";
$pdf .= "3 0 obj\n<< /Length 150 >>\nstream\n";

// Write title
$pdf .= "BT\n/F1 24 Tf\n72 720 Td\n(Laporan Peminjaman Buku) Tj\n";
$pdf .= "0 -30 Td\n(Filtered Date: " . ($filterDate ? $filterDate : "All") . ") Tj\n";

// Set up a basic table structure
$pdf .= "0 -30 Td\n";
$pdf .= "(PeminjamanID) Tj\n";
$pdf .= "100 0 Td\n(UserID) Tj\n";
$pdf .= "200 0 Td\n(BukuID) Tj\n";
$pdf .= "300 0 Td\n(Tanggal Peminjaman) Tj\n";
$pdf .= "400 0 Td\n(Tanggal Pengembalian) Tj\n";
$pdf .= "500 0 Td\n(Status) Tj\n";
$pdf .= "0 -10 Td\n";

// Fetch data and add to PDF
while ($row = mysqli_fetch_assoc($result)) {
    $pdf .= "0 -15 Td\n";
    $pdf .= "(" . $row['PeminjamanID'] . ") Tj\n";
    $pdf .= "100 0 Td\n(" . $row['UserID'] . ") Tj\n";
    $pdf .= "200 0 Td\n(" . $row['BukuID'] . ") Tj\n";
    $pdf .= "300 0 Td\n(" . $row['TanggalPeminjaman'] . ") Tj\n";
    $pdf .= "400 0 Td\n(" . $row['TanggalPengembalian'] . ") Tj\n";
    $pdf .= "500 0 Td\n(" . $row['StatusPeminjaman'] . ") Tj\n";
}

// End the PDF stream
$pdf .= "ET\nendstream\nendobj\n";
$pdf .= "4 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n";
$pdf .= "xref\n0 5\n0000000000 65535 f \n0000000010 00000 n \n0000000077 00000 n \n0000000170 00000 n \n0000000250 00000 n \n";
$pdf .= "trailer\n<< /Size 5 /Root 4 0 R >>\n";
$pdf .= "%%EOF";

// Output the PDF content
echo $pdf;

// Flush output buffer and send it to the browser
ob_end_flush();

// Close database connection
mysqli_close($koneksi);
?>
