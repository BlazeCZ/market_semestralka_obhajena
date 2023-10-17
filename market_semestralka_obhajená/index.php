<?php

require 'inc/user.php';


include __DIR__ . '/inc/header.php';

$categories = $db->query('SELECT * FROM categories ORDER BY name;')->fetchAll(PDO::FETCH_ASSOC);

echo '<div class="container-fluid">';
echo '<div class="row flex-nowrap">';

if (!empty($categories)) {
    echo '<div class="border-primary border-5 col-auto col-md-3 col-xl-2 px-sm-2 px-0 bg-dark">';
    echo '<div id="sidebar" class="collapse collapse-horizontal show ">';
    echo '<div id="sidebar-nav" class="d-flex flex-column align-items-center align-items-sm-start px-3 pt-2 text-white min-vh-50">';
    echo '<ul class="nav nav-pills flex-column mb-sm-auto mb-0 text-center" id="menu">';
    foreach ($categories as $category) {
        $selected = "";
        if (!empty($_GET['kategorie'])){
            if ($category['category_id']==$_GET['kategorie']) {
                $selected = "bg-secondary rounded-pill";
            }
        }


        echo '    <li class="nav-item '.$selected.'">
                    <a href="./?kategorie=' . htmlspecialchars($category['category_id']) . '" class="nav-link align-middle px-0 text-light" data-bs-parent="#sidebar"><span class="ms-1 d-none d-sm-inline">' . htmlspecialchars($category['name']) . '</span> </a>
                  </li>';
    }
    echo '<li class="nav-item mt-10"><a href="archivedItems.php" class="nav-link align-middle px-0 text-light" data-bs-parent="#sidebar"><span class="ms-1 d-none d-sm-inline">Archiv</span></a></li>';
    if (!empty($_SESSION['user_id'])) {
        if ($currentUser['role'] == 'admin') {
            echo '<li class="nav-item mt-10"><a href="categoryNew.php" class="nav-link align-middle px-0 text-light" data-bs-parent="#sidebar"><span class="ms-1 d-none d-sm-inline btn-primary btn">Úprava kategorii</span></a></li>';
        }
    }
    echo '</ul></div></div></div>';
}
if (!empty($_GET)) {
    if (!empty($_GET['search']) && empty($_GET['kategorie']) ) {
        $keywords = $_GET['search'];
    $query= $db->prepare("SELECT * FROM goods WHERE (description LIKE '%$keywords%' OR name LIKE '%$keywords%');");
    $query->execute([
    ]);
        $goods = $query;
    } else {
        if (!empty($_GET['search']) && !empty($_GET['kategorie'])) {
            $keywords = $_GET['search'];
            $query = $db->prepare("SELECT * FROM goods WHERE (description LIKE '%$keywords%' OR name LIKE '%$keywords%') AND (category_id=:category) ORDER BY id DESC;");
            $query->execute([
                ':category' => $_GET['kategorie']
            ]);
            $goods = $query;
        } else {
            if (empty($_GET['search']) && !empty($_GET['kategorie'])) {
                $query = $db->prepare('SELECT * FROM goods WHERE category_id=:category ORDER BY id DESC;');
                $query->execute([
                    ':category' => $_GET['kategorie']
                ]);
                $goods = $query;
            }
        }
    }

} else {
    $query = $db->prepare('SELECT * FROM goods ORDER BY id DESC;');
    $query->execute();
    $goods = $query;
}


echo '<div class="col ps-md-3 pt-3">';
if (!empty($_SESSION['user_id'])) {
    echo '<div class="row my-3">
            <a href="new.php?category=' . @$_GET['kategorie'] . '" class="btn btn-primary">Přidat položku</a>
          </div>';
}
echo '<h2 class="">Položky na prodej</h2>';
echo '<div class="row">';

if (!empty($goods)) {

    foreach ($goods as $item) {
        $filename = "inc/uploaded_files/image.jpg";
        $id = $item['id'];
        $files = glob("inc/uploaded_files/$id.*");
        foreach ($files as $file){
            $filename = $file;
        }
        echo '<a class="col-sm-4 text-dark" href="item.php?id=' . $item['id'] . '">';

        echo '<div class="card" <!--style="width: 18rem;"-->';
        echo ' <img src="'.$filename.'" alt="photo" class="card-img-top img-thumbnail">';
        echo '<div class="card-body">
                    <h3 class="card-title">' . htmlspecialchars($item['name']) . '</h3>
                    <p class="card-text">' . htmlspecialchars($item['description']) . '</p>
                    <p class="card-text">Cena: ' . htmlspecialchars($item['price']) . '</p>
              </div>';
        ;echo '</div></a>';
    }
}

echo '</div>'; //row
echo '</div>'; //col ps-md-3 pt-3




echo '</div></div>'; //container, row flex


include __DIR__ . '/inc/footer.php';