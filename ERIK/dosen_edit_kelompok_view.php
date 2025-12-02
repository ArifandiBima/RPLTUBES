<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dosen: Edit Kelompok - <?php echo $tugas_besar['namaMK'] ?? 'Tugas Besar'; ?></title> 
    <link rel="stylesheet" href="style.css">
    <script src="script.js"></script>
</head>
<body>

<div class="header-top-nav">
    <div class="left-nav">
        <a href="home.php" class="btn-nav back-btn">
            <span class="arrow">←</span> Kembali
        </a>
    </div>
    <div class="right-nav">
        <a href="profil.php" class="btn-nav profile-btn">
            <?= htmlspecialchars($tugas_besar['namaDosen'] ?? 'Dosen'); ?> 👤
        </a>
    </div>
</div>
<div class="container">
    
    <div class="header-container-plain"> 
        <?php if (!empty($tugas_besar)): ?>
        <h1><?= htmlspecialchars($tugas_besar['namaMK']); ?> (<?= htmlspecialchars($tugas_besar['kodeMK']); ?>)</h1>
        <h2>Tugas Besar: <?= htmlspecialchars($tugas_besar['nama']); ?></h2> 
        <?php else: ?>
        <h1>Data Tugas Besar Tidak Ditemukan</h1>
        <?php endif; ?>
    </div>
    <div class="card management-section">
        <h3>Pengaturan Kelompok</h3>
        <p style="margin-bottom: 20px;">
            Mahasiswa Belum Berkelompok: <strong><?= count($mahasiswa_belum_kelompok); ?> Orang</strong> 
            | Mode Pembentukan: <strong><?= isset($tugas_besar['mode_pemilihan']) ? ucfirst($tugas_besar['mode_pemilihan']) : 'N/A'; ?></strong>
        </p>
        
        <hr>

        <form method="POST" class="manual-insert-form">
            
            <div class="form-row-actions"> 
                
                <div class="manual-select-group">
                    Masukkan 
                    <select name="npm_target" required>
                        <option value="">-- Pilih Mahasiswa (NPM & Nama) --</option>
                        <?php foreach ($mahasiswa_belum_kelompok as $mhs) : ?>
                            <option value="<?= htmlspecialchars($mhs['npm']); ?>">
                                <?= htmlspecialchars($mhs['npm']); ?> - <?= htmlspecialchars($mhs['nama']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    
                    ke Kelompok
                    
                    <select name="kelompok_target" required>
                        <option value="">-- Pilih Kelompok --</option>
                        <?php 
                        foreach ($data_kelompok as $nomorKelompok => $kelompok) : 
                            // Hitung status kelompok
                            $terisi = count($kelompok['anggota']);
                            $kapasitas = $kelompok['kapasitas'] ?? 0;
                            $status = ($terisi >= $kapasitas) ? ' (PENUH)' : '';
                        ?>
                            <option value="<?= $nomorKelompok; ?>" <?= $status ? 'disabled' : ''; ?>>
                                Kelompok <?= $kelompok_label($nomorKelompok); ?> <?= $status; ?>
                            </option>
                        <?php endforeach; ?>
                        
                    </select>
                    
                    <button type="submit" name="insert_manual" class="btn secondary">Masukkan</button>
                </div>

                <div class="action-buttons-group">
                    <button type="submit" name="auto_fill" class="btn primary"
                            onclick="return confirm('Yakin ingin mengisi otomatis mahasiswa yang tersisa?');">
                        Auto Fill
                    </button>
                    
                    <?php 
                    // Asumsikan is_locked ada, jika tidak, asumsikan false
                    $is_locked = $tugas_besar['is_locked'] ?? false; 
                    ?>
                    <input type="hidden" name="lock_team">
                    <input type="hidden" name="new_status" value="<?= $is_locked ? 'false' : 'true'; ?>">
                    <button type="submit" 
                            class="btn <?= $is_locked ? 'warning' : 'danger'; ?>" 
                            onclick="return confirm('Yakin ingin <?= $is_locked ? 'MEMBUKA KUNCI' : 'MENGUNCI'; ?> pemilihan kelompok?');">
                        <?= $is_locked ? 'Unlock Team' : 'Lock Team'; ?> 
                    </button>
                </div>
            </div>
        </form>
    </div>

    <hr> <h2>Daftar Kelompok Saat Ini</h2>
    <table id="tabelEditKelompok">
        <thead>
            <tr>
                <th>Grup</th>
                <th>Status</th>
                <th>Nama Anggota (NPM)</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($data_kelompok as $nomorKelompok => $kelompok) : 
                $label = $kelompok_label($nomorKelompok);
                $terisi = count($kelompok['anggota']);
                $kapasitas = $kelompok['kapasitas'] ?? 0;
                $status_text = "{$terisi} / {$kapasitas} Orang";
                $is_full = ($kapasitas > 0) && ($terisi >= $kapasitas);
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
                                <li>
                                    <?= htmlspecialchars($anggota['nama']); ?> (<?= htmlspecialchars($anggota['npm']); ?>)
                                    <form method="POST" class="remove-form" onsubmit="return confirm('Keluarkan <?= htmlspecialchars($anggota['nama']); ?> dari kelompok <?= $label; ?>?');">
                                        <input type="hidden" name="action" value="remove_member">
                                        <input type="hidden" name="npm" value="<?= htmlspecialchars($anggota['npm']); ?>">
                                        <button type="submit" class="btn-sm danger-sm">X</button>
                                    </form>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else : ?>
                        <span class="empty-slot">Slot Kosong</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

</div>

</body>
</html>