<?php

require 'inc/user.php';

if (empty($_SESSION['user_id'])){
    //uživatel není přihlášen
    header("Location: index.php?error=login");
    exit();
}
if (!empty($_GET['id']) ) {
    $query = $db->prepare('SELECT * FROM goods WHERE id=?');
    $query->execute(array($_GET['id']));
    $item = $query->fetch(PDO::FETCH_ASSOC);
} else {
    header("Location: index.php");
    exit();
}

if ($_SESSION['user_id']==$item['user_id'] || $currentUser['role'] == 'admin') {


$itemId='';
$itemCategory=(!empty($_REQUEST['category'])?intval($_REQUEST['category']):'');
$itemTitle='';
$itemText='';
$itemPrice='';


$errors=[];
if (!empty($_POST)) {

    if (!empty($_POST['category'])) {

        $categoryQuery = $db->prepare('SELECT * FROM categories WHERE category_id=:category LIMIT 1;');
        $categoryQuery->execute([
            ':category' => $_POST['category']
        ]);
        if ($categoryQuery->rowCount() == 0) {
            $errors['category'] = 'Zvolená kategorie neexistuje!';
            $itemCategory = '';
        } else {
            $itemCategory = $_POST['category'];
        }

    } else {
        $errors['category'] = 'Musíte vybrat kategorii.';
    }

    $itemText = trim(@$_POST['text']);
    if (empty($itemText)) {
        $errors['text'] = 'Musíte zadat alespoň nějaké informace o položce.';
    }

    $itemTitle = trim(@$_POST['title']);
    if (empty($itemTitle)) {
        $errors['title'] = 'Musíte zadat nadpis položky.';
    }

    $itemPrice = trim(@$_POST['price']);
    if (empty($itemPrice) || !is_numeric($itemPrice)) {
        $errors['price'] = 'Musíte zadat požadovanou cenu v korunách.';
    }
    if (empty($errors)) {
        $saveQuery = $db->prepare('UPDATE goods SET name=:title, description=:text, price=:price, category_id=:category WHERE id=:id;');
        $saveQuery->execute([
            ':title' => $itemTitle,
            ':text' => $itemText,
            ':price' => $itemPrice,
            ':category' => $itemCategory,
            'id' => $_GET['id']
        ]);

        $loadQuery = $db->prepare('SELECT * FROM goods WHERE name=:title AND description=:text AND price=:price AND category_id=:category AND user_id=:user ');
        $loadQuery->execute([
            ':title' => $itemTitle,
            ':text' => $itemText,
            ':price' => $itemPrice,
            ':category' => $itemCategory,
            ':user' => $_SESSION['user_id']
        ]);
        $load = $loadQuery->fetch(PDO::FETCH_ASSOC);

        if (isset($_FILES['uploadedFile']) ){
            $fileTmpPath = $_FILES['uploadedFile']['tmp_name'];
            $fileName = $_FILES['uploadedFile']['name'];
            $fileSize = $_FILES['uploadedFile']['size'];
            $fileType = $_FILES['uploadedFile']['type'];
            $fileNameCmps = explode(".", $fileName);
            $fileExtension = strtolower(end($fileNameCmps));

            $newFileName = $load['id'] . '.' . $fileExtension;

            $allowedfileExtensions = array('jpg', 'png');
            if (in_array($fileExtension, $allowedfileExtensions)) {
                $uploadFileDir = 'inc/uploaded_files/';
                $dest_path = $uploadFileDir . $newFileName;
                if(file_exists($dest_path)) {
                    unlink($dest_path);
                }
                move_uploaded_file($fileTmpPath, $dest_path);
            }
            else
            {
                $error['upload'] = 'Nahrávání se nezdařilo. Povolené formáty: ' . implode(',', $allowedfileExtensions);
            }
        }


        header('Location: index.php?kategorie=' . $itemCategory);
        exit();
    }
}

include __DIR__ . '/inc/header.php';


$postCategory = 0;
?>

    <form method="post" enctype="multipart/form-data">
        <div class="form-group">
            <label for="category">Kategorie:</label>
            <select name="category" id="category" required class="form-control <?php echo (!empty($errors['category'])?'is-invalid':''); ?>">
                <option value="">--vyberte--</option>
                <?php
                $categoryQuery=$db->prepare('SELECT * FROM categories ORDER BY name;');
                $categoryQuery->execute();
                $categories=$categoryQuery->fetchAll(PDO::FETCH_ASSOC);
                if (!empty($categories)){
                    foreach ($categories as $category){
                        echo '<option value="' . $category['category_id'] . '" ' . ($category['category_id'] == $item['category_id'] ? 'selected="selected"' : '') . '>' . htmlspecialchars($category['name']) . '</option>';
                    }
                }
                ?>
            </select>
            <?php
            if (!empty($errors['category'])){
                echo '<div class="text-danger"><small>'.$errors['category'].'</small></div>';
            }
            ?>
        </div>

        <div class="form-group">
            <label for="title" class="form-label">Nadpis:</label>
            <input name="title" id="title" required class="form-control" value="<?php echo htmlspecialchars($item['name']); echo htmlspecialchars(@$_POST['title']); ?>">
            <?php
            if (!empty($errors['title'])){
                echo '<div class="text-danger"><small>'.$errors['title'].'</small></div>';
            }
            ?>
        </div>

        <div class="form-group">
            <label for="price" class="form-label">Cena:</label>
            <input name="price" id="price" required class="form-control" value="<?php echo htmlspecialchars($item['price']); echo htmlspecialchars(@$_POST['price']); ?>">
            <?php
            if (!empty($errors['price'])){
                echo '<div class="text-danger"><small>'.$errors['price'].'</small></div>';
            }
            ?>
        </div>

        <div class="form-group">
            <label for="text">Text příspěvku:</label>
            <textarea name="text" id="text" required placeholder=" <?php echo htmlspecialchars($item['description']) ?> "
                      class="form-control <?php echo(!empty($errors['text']) ? 'is-invalid' : ''); ?>"><?php echo htmlspecialchars($item['description']); echo htmlspecialchars(@$_POST['text']); ?></textarea>
            <?php
            if (!empty($errors['text'])) {
                echo '<div class="text-danger">' . $errors['text'] . '</div>';
            }
            ?>
            <div class="form-group mb-5">
                <label for="file-upload" class="form-label">Nahrazení fotografie novou</label>
                <input class="form-control" type="file" id="uploadedFile" name="uploadedFile" >
            </div>

        </div>
        <button type="submit" class="btn btn-primary">Uložit</button>
        <a href="index.php?category=<?php echo $postCategory;?>" class="btn btn-light">zrušit</a>
    </form>

<?php
include __DIR__ . '/inc/footer.php';
} else {
    header("Location: index.php?error=user");
    exit();
}