<?php
require_once 'function.php';

$db = initDB();
$edit_mode = false;
$edit_data = null;

// Proses form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['submit'])) {
        $nama = sanitize_input($_POST['nama']);
        $kelas = sanitize_input($_POST['kelas']);
        $eskul = sanitize_input($_POST['eskul']);
        $alasan = sanitize_input($_POST['alasan']);
        
        // Handle file upload
        $gambar = '';
        if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
            $upload_result = handle_file_upload($_FILES['gambar']);
            if (strpos($upload_result, 'uploads/') === 0) {
                $gambar = $upload_result;
            } else {
                echo "<p class='error'>$upload_result</p>";
            }
        }
        
        if ($gambar !== '' || !isset($_FILES['gambar'])) {
            if (insertData($db, $nama, $kelas, $eskul, $alasan, $gambar)) {
                echo "<p class='success'>Data berhasil disimpan!</p>";
            } else {
                echo "<p class='error'>Gagal menyimpan data.</p>";
            }
        }
    } elseif (isset($_POST['update'])) {
        $id = $_POST['id'];
        $nama = sanitize_input($_POST['nama']);
        $kelas = sanitize_input($_POST['kelas']);
        $eskul = sanitize_input($_POST['eskul']);
        $alasan = sanitize_input($_POST['alasan']);
        
        if (updateData($db, $id, $nama, $kelas, $eskul, $alasan)) {
            echo "<p class='success'>Data berhasil diperbarui!</p>";
        } else {
            echo "<p class='error'>Gagal memperbarui data.</p>";
        }
    } elseif (isset($_POST['delete'])) {
        $id = $_POST['id'];
        if (deleteData($db, $id)) {
            echo "<p class='success'>Data berhasil dihapus!</p>";
        } else {
            echo "<p class='error'>Gagal menghapus data.</p>";
        }
    }
}

// Handle edit request
if (isset($_GET['edit'])) {
    $edit_mode = true;
    $edit_id = $_GET['edit'];
    $result = getDataById($db, $edit_id);
    $edit_data = $result->fetchArray(SQLITE3_ASSOC);
}

// Ambil semua data
$results = getAllData($db);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pendaftaran Eskul Sekolah</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Pendaftaran Eskul Sekolah</h1>
        
        <form action="" method="post" enctype="multipart/form-data">
            <?php if ($edit_mode): ?>
                <input type="hidden" name="id" value="<?= $edit_data['id'] ?>">
            <?php endif; ?>
            
            <label for="nama">Nama:</label>
            <input type="text" id="nama" name="nama" required autofocus value="<?= $edit_mode ? $edit_data['nama'] : '' ?>">
            
            <label for="kelas">Kelas:</label>
            <input type="text" id="kelas" name="kelas" required value="<?= $edit_mode ? $edit_data['kelas'] : '' ?>">
            
            <label for="eskul">Eskul:</label>
            <select id="eskul" name="eskul" required>
                <option value="">Pilih Eskul</option>
                <?php
                $eskul_options = ['Pramuka', 'PMR', 'Basket', 'Futsal'];
                foreach ($eskul_options as $option) {
                    $selected = ($edit_mode && $edit_data['eskul'] == $option) ? 'selected' : '';
                    echo "<option value='$option' $selected>$option</option>";
                }
                ?>
            </select>
            
            <label for="alasan">Alasan:</label>
            <textarea id="alasan" name="alasan"><?= $edit_mode ? $edit_data['alasan'] : '' ?></textarea>
            
            <?php if (!$edit_mode): ?>
                <label for="gambar">Gambar:</label>
                <input type="file" id="gambar" name="gambar" accept="image/*">
            <?php endif; ?>
            
            <?php if ($edit_mode): ?>
                <button type="submit" name="update">Update</button>
                <a href="index.php" class="button">Batal</a>
            <?php else: ?>
                <button type="submit" name="submit">Daftar</button>
            <?php endif; ?>
        </form>
        
        <h2>Daftar Pendaftar</h2>
        <table>
            <tr>
                <th>Nama</th>
                <th>Kelas</th>
                <th>Eskul</th>
                <th>Alasan</th>
                <th>Gambar</th>
                <th>Aksi</th>
            </tr>
            <?php while ($row = $results->fetchArray()): ?>
            <tr>
                <td><?= htmlspecialchars($row['nama']) ?></td>
                <td><?= htmlspecialchars($row['kelas']) ?></td>
                <td><?= htmlspecialchars($row['eskul']) ?></td>
                <td><?= htmlspecialchars($row['alasan']) ?></td>
                <td>
                    <?php if ($row['gambar']): ?>
                        <img src="<?= htmlspecialchars($row['gambar']) ?>" alt="Gambar Pendaftar" width="100">
                    <?php else: ?>
                        Tidak ada gambar
                    <?php endif; ?>
                </td>
                <td>
                    <a href="?edit=<?= $row['id'] ?>" class="button edit">Edit</a>
                    <form action="" method="post" style="display: inline;">
                        <input type="hidden" name="id" value="<?= $row['id'] ?>">
                        <button type="submit" name="delete" class="delete" onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?')">Hapus</button>
                    </form>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>
</body>
</html>