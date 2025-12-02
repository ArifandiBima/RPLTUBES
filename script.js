/* Script.js untuk dosen_edit_kelompok.php
    
    Catatan: Semua fungsionalitas utama (Auto Fill, Lock Team, Insert Manual)
    telah menggunakan form submission dan konfirmasi inline di file PHP.
    
    File ini dapat digunakan untuk menambahkan fitur frontend yang lebih kompleks 
    di masa depan, seperti:
    
    1. Konfirmasi penghapusan anggota menggunakan modal.
    2. Filtrasi mahasiswa yang belum berkelompok.
    3. AJAX submission untuk update tanpa reload halaman.
*/

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