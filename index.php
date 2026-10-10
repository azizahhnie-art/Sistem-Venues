<?php
require 'koneksi.php';
function e($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

// DELETE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['hapus_id'])) {
    $stmt = $conn->prepare("DELETE FROM instruktur WHERE id = ?");
    $stmt->bind_param('i', $_POST['hapus_id']);
    $stmt->execute();
    header('Location: index.php');
    exit;
}

// READ + cari + filter
$q      = trim($_GET['q'] ?? '');
$cabang = $_GET['cabang'] ?? '';
$status = $_GET['status'] ?? '';
$cabangOptions = ['Badminton','Futsal','Basket','Tenis','Voli'];

$where = []; $params = []; $types = '';
if ($q !== '') {
    $where[] = "(nama LIKE ? OR nomor_lisensi LIKE ? OR telepon LIKE ?)";
    $like = "%$q%";
    array_push($params, $like, $like, $like);
    $types .= 'sss';
}
if ($cabang !== '') { $where[] = "cabang LIKE ?"; $params[] = "%$cabang%"; $types .= 's'; }
if ($status !== '') { $where[] = "status = ?";    $params[] = $status;      $types .= 's'; }

$sql = "SELECT * FROM instruktur"
     . ($where ? " WHERE " . implode(' AND ', $where) : "")
     . " ORDER BY id DESC";
$stmt = $conn->prepare($sql);
if ($params) $stmt->bind_param($types, ...$params);
$stmt->execute();
$rows = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Daftar Instruktur — Modul Instruktur & Coach</title>
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
      <a href="index.php" class="active">Daftar Instruktur</a>
      <a href="form.php">Tambah Instruktur</a>
    </nav>
  </div>
</div>

<main>
  <div class="page-head">
    <div>
      <h1 class="page-title">Daftar Instruktur & Pelatih Mitra</h1>
      <p class="page-desc">Data instruktur/pelatih mitra venue, nomor lisensi, dan keahlian cabang olahraga.</p>
    </div>
    <a href="form.php" class="btn btn-primary">+ Tambah Instruktur</a>
  </div>

  <form class="toolbar" method="get" action="index.php">
    <input type="text" name="q" placeholder="Cari nama, nomor lisensi, atau kontak..." value="<?= e($q) ?>">
    <select name="cabang">
      <option value="">Semua Cabang</option>
      <?php foreach ($cabangOptions as $c): ?>
        <option value="<?= e($c) ?>" <?= $cabang === $c ? 'selected' : '' ?>><?= e($c) ?></option>
      <?php endforeach; ?>
    </select>
    <select name="status">
      <option value="">Semua Status</option>
      <option value="Aktif" <?= $status === 'Aktif' ? 'selected' : '' ?>>Aktif</option>
      <option value="Nonaktif" <?= $status === 'Nonaktif' ? 'selected' : '' ?>>Nonaktif</option>
    </select>
    <button type="submit" class="btn btn-primary">Cari</button>
  </form>

  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>Nama Instruktur</th>
          <th>No. Lisensi</th>
          <th>Keahlian Cabang</th>
          <th>Level</th>
          <th>Tarif / Sesi</th>
          <th>Status</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($d = $rows->fetch_assoc()): ?>
        <tr>
          <td class="name-cell">
            <strong><?= e($d['nama']) ?></strong>
            <span><?= e($d['telepon']) ?></span>
          </td>
          <td><?= e($d['nomor_lisensi']) ?></td>
          <td>
            <?php foreach (explode(', ', $d['cabang']) as $c): ?>
              <span class="pill"><?= e($c) ?></span>
            <?php endforeach; ?>
          </td>
          <td><?= e($d['level_sertifikasi']) ?></td>
          <td>Rp <?= number_format($d['tarif'], 0, ',', '.') ?></td>
          <td><span class="badge badge-<?= strtolower($d['status']) ?>"><?= e($d['status']) ?></span></td>
          <td>
            <a class="btn-edit-text" href="form.php?id=<?= (int)$d['id'] ?>">Edit</a>
            <form method="post" style="display:inline">
              <input type="hidden" name="hapus_id" value="<?= (int)$d['id'] ?>">
              <button type="submit" class="btn-danger-text">Hapus</button>
            </form>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>

    <?php if ($rows->num_rows === 0): ?>
    <div class="empty-state">
      <div class="big">Belum ada data instruktur</div>
      <div>Klik "Tambah Instruktur" untuk mendaftarkan pelatih mitra pertama.</div>
    </div>
    <?php endif; ?>
  </div>
</main>

</body>
</html>