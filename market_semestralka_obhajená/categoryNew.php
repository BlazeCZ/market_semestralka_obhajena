<?php


require_once 'inc/user.php';

if (empty($_SESSION['user_id'])) {
    header("Location: index.php?error=login");
    exit();
}

if ($currentUser['role'] != 'admin') {
    header("Location: index.php?error=admin");
    exit();
}



include 'inc/header.php';

$phase = array (
    "new" => "Kategorie byla vytvořena.",
    "exist" => "Kategorie byla upravena.",
    "delete" => "Kategorie byla smazána."
);

if (!empty($_GET['phase'])) {
    echo '<div class="alert alert-success alert-dismissible fade show" role="alert">  <button type="button" class="close" data-dismiss="alert">&times;</button>' .$phase[$_GET['phase']].'
  </div>';
}

$categoryQuery=$db->prepare('SELECT * FROM categories;');
$categoryQuery->execute();
$categories=$categoryQuery->fetchAll(PDO::FETCH_ASSOC);
if (!empty($categories)) {
    echo '<table class="table table-striped w-auto">';
    foreach ($categories as $category) {
        echo '     <tr>
                     <td class="lead font-weight-bold">' . htmlspecialchars($category['name']) . '</td>
                     <td><a href="edit_category.php?id=' . $category['category_id'] . '" class="btn btn-warning">Upravit</a></td>
                     <td><a href="delete.php?categoryid=' . $category['category_id'] . ' " class="btn btn-danger">Smazat</a></td>
                   </tr>';
    };
    echo '</table>';
}

echo '<div class="btn-group">
            <a href="edit_category.php" class="btn btn-primary">Přidat kategorii</a>
          ';
echo '<a href="index.php" class="btn btn-light">Zrušit</a></div>';


include 'inc/footer.php';