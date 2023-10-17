<?php
//načteme připojení k databázi a inicializujeme session
require_once 'inc/user.php';

if (empty($_SESSION['user_id'])){
    //uživatel není přihlášen
    header("Location: index.php?error=login");
    exit();
}


if ($currentUser['role'] != 'admin') {
    header("Location: index.php?error=admin");
    exit();
}

$categoryId='';
$categoryName='';

if (!empty($_REQUEST['id'])){
    $pageTitle='Úprava kategorie';
    $categoryQuery=$db->prepare('SELECT * FROM categories WHERE category_id=:id LIMIT 1;');
    $categoryQuery->execute([':id'=>$_REQUEST['id']]);
    if ($category=$categoryQuery->fetch(PDO::FETCH_ASSOC)){
        //naplníme pomocné proměnné daty příspěvku
        $categoryId=$category['category_id'];
        $categoryName=$category['name'];

    }else{
        header("Location: categoryNew.php?error=nonexist");
        exit();
    }
}else{
    $pageTitle='Nová kategorie';
}




$errors=[];
if (!empty($_POST)){
    $categoryName=trim(@$_POST['name']);
    if (empty($categoryName)){
        $errors['name']='Musíte zadat název kategorie.';
    }

    if (empty($errors)){

        if ($categoryId){
            #region aktualizace existujícího příspěvku
            $saveQuery=$db->prepare('UPDATE categories SET name=:name WHERE category_id=:id LIMIT 1;');
            $saveQuery->execute([
                ':name'=>$categoryName,
                ':id'=>$categoryId
            ]);

            header('Location: categoryNew.php?phase=exist');
            exit();
            #endregion aktualizace existujícího příspěvku
        }else{
            #region uložení nového příspěvku
            $saveQuery=$db->prepare('INSERT INTO categories (name) VALUES (:name);');
            $saveQuery->execute([
                ':name'=>$categoryName
            ]);

            header('Location: categoryNew.php?phase=new');
            exit();
            #endregion uložení nového příspěvku
        }


    }
    #endregion zpracování formuláře
}



include 'inc/header.php';
?>

<form method="post">

    <div class="form-group">
        <label for="name">Název kategorie:</label>
        <textarea name="name" id="name" required class="form-control <?php echo (!empty($errors['name'])?'is-invalid':''); ?>"><?php echo htmlspecialchars($categoryName)?></textarea>
        <?php
        if (!empty($errors['text'])){
            echo '<div class="invalid-feedback">'.$errors['name'].'</div>';
        }
        ?>
    </div>

    <button type="submit" class="btn btn-primary">uložit...</button>
    <a href="categoryNew.php" class="btn btn-light">zrušit</a>
</form>




<?php
include 'inc/footer.php';