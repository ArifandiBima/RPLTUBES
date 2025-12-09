
document.addEventListener('DOMContentLoaded', function() {
    console.log("Dosen Edit Kelompok Page Loaded.");
    
    // Contoh: Menonaktifkan tombol 'Masukkan' jika tidak ada mahasiswa yang dipilih
    const manualForm = document.querySelector('.manual-insert-form');
    const npmSelect = document.querySelector('select[name="npm_target"]');
    const kelompokSelect = document.querySelector('select[name="kelompok_target"]');
    const insertBtn = document.querySelector('button[name="insert_manual"]');

    if (manualForm && insertBtn) {
        const checkValidity = () => {
            // Tombol aktif jika mahasiswa dipilih DAN kelompok dipilih
            insertBtn.disabled = !npmSelect.value || !kelompokSelect.value;
        };

        npmSelect.addEventListener('change', checkValidity);
        kelompokSelect.addEventListener('change', checkValidity);
        
        checkValidity(); // Panggil di awal untuk inisialisasi status tombol
    }
});