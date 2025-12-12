<?php
require_once 'conn.php';

$namaTugasBesar = $_GET["namaTugasBesar"] ?? "";
$selectedOption = $_POST["dropdown"] ?? "";
$textareaContent = "";

$query = "SELECT namaKomponen FROM komponenPenilaian";
$result = mysqli_query($conn, $query);
$options = [];
while ($row = mysqli_fetch_assoc($result)) {
    $options[] = $row;
}

if ($selectedOption) {
    $query = "SELECT rubrik FROM komponenPenilaian WHERE namaKomponen = $selectedOption";
    $result = mysqli_query($conn, $query);
    if ($row = mysqli_fetch_assoc($result)) {
        $textareaContent = $row['rubrik'];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['textarea_content'])) {
    $newContent = mysqli_real_escape_string($conn, $_POST['textarea_content']);
    $updateQuery = "UPDATE komponenPenilaian SET rubrik = '$newContent' WHERE namaKomponen = $selectedOption";
    $textareaContent = $_POST['textarea_content'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="main.css">
    <style>
        h1{
            color: var(--green-text);
            margin-bottom: 5%;
        }

        h5{
            margin-bottom: 2%;
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
    <main>
        <form method="POST" action="">
            <h1><?php echo $_GET["namaTugasBesar"]??""?></h1>

            <select name="komponen-rubrik" onchange="this.form.submit()">
            <option value="">Komponen Rubrik</option>
                <?php foreach ($options as $option): ?>
                    <option value="<?php echo $option['namaKomponen']; ?>" 
                        <?php echo ($selectedOption == $option['namaKomponen']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($option['namaKomponen']); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <textarea class="box" name="textarea_content" placeholder="text area">
                <?php echo $selectedOption ? '' : 'disabled'; ?>>
                <?php echo htmlspecialchars($textareaContent); ?>
            </textarea>

            <input class="btn" type="submit" value="Simpan"
                <?php echo $selectedOption ? '' : 'disabled'; ?>>
        </form>
    </main>
</body>
</html>


<?php
mysqli_close($conn);
?>