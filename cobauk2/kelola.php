
<?php
session_start();
if (!isset($_SESSION['session_username'])) {
    header('location:login.php');
    exit();
}
include("koneksi.php");

if (isset($_POST['add_member'])) {
    // Handle new member addition
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];  // Retrieve the password from the form
    $nama_lengkap = $_POST['nama_lengkap'];
    $alamat = $_POST['alamat'];
    $role = $_POST['role'];
    
    // Hash the password for security
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Prepare the insert query
    $query = "INSERT INTO user (username, email, password, NamaLengkap, alamat, role) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $koneksi->prepare($query);
    $stmt->bind_param("ssssss", $username, $email, $hashed_password, $nama_lengkap, $alamat, $role);
    
    // Execute the query and provide feedback
    if ($stmt->execute()) {
        echo "<div class='bg-green-500 text-white text-center py-2 mb-4'>Anggota berhasil ditambahkan!</div>";
    } else {
        echo "<div class='bg-red-500 text-white text-center py-2 mb-4'>Error: " . $stmt->error . "</div>";
    }
}

if (isset($_POST['delete'])) {
    $id = $_POST['UserID'];
    
    // Delete query
    $deleteQuery = "DELETE FROM user WHERE UserID = ?";
    $stmt = $koneksi->prepare($deleteQuery);
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        header("Location: kelola.php");
    } else {
        echo "<div class='bg-red-500 text-white text-center py-2 mb-4'>Error deleting user: " . $koneksi->error . "</div>";
    }
}

$query = "SELECT * FROM user";
$result = mysqli_query($koneksi, $query);



?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <title>Kelola Anggota</title>
</head>

<body class="bg-gray-100">
<?php include 'navbar.php'; ?>
<div class="container mx-auto py-10 px-4">
    
    <!-- Form to add a new user -->
    <div class="bg-white shadow-md rounded-lg p-6 mb-6">
        <h2 class="text-2xl font-bold mb-4 text-gray-800">Tambah Anggota</h2>
        <form method="post" class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="mb-4">
                <label class="block text-gray-700">Username:</label>
                <input type="text" name="username" required class="w-full p-2 border border-gray-300 rounded focus:outline-none focus:ring focus:ring-blue-200">
            </div>
            <div class="mb-4">
                <label class="block text-gray-700">Email:</label>
                <input type="email" name="email" required class="w-full p-2 border border-gray-300 rounded focus:outline-none focus:ring focus:ring-blue-200">
            </div>
            <div class="mb-4 col-span-2">
                <label class="block text-gray-700">Password:</label>
                <input type="password" name="password" required class="w-full p-2 border border-gray-300 rounded focus:outline-none focus:ring focus:ring-blue-200">
            </div>
            <div class="mb-4 col-span-2">
                <label class="block text-gray-700">Nama Lengkap:</label>
                <input type="text" name="nama_lengkap" required class="w-full p-2 border border-gray-300 rounded focus:outline-none focus:ring focus:ring-blue-200">
            </div>
            <div class="mb-4 col-span-2">
                <label class="block text-gray-700">Alamat:</label>
                <input type="text" name="alamat" required class="w-full p-2 border border-gray-300 rounded focus:outline-none focus:ring focus:ring-blue-200">
            </div>
            <div class="mb-4 col-span-2">
                <label class="block text-gray-700">Role:</label>
                <select name="role" required class="w-full p-2 border border-gray-300 rounded focus:outline-none focus:ring focus:ring-blue-200">
                    <option value="Petugas">Petugas</option>
                    <option value="Peminjam">Peminjam</option>
                </select>
            </div>
            <div class="col-span-2 text-right">
                <button type="submit" name="add_member" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded transition duration-200">Tambah Anggota</button>
            </div>
        </form>
    </div>

    <!-- User Table -->
    <div class="bg-white shadow-md rounded-lg p-6">
        <h2 class="text-2xl font-bold mb-4 text-gray-800">Daftar Anggota</h2>
        <table class="min-w-full bg-white border border-gray-200 rounded-lg">
            <thead>
                <tr class="bg-gray-800 text-white">
                    <th class="py-3 px-6 text-left">Username</th>
                    <th class="py-3 px-6 text-left">Email</th>
                    <th class="py-3 px-6 text-left">Nama Lengkap</th>
                    <th class="py-3 px-6 text-left">Alamat</th>
                    <th class="py-3 px-6 text-left">Role</th>
                    <th class="py-3 px-6 text-left">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <tr class="border-b hover:bg-gray-100">
                        <td class="py-3 px-6"><?php echo htmlspecialchars($row['username']); ?></td>
                        <td class="py-3 px-6"><?php echo htmlspecialchars($row['email']); ?></td>
                        <td class="py-3 px-6"><?php echo htmlspecialchars($row['NamaLengkap']); ?></td>
                        <td class="py-3 px-6"><?php echo htmlspecialchars($row['alamat']); ?></td>
                        <td class="py-3 px-6"><?php echo htmlspecialchars($row['role']); ?></td>
                        <td class="py-3 px-6">
                            <a href="edituser.php?id=<?php echo $row['UserID']; ?>" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Edit</a>
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="UserID" value="<?php echo $row['UserID']; ?>">
                                <button type="submit" name="delete" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded" onclick="return confirm('Anda yakin ingin menghapus user ini?');">Hapus</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>
