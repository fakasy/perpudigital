<?php
session_start();
include("koneksi.php");

// Initialize error variable
$err = "";

// Cek session
if (isset($_SESSION['session_username'])) {
    header("location:dashboard.php");
    exit();
}

// Proses login
if (isset($_POST["Login"])) {
    $username = $_POST["username"];
    $password = $_POST["password"];

    if ($username == '' || $password == '') {
        $err = "Silakan masukkan username dan password.";
    } else {
        $sql1 = "SELECT * FROM user WHERE username = '$username'";
        $q1 = mysqli_query($koneksi, $sql1);
        $r1 = mysqli_fetch_array($q1);
        if ($r1 == NULL) {
            $err = "Username tidak ditemukan.";
        } elseif (!password_verify($password, $r1['password'])) {
            $err = 'Password salah.';
        }

        if (empty($err)) {
            $_SESSION['session_username'] = $username;
            $_SESSION['session_role'] = $r1['role'];
            header('Location: dashboard.php');
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perpustakaan - Login</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gradient-to-r from-blue-400 to-purple-400 h-screen flex justify-center items-center">
    <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-sm">
        <h1 class="text-2xl font-semibold text-center mb-6">Login Perpustakaan</h1>
        <?php if ($err) { ?>
            <div class="bg-red-200 text-red-600 p-2 rounded mb-4">
                <?php echo $err; ?>
            </div>
        <?php } ?>
        <form method="post">
            <div class="mb-4">
                <input type="text" name="username" placeholder="Username" class="w-full border border-gray-300 rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>
            <div class="mb-4">
                <input type="password" name="password" placeholder="Password" class="w-full border border-gray-300 rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>
            <button type="submit" name="Login" class="w-full bg-blue-500 text-white rounded-md p-2 hover:bg-blue-600 transition duration-200">Login</button>
            <p class="mt-4 text-center">
                <a href="regist.php" class="text-blue-500 hover:underline">Klik sini nek ra ndue akun</a>
            </p>
        </form>
    </div>
</body>
</html>
