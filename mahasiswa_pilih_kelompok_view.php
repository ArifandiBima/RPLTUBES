<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Mahasiswa: Pilih Kelompok - <?php echo $tugas_besar['namaMK'] ?? 'Tugas Besar'; ?></title> 
    <link rel="stylesheet" href="kelompokstyle.css">
</head>
<body>

<div class="header-top-nav">
    <div class="left-nav">
        <a <?php echo 'href="admin/admin.php?'.http_build_query($data).'"'?>class="btn-nav back-btn">
            <span class="arrow">←</span> Kembali
        </a>
    </div>
    <div class="right-nav">
        <a href="matkul.php" class="btn-nav profile-btn">
            <?= htmlspecialchars($nama_mahasiswa ?? 'Mahasiswa'); ?>👤
        </a>
    </div>
</div>
<div class="container">
    
    <div class="header-container-plain"> 
        <?php if (!empty($tugas_besar)): ?>
        <h1><?= htmlspecialchars($tugas_besar['namaMK']); ?> (<?= htmlspecialchars($tugas_besar['kodeMK']); ?>)</h1>
        <h2>Pilih Kelompok: <?= htmlspecialchars($tugas_besar['nama']); ?></h2> 
        <?php else: ?>
        <h1>Data Tugas Besar Tidak Ditemukan</h1>
        <?php endif; ?>
    </div>
    
    <div class="card management-section">
        <h3>Status Kelompok Anda</h3>
        <p>
            Status Pemilihan: 
            <strong><?= ($tugas_besar['is_locked'] ?? 0) ? '<span style="color:red;">TERKUNCI</span>' : '<span style="color:green;">TERBUKA</span>'; ?></strong>
        </p>
        
        <?php if ($kelompok_mahasiswa_saat_ini): ?>
            <p style="font-size: 1.1em;">
                Anda saat ini berada di: 
                <strong style="color:#007bff;">Kelompok <?= $kelompok_label($kelompok_mahasiswa_saat_ini); ?></strong>
            </p>
        <?php else: ?>
            <p style="font-size: 1.1em; color:#dc3545;">
                Anda belum memilih kelompok. Silakan bergabung ke salah satu kelompok di bawah.
            </p>
        <?php endif; ?>
        
        <?php if (!empty($message)): ?>
            <div style="padding: 10px; border-radius: 5px; background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; margin-top: 15px;">
                <?= $message; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <hr> <h2>Daftar Kelompok Yang Tersedia</h2>
    <table id="tabelEditKelompok">
        <thead>
            <tr>
                <th>Grup</th>
                <th>Status</th>
                <th>Anggota Saat Ini</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($data_kelompok as $nomorKelompok => $kelompok) : 
                $label = $kelompok_label($nomorKelompok);
                $terisi = count($kelompok['anggota']);
                $kapasitas = $kelompok['kapasitas'] ?? 0;
                $status_text = "{$terisi} / {$kapasitas} Orang";
                $is_full = ($kapasitas > 0) && ($terisi >= $kapasitas);
                $is_current_group = $kelompok_mahasiswa_saat_ini === $nomorKelompok;
            ?>
            <tr> 
                <td data-label="Grup"><?= $label; ?></td> 
                <td data-label="Status" class="<?= $is_full ? 'status-full' : 'status-open'; ?>">
                    <?= $status_text; ?>
                </td>
                <td data-label="Anggota">
                    <?php if (!empty($kelompok['anggota'])) : ?>
                        <ul class="anggota-list">
                            <?php foreach ($kelompok['anggota'] as $anggota) : ?>
                                <li style="justify-content: flex-start;">
                                    <?= htmlspecialchars($anggota['nama']); ?> (<?= htmlspecialchars($anggota['npm']); ?>)
                                    <?php if ($anggota['npm'] === $npm_mahasiswa): ?>
                                        <span style="color: #007bff; font-weight: bold; margin-left: 10px;">(Anda)</span>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else : ?>
                        <span class="empty-slot">Slot Kosong</span>
                    <?php endif; ?>
                </td>
                <td data-label="Aksi">
                    <?php if ($is_current_group): ?>
                        <button class="btn secondary" disabled>Kelompok Anda Saat Ini</button>
                    <?php elseif ($is_full || ($tugas_besar['is_locked'] ?? 0)): ?>
                        <button class="btn secondary" disabled>
                            <?= ($tugas_besar['is_locked'] ?? 0) ? 'Terkunci' : 'Penuh'; ?>
                        </button>
                    <?php else: ?>
                        <form method="POST" onsubmit="return confirm('Pindah ke Kelompok <?= $label; ?>?');">
                            <input type="hidden" name="action" value="join_group">
                            <input type="hidden" name="nomor_kelompok" value="<?= $nomorKelompok; ?>">
                            <button type="submit" class="btn primary">
                                <?= $kelompok_mahasiswa_saat_ini ? 'Pindah Kelompok' : 'Gabung Kelompok'; ?>
                            </button>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

</div>

</body>
</html>