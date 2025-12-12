<?php
require_once 'conn.php';

$selectedOption = $_POST["komponen-rubrik"] ?? "";
$textareaContent = "";

$query = "SELECT namaKomponen FROM komponenPenilaian";
$result = mysqli_query($conn, $query);
$options = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $options[] = $row;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['komponen-rubrik'])) {
        $selected_component = mysqli_real_escape_string($conn, $_POST['komponen-rubrik']);
        $query_detail = "SELECT rubrik FROM komponenPenilaian WHERE namaKomponen = '$selected_component'";
        $result_detail = mysqli_query($conn, $query_detail);
        
        if ($result_detail && mysqli_num_rows($result_detail) > 0) {
            $row_detail = mysqli_fetch_assoc($result_detail);
            $textareaContent = $row_detail['rubrik'];
        }
    }

    if (isset($_POST['simpan'])) {
        $selected_component = $_POST['komponen-rubrik'] ?? '';
        $edited_text = $_POST['textarea_content'] ?? '';
        
        if (!empty($selected_component) && !empty($edited_text)) {
            $edit_query = "UPDATE komponenPenilaian SET rubrik = ? WHERE namaKomponen = ?";
            $stmt = mysqli_prepare($conn, $edit_query);
            mysqli_stmt_bind_param($stmt, "ss", $edited_text, $selected_component);
            
            if (mysqli_stmt_execute($stmt)) {
                $textareaContent = $edited_text;
                echo "<script>alert('Data berhasil disimpan!');</script>";
            } else {
                echo "<script>alert('Gagal menyimpan data!');</script>";
            }
            mysqli_stmt_close($stmt);
        }
    }
} else {
    if (isset($_GET['komponen-rubrik'])) {
        $selected_component = mysqli_real_escape_string($conn, $_GET['komponen-rubrik']);
        $query_detail = "SELECT rubrik FROM komponenPenilaian WHERE namaKomponen = '$selected_component'";
        $result_detail = mysqli_query($conn, $query_detail);
        
        if ($result_detail && mysqli_num_rows($result_detail) > 0) {
            $row_detail = mysqli_fetch_assoc($result_detail);
            $textareaContent = $row_detail['rubrik'];
            $selectedOption = $_GET['komponen-rubrik'];
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="main.css">
    <style>
        .back-btn{
            position: absolute;
            top: 40px;
            left: 29px;
            text-decoration: none;
            color: #666;
            font-weight: 600;
            font-size: 20px;
        }

        h1{
            color: var(--green-text);
            margin-bottom: 5%;
        }

        select{
            color: white;
        }

        .height100 {
            height: 100%;
        }

        body{
            display: flex;
            justify-content: center;
            align-items: end;
        }

        main{
            width: 75%;
            height: 80%;
        }

        .box{
            width: 100%;
            height: 70%;
            padding: 20px;
            margin: 5px;
            resize: none;
        }

        .btn{
            width: 100px;
            color: white;
            background-color: var(--green-button-color);
            display: flex;
            justify-self: end;
        }
    </style>
</head>
<body>
    <a class="back-btn" href="admin/admin.php">⮌</a>
    <main>
        <form class="height100" method="POST" action="">
            <h1><?php echo $_GET["namaTugasBesar"]??"Nama Tugas Besar"?></h1>

            <select name="komponen-rubrik" onchange="this.form.submit()">
                <option value="">-- Komponen Rubrik --</option>
                <?php foreach ($options as $option): ?>
                    <option value="<?php echo $option['namaKomponen']; ?>" 
                        <?php echo ($selectedOption == $option['namaKomponen']) ? 'selected' : ''; ?>>
                        <?php echo $option['namaKomponen']; ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <textarea class="box" name="textarea_content" placeholder="text area"><?php 
                echo $textareaContent; 
            ?></textarea>

            <input class="btn" type="submit" name="simpan" value="Simpan">
        </form>
        
    </main>
</body>
</html>


<?php
mysqli_close($conn);
?>