<?php
session_start();
if (!isset($_SESSION['session_username'])) {
    header('location:login.php');
    exit();
}
include("koneksi.php");

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // Fetch user data
    $query = "SELECT * FROM user WHERE UserID = ?";
    $stmt = $koneksi->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    // Update user
    if (isset($_POST['update'])) {
        $username = $_POST['username'];
        $email = $_POST['email'];
        $nama_lengkap = $_POST['nama_lengkap'];
        $alamat = $_POST['alamat'];
        $role = $_POST['role'];

        $updateQuery = "UPDATE user SET username = ?, email = ?, NamaLengkap = ?, alamat = ?, role = ? WHERE UserID = ?";
        $updateStmt = $koneksi->prepare($updateQuery);
        $updateStmt->bind_param("sssssi", $username, $email, $nama_lengkap, $alamat, $role, $id);
        
        if ($updateStmt->execute()) {
            header("Location: kelola.php");
        } else {
            echo "Error updating user: " . $koneksi->error;
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <title>Document</title>
</head>
<body>
<form method="post" class="max-w-lg mx-auto bg-white p-6 rounded-lg shadow-md">
    <h2 class="text-2xl font-bold mb-6">Edit User</h2>
    <div class="mb-4">
        <label class="block text-gray-700">Username:</label>
        <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" class="w-full p-2 border border-gray-300 rounded">
    </div>
    <div class="mb-4">
        <label class="block text-gray-700">Email:</label>
        <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" class="w-full p-2 border border-gray-300 rounded">
    </div>
    <div class="mb-4">
        <label class="block text-gray-700">Nama Lengkap:</label>
        <input type="text" name="nama_lengkap" value="<?php echo htmlspecialchars($user['NamaLengkap']); ?>" class="w-full p-2 border border-gray-300 rounded">
    </div>
    <div class="mb-4">
        <label class="block text-gray-700">Alamat:</label>
        <input type="text" name="alamat" value="<?php echo htmlspecialchars($user['alamat']); ?>" class="w-full p-2 border border-gray-300 rounded">
    </div>
    <div class="mb-4">
        <label class="block text-gray-700">Role:</label>
        <select name="role" class="w-full p-2 border border-gray-300 rounded">
            <option value="Petugas" <?php if ($user['role'] == 'Petugas') echo 'selected'; ?>>Petugas</option>
            <option value="Peminjam" <?php if ($user['role'] == 'Peminjam') echo 'selected'; ?>>Peminjam</option>
        </select>
    </div>
    <div class="flex justify-between">
        <button type="submit" name="update" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Update</button>
        <a href="kelola.php" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">Back</a>
    </div>
</form>

</body>
</html>