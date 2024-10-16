<?php
// Inisialisasi database
function initDB() {
    $db = new SQLite3('eskul.db');
    $db->exec('CREATE TABLE IF NOT EXISTS eskul (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        nama TEXT NOT NULL,
        kelas TEXT NOT NULL,
        eskul TEXT NOT NULL,
        alasan TEXT,
        gambar TEXT
    )');
    return $db;
}

function sanitize_input($input) {
    return htmlspecialchars(strip_tags(trim($input)));
}

function handle_file_upload($file) {
    $target_dir = __DIR__ . "/uploads/";
    
    // Buat folder uploads jika belum ada
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $filename = basename($file["name"]);
    $target_file = $target_dir . $filename;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Periksa apakah file benar-benar gambar
    $check = getimagesize($file["tmp_name"]);
    if ($check === false) {
        return "File bukan gambar.";
    }

    // Periksa ukuran file (batas 5MB)
    if ($file["size"] > 5000000) {
        return "Maaf, file Anda terlalu besar.";
    }

    // Izinkan format file tertentu
    if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
        return "Maaf, hanya file JPG, JPEG, PNG & GIF yang diizinkan.";
    }

    // Jika file sudah ada, tambahkan angka ke nama file
    $i = 1;
    $new_filename = $filename;
    while (file_exists($target_dir . $new_filename)) {
        $new_filename = pathinfo($filename, PATHINFO_FILENAME) . "($i)." . $imageFileType;
        $i++;
    }
    $target_file = $target_dir . $new_filename;

    // Coba unggah file
    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        return "uploads/" . $new_filename;
    } else {
        return "Maaf, terjadi kesalahan saat mengunggah file Anda.";
    }
}

function insertData($db, $nama, $kelas, $eskul, $alasan, $gambar) {
    $stmt = $db->prepare('INSERT INTO eskul (nama, kelas, eskul, alasan, gambar) VALUES (:nama, :kelas, :eskul, :alasan, :gambar)');
    $stmt->bindValue(':nama', $nama, SQLITE3_TEXT);
    $stmt->bindValue(':kelas', $kelas, SQLITE3_TEXT);
    $stmt->bindValue(':eskul', $eskul, SQLITE3_TEXT);
    $stmt->bindValue(':alasan', $alasan, SQLITE3_TEXT);
    $stmt->bindValue(':gambar', $gambar, SQLITE3_TEXT);
    return $stmt->execute();
}

function updateData($db, $id, $nama, $kelas, $eskul, $alasan) {
    $stmt = $db->prepare('UPDATE eskul SET nama = :nama, kelas = :kelas, eskul = :eskul, alasan = :alasan WHERE id = :id');
    $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
    $stmt->bindValue(':nama', $nama, SQLITE3_TEXT);
    $stmt->bindValue(':kelas', $kelas, SQLITE3_TEXT);
    $stmt->bindValue(':eskul', $eskul, SQLITE3_TEXT);
    $stmt->bindValue(':alasan', $alasan, SQLITE3_TEXT);
    return $stmt->execute();
}

function deleteData($db, $id) {
    $stmt = $db->prepare('DELETE FROM eskul WHERE id = :id');
    $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
    return $stmt->execute();
}

function getAllData($db) {
    return $db->query('SELECT * FROM eskul ORDER BY id DESC');
}

function getDataById($db, $id) {
    $stmt = $db->prepare('SELECT * FROM eskul WHERE id = :id');
    $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
    return $stmt->execute();
}
?>