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
        <a href="home.php" class="btn-nav profile-btn">
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
        
        <?php 
        // Ambil status lock dari controller
        $is_locked = (bool)($tugas_besar['is_locked'] ?? false); 
        ?>
        
        <p>
            <strong>Status Pemilihan:</strong> 
            <span class="status-badge <?= $is_locked ? 'danger' : 'success'; ?>">
                <?= $is_locked ? 'TERKUNCI' : 'TERBUKA'; ?>
            </span>
            | Maks. Anggota: <strong><?= htmlspecialchars($tugas_besar['max_anggota'] ?? 'N/A'); ?></strong>
        </p>

        <form method="POST" class="manual-insert-form">
            <input type="hidden" name="action" value="manual_insert"> 
            
            <div class="form-row-actions"> 
                
                <div class="manual-select-group">
                    Masukkan 
                    <select name="npm_target" required <?= $is_locked ? 'disabled' : ''; ?>>
                        <option value="">-- Pilih Mahasiswa (NPM & Nama) --</option>
                        <?php foreach ($mahasiswa_belum_kelompok as $mhs) : ?>
                            <option value="<?= htmlspecialchars($mhs['npm']); ?>">
                                <?= htmlspecialchars($mhs['npm']); ?> - <?= htmlspecialchars($mhs['nama']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    
                    ke Kelompok
                    
                    <select name="kelompok_target" required <?= $is_locked ? 'disabled' : ''; ?>>
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
                    
                    <button type="submit" class="btn secondary"
                        <?= $is_locked ? 'disabled' : ''; ?>>
                        Masukkan
                    </button>
                </div>

                <div class="action-buttons-group">
                    <button type="submit" name="action" value="auto_fill" class="btn primary"
                            formnovalidate  
                            onclick="return confirm('Mengisi otomatis mahasiswa yang tersisa?');"
                            <?= $is_locked ? 'disabled' : ''; ?>>
                        Auto Fill
                    </button>
                    
                    <button type="submit" name="action" value="toggle_lock"
                            formnovalidate
                            class="btn <?= $is_locked ? 'warning' : 'danger'; ?>" 
                            onclick="return confirm('Yakin ingin <?= $is_locked ? 'MEMBUKA KUNCI' : 'MENGUNCI'; ?> pemilihan kelompok? Tindakan ini akan <?= $is_locked ? 'mengaktifkan kembali' : 'menonaktifkan'; ?> semua fungsi manajemen kelompok manual/otomatis.');">
                        <?= $is_locked ? 'Unlock Team' : 'Lock Team'; ?> 
                    </button>
                </div>
            </div>
        </form>

        <?php if (!empty($message)): ?>
            <div class="message <?= $message_type; ?>">
                <?= htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
    </div>


    <table id="tabelEditKelompok">
        <thead>
            <tr>
                <th>No. Kelompok</th>
                <th>Kapasitas</th>
                <th>Status</th>
                <th>Anggota (NPM & Nama)</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($data_kelompok as $nomorKelompok => $kelompok) : 
                $label = $kelompok_label($nomorKelompok);
                $terisi = count($kelompok['anggota']);
                $kapasitas = $kelompok['kapasitas'] ?? 0;
                $is_full = ($terisi >= $kapasitas);
                $status_text = $is_full ? 'PENUH' : 'TERBUKA (' . ($kapasitas - $terisi) . ' slot)';
            ?>
            <tr>
                <td data-label="No. Kelompok">Kelompok <?= $label; ?></td>
                <td data-label="Kapasitas"><?= $terisi; ?> / <?= $kapasitas; ?></td>
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
                                        <button type="submit" class="btn-sm danger-sm" <?= $is_locked ? 'disabled' : ''; ?>>X</button> 
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