<?php
include("koneksi.php");
session_start();

if (isset($_POST["tambahuser"])) {
    $username = $_POST["username"];
    $password = $_POST["password"];
    $email = $_POST["email"];
    $NamaLengkap = $_POST["NamaLengkap"];
    $alamat = $_POST["alamat"];
    $role = $_POST["role"];
    
    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Query untuk memasukkan data
    $sql_insert = "INSERT INTO user (username, password, email, NamaLengkap, alamat, role) 
        VALUES ('$username', '$hashed_password', '$email', '$NamaLengkap', '$alamat', '$role')";
    
    // Eksekusi query
    $_SESSION['session_username'] = $username;
    $_SESSION['session_role'] = $role;

    if (mysqli_query($koneksi, $sql_insert)) {
        echo "Registrasi berhasil!";
        // Redirect to dashboard after successful registration
        header('Location: dashboard.php');
        exit();
    } else {
        echo "Error: " . $sql_insert . "<br>" . mysqli_error($koneksi);
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Perpustakaan</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gradient-to-r from-blue-400 to-purple-400 flex justify-center items-center h-screen">
    <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-md">
        <h2 class="text-2xl font-semibold text-blue-600 mb-6 text-center">Daftar Akun</h2>
        <form action="" method="post">
            <div class="mb-4">
                <label for="username" class="block text-gray-700 mb-1">Username:</label>
                <input type="text" name="username" placeholder="Masukkan Username" class="w-full p-2 bg-gray-100 text-gray-800 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-400" required>
            </div>
            <div class="mb-4">
                <label for="email" class="block text-gray-700 mb-1">Email:</label>
                <input type="email" name="email" placeholder="Masukkan Email" class="w-full p-2 bg-gray-100 text-gray-800 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-400" required>
            </div>
            <div class="mb-4">
                <label for="NamaLengkap" class="block text-gray-700 mb-1">Nama Lengkap:</label>
                <input type="text" name="NamaLengkap" placeholder="Masukkan Nama Lengkap" class="w-full p-2 bg-gray-100 text-gray-800 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-400" required>
            </div>
            <div class="mb-4">
                <label for="alamat" class="block text-gray-700 mb-1">Alamat:</label>
                <input type="text" name="alamat" placeholder="Masukkan Alamat" class="w-full p-2 bg-gray-100 text-gray-800 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-400" required>
            </div>
            <input type="hidden" name="role" value="Peminjam">
            <div class="mb-4">
                <label for="password" class="block text-gray-700 mb-1">Password:</label>
                <input type="password" name="password" placeholder="Masukkan Password" class="w-full p-2 bg-gray-100 text-gray-800 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-400" required>
            </div>
            <button type="submit" name="tambahuser" class="w-full bg-blue-500 text-white py-2 rounded hover:bg-blue-600 transition duration-200">Daftar</button>
            <div class="mt-4 text-center">
                <a href="login.php" class="text-blue-500 hover:underline">Sudah punya akun? Kembali ke login</a>
            </div>
        </form>
    </div>
</body>
</html>
