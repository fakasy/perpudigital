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

