<?php
require "conn.php";
session_start();

// Safely read GET parameters (avoid PHP warnings)
$kodeMatkul = $_GET['kodeMatkul'] ?? null;
$namaMatkul = $_GET['namaMatkul'] ?? null;
$namaTB     = $_GET['namaTB']     ?? null;
$kelas      = $_GET['kelas']      ?? null;
$semester   = isset($_GET['semester']) ? (int)$_GET['semester'] : null;

// If required data missing, show friendly message and exit
if (!$kodeMatkul || !$namaMatkul || !$namaTB || !$kelas || !$semester) {
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="utf-8">
        <title>Pilih Kelompok — Error</title>
        <link rel="stylesheet" href="pilihKelompokStyles.css">
    </head>
    <body class="outer">
      <div class="inner">
        <a class="back" href="home_dosen.php">←</a>
        <h1>Penilaian Kelompok</h1>
        <div class="notice">
          Parameter halaman tidak lengkap. Pastikan Anda menuju halaman ini dari halaman detail tugas besar (Edit Nilai).
        </div>
        <div style="margin-top:18px;">
          <a class="primary-btn" href="home_dosen.php">Kembali ke Home Dosen</a>
        </div>
      </div>
    </body>
    </html>
    <?php
    exit;
}

// Get groups for this tugas besar
$sql = "SELECT nomorKelompok
        FROM kelompok
        WHERE namaTugasBesar = ?
          AND kodeMataKuliah = ?
          AND kodeKelas = ?
          AND semester = ?
        ORDER BY nomorKelompok";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sssi", $namaTB, $kodeMatkul, $kelas, $semester);
$stmt->execute();
$groups = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Pilih Kelompok — <?= htmlspecialchars($namaMatkul) ?></title>
    <link rel="stylesheet" href="pilihKelompokStyles.css">
</head>
<body class="outer">

  <div class="inner">
    <a class="back" href="matkul.php">←</a>

    <div class="top-info">
      <div class="matkul"><?= htmlspecialchars($namaMatkul) ?></div>
      <div class="kode"><?= htmlspecialchars($kodeMatkul) ?></div>
    </div>

    <h1>Penilaian Kelompok</h1>

    <?php if (count($groups) === 0): ?>
      <div class="empty">Tidak ada kelompok untuk tugas besar ini.</div>
      <a class="primary-btn" href="matkul.php">Kembali</a>
    <?php else: ?>
      <form method="GET" action="nilai_dosen.php" id="pilihForm">
        <!-- pass necessary values to nilai_dosen.php -->
        <input type="hidden" name="kodeMatkul" value="<?= htmlspecialchars($kodeMatkul) ?>">
        <input type="hidden" name="namaMatkul" value="<?= htmlspecialchars($namaMatkul) ?>">
        <input type="hidden" name="namaTB" value="<?= htmlspecialchars($namaTB) ?>">
        <input type="hidden" name="kelas" value="<?= htmlspecialchars($kelas) ?>">
        <input type="hidden" name="semester" value="<?= htmlspecialchars($semester) ?>">

        <table class="group-table">
          <thead>
            <tr>
              <th>Pilih</th>
              <th>Group</th>
              <th>Nama Anggota</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($groups as $gRow):
              $g = (int)$gRow['nomorKelompok'];

              // Get member list for this group
              $q = "SELECT m.nama, a.npmPeserta
                    FROM anggotaKelompok a
                    JOIN mahasiswa m ON a.npmPeserta = m.npm
                    WHERE a.namaTugasBesar = ? AND a.kodeMataKuliah = ? AND a.kodeKelas = ? AND a.semester = ? AND a.nomorKelompok = ?
                    ORDER BY m.nama";
              $st = $conn->prepare($q);
              $st->bind_param("sssii", $namaTB, $kodeMatkul, $kelas, $semester, $g);
              $st->execute();
              $members = $st->get_result()->fetch_all(MYSQLI_ASSOC);

              // Count how many members have at least one nilai for komponen 1 (example)
              $q2 = "SELECT COUNT(DISTINCT a.npmPeserta) AS total,
                            SUM(CASE WHEN n.npmPeserta IS NOT NULL THEN 1 ELSE 0 END) AS done
                     FROM anggotaKelompok a
                     LEFT JOIN nilai n ON n.namaTugasBesar = a.namaTugasBesar
                       AND n.kodeMataKuliah = a.kodeMataKuliah
                       AND n.kodeKelas = a.kodeKelas
                       AND n.semester = a.semester
                       AND n.npmPeserta = a.npmPeserta
                       AND n.nomorKomponen = 1
                     WHERE a.namaTugasBesar = ? AND a.kodeMataKuliah = ? AND a.kodeKelas = ? AND a.semester = ? AND a.nomorKelompok = ?";
              $st2 = $conn->prepare($q2);
              $st2->bind_param("sssii", $namaTB, $kodeMatkul, $kelas, $semester, $g);
              $st2->execute();
              $rowCount = $st2->get_result()->fetch_assoc();
              $total = (int)($rowCount['total'] ?? count($members));
              $done  = (int)($rowCount['done'] ?? 0);

              $names = array_column($members, 'nama');
              $names_str = $names ? implode(", ", $names) : '-';
            ?>
            <tr>
              <td class="radio-cell">
                <input type="radio" name="kelompok" value="<?= $g ?>" required>
              </td>
              <td class="group-letter"><?= htmlspecialchars(chr(64 + $g)) ?></td>
              <td class="members"><?= htmlspecialchars($names_str) ?></td>
              <td class="status"><?= $done ?>/<?= $total ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>

        <div class="actions">
          <button type="submit" class="primary-btn">Pilih Kelompok</button>
        </div>
      </form>
    <?php endif; ?>
  </div>

<script>
// optional: nicer client-side validation: ensure radio selected
document.getElementById('pilihForm')?.addEventListener('submit', function(e){
  if (!document.querySelector('input[name="kelompok"]:checked')) {
    e.preventDefault();
    alert('Pilih salah satu kelompok dulu.');
  }
});
</script>

</body>
</html>
