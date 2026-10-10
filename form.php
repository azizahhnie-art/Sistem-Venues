<?php
require 'koneksi.php';
function e($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

$cabangOptions = ['Badminton','Futsal','Basket','Tenis','Voli'];
$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);   // 0 = tambah, >0 = edit

$data = [
    'nama' => '', 'nomor_lisensi' => '', 'level_sertifikasi' => '',
    'cabang' => [], 'telepon' => '', 'tarif' => '',
    'status' => 'Aktif', 'tanggal_gabung' => '', 'catatan' => ''
];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data['nama']              = trim($_POST['nama'] ?? '');
    $data['nomor_lisensi']     = trim($_POST['nomor_lisensi'] ?? '');
    $data['level_sertifikasi'] = $_POST['level_sertifikasi'] ?? '';
    $data['cabang']            = array_values(array_intersect($cabangOptions, $_POST['cabang'] ?? []));
    $data['telepon']           = trim($_POST['telepon'] ?? '');
    $data['tarif']             = trim($_POST['tarif'] ?? '');
    $data['status']            = $_POST['status'] ?? 'Aktif';
    $data['tanggal_gabung']    = $_POST['tanggal_gabung'] ?? '';
    $data['catatan']           = trim($_POST['catatan'] ?? '');

    // validasi
    if ($data['nama'] === '')          $errors['nama'] = 'Nama wajib diisi.';
    if ($data['nomor_lisensi'] === '') $errors['lisensi'] = 'Nomor lisensi wajib diisi.';
    if (!in_array($data['level_sertifikasi'], ['Daerah','Nasional','Internasional'])) $errors['level'] = 'Pilih level sertifikasi.';
    if (count($data['cabang']) === 0)  $errors['cabang'] = 'Pilih minimal satu cabang olahraga.';
    if ($data['telepon'] === '')       $errors['telepon'] = 'Nomor telepon wajib diisi.';
    if (!is_numeric($data['tarif']) || $data['tarif'] < 0) $errors['tarif'] = 'Masukkan tarif yang valid.';
    if (!in_array($data['status'], ['Aktif','Nonaktif'])) $data['status'] = 'Aktif';

    // cek nomor lisensi tidak kembar
    if (!isset($errors['lisensi'])) {
        $cek = $conn->prepare("SELECT id FROM instruktur WHERE nomor_lisensi = ? AND id <> ?");
        $cek->bind_param('si', $data['nomor_lisensi'], $id);
        $cek->execute();
        if ($cek->get_result()->num_rows > 0) {
            $errors['lisensi'] = 'Nomor lisensi sudah terdaftar untuk instruktur lain.';
        }
    }

    // simpan kalau tidak ada error
    if (!$errors) {
        $cabangStr = implode(', ', $data['cabang']);
        $tarif     = (int)$data['tarif'];
        $tgl       = $data['tanggal_gabung'] !== '' ? $data['tanggal_gabung'] : null;

        if ($id > 0) {
            // UPDATE
            $stmt = $conn->prepare(
                "UPDATE instruktur SET nama=?, nomor_lisensi=?, level_sertifikasi=?, cabang=?,
                 telepon=?, tarif=?, status=?, tanggal_gabung=?, catatan=? WHERE id=?"
            );
            $stmt->bind_param('sssssisssi',
                $data['nama'], $data['nomor_lisensi'], $data['level_sertifikasi'], $cabangStr,
                $data['telepon'], $tarif, $data['status'], $tgl, $data['catatan'], $id
            );
        } else {
            // CREATE
            $stmt = $conn->prepare(
                "INSERT INTO instruktur (nama, nomor_lisensi, level_sertifikasi, cabang,
                 telepon, tarif, status, tanggal_gabung, catatan)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );
            $stmt->bind_param('sssssisss',
                $data['nama'], $data['nomor_lisensi'], $data['level_sertifikasi'], $cabangStr,
                $data['telepon'], $tarif, $data['status'], $tgl, $data['catatan']
            );
        }
        $stmt->execute();
        header('Location: index.php');
        exit;
    }
} elseif ($id > 0) {
    // mode edit: ambil data lama dari database
    $stmt = $conn->prepare("SELECT * FROM instruktur WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    if (!$row) { header('Location: index.php'); exit; }
    $data = $row;
    $data['cabang']         = explode(', ', $row['cabang']);
    $data['tanggal_gabung'] = $row['tanggal_gabung'] ?? '';
    $data['catatan']        = $row['catatan'] ?? '';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Form Instruktur — Modul Instruktur & Coach</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style.css">
</head>
<body>

<div class="topbar">
  <div class="topbar-inner">
    <div class="brand">
      <div class="mark">Instruktur<span>&Coach</span></div>
      <div class="sub">Modul Individu D — Sistem Manajemen Venue Olahraga</div>
    </div>
    <nav class="tabs">
      <a href="index.php">Daftar Instruktur</a>
      <a href="form.php" class="active">Tambah Instruktur</a>
    </nav>
  </div>
</div>

<main>
  <div class="page-head">
    <div>
      <h1 class="page-title"><?= $id ? 'Edit Data Instruktur' : 'Tambah Instruktur Baru' ?></h1>
      <p class="page-desc"><?= $id ? 'Memperbarui data untuk ' . e($data['nama']) . '.' : 'Lengkapi data pelatih mitra untuk didaftarkan ke sistem venue.' ?></p>
    </div>
  </div>

  <form id="instrukturForm" method="post" action="form.php">
    <input type="hidden" name="id" value="<?= $id ?>">

    <div class="field-grid">
      <div class="field full <?= isset($errors['nama']) ? 'invalid' : '' ?>">
        <label>Nama Instruktur/Pelatih <span class="req">*</span></label>
        <input type="text" name="nama" placeholder="cth. Andika Pratama" autocomplete="off" value="<?= e($data['nama']) ?>">
        <small class="err"><?= e($errors['nama'] ?? '') ?></small>
      </div>

      <div class="field <?= isset($errors['lisensi']) ? 'invalid' : '' ?>">
        <label>Nomor Lisensi <span class="req">*</span></label>
        <input type="text" name="nomor_lisensi" placeholder="cth. LSN-2024-0091" autocomplete="off" value="<?= e($data['nomor_lisensi']) ?>">
        <small class="err"><?= e($errors['lisensi'] ?? '') ?></small>
      </div>

      <div class="field <?= isset($errors['level']) ? 'invalid' : '' ?>">
        <label>Level Sertifikasi <span class="req">*</span></label>
        <select name="level_sertifikasi">
          <option value="">Pilih level</option>
          <?php foreach (['Daerah','Nasional','Internasional'] as $lv): ?>
            <option value="<?= $lv ?>" <?= $data['level_sertifikasi'] === $lv ? 'selected' : '' ?>><?= $lv ?></option>
          <?php endforeach; ?>
        </select>
        <small class="err"><?= e($errors['level'] ?? '') ?></small>
      </div>

      <div class="field full <?= isset($errors['cabang']) ? 'invalid' : '' ?>">
        <label>Keahlian Cabang Olahraga <span class="req">*</span></label>
        <div class="checkgroup">
          <?php foreach ($cabangOptions as $c): ?>
            <label>
              <input type="checkbox" name="cabang[]" value="<?= e($c) ?>" <?= in_array($c, $data['cabang']) ? 'checked' : '' ?>>
              <?= e($c) ?>
            </label>
          <?php endforeach; ?>
        </div>
        <small class="err"><?= e($errors['cabang'] ?? '') ?></small>
      </div>

      <div class="field <?= isset($errors['telepon']) ? 'invalid' : '' ?>">
        <label>Nomor Telepon <span class="req">*</span></label>
        <input type="text" name="telepon" placeholder="cth. 081234567890" autocomplete="off" value="<?= e($data['telepon']) ?>">
        <small class="err"><?= e($errors['telepon'] ?? '') ?></small>
      </div>

      <div class="field <?= isset($errors['tarif']) ? 'invalid' : '' ?>">
        <label>Tarif per Sesi (Rp) <span class="req">*</span></label>
        <input type="number" name="tarif" placeholder="cth. 150000" min="0" value="<?= e($data['tarif']) ?>">
        <small class="err"><?= e($errors['tarif'] ?? '') ?></small>
      </div>

      <div class="field">
        <label>Status Mitra</label>
        <select name="status">
          <option value="Aktif" <?= $data['status'] === 'Aktif' ? 'selected' : '' ?>>Aktif</option>
          <option value="Nonaktif" <?= $data['status'] === 'Nonaktif' ? 'selected' : '' ?>>Nonaktif</option>
        </select>
      </div>

      <div class="field">
        <label>Tanggal Bergabung</label>
        <input type="date" name="tanggal_gabung" value="<?= e($data['tanggal_gabung']) ?>">
      </div>

      <div class="field full">
        <label>Catatan (opsional)</label>
        <textarea name="catatan" rows="2" placeholder="Catatan tambahan mengenai instruktur..."><?= e($data['catatan']) ?></textarea>
      </div>
    </div>

    <div class="form-actions">
      <button type="submit" class="btn btn-primary"><?= $id ? 'Perbarui Data' : 'Simpan Data' ?></button>
      <a href="index.php" class="btn btn-ghost">Batal</a>
    </div>
    <p class="form-note">Data disimpan di database MySQL (venue_db).</p>
  </form>
</main>

</body>
</html>