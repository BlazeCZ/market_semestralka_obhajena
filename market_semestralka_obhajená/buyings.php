<?php

require 'inc/user.php';


include __DIR__ . '/inc/header.php';

if (empty($_SESSION['user_id'])){
    //uživatel není přihlášen
    header("Location: index.php?error=login");
    exit();
}

if ($_SESSION['user_id'] == $_GET['id']) {


    echo '<div class="container-fluid">';
    echo '<div class="row flex-nowrap">';
    echo '<div class="col ps-md-3 pt-3">';
    echo '<h2>Moje nákupy</h2>';
    $queryGoods = $db->prepare('SELECT * FROM archive WHERE buyer_id=:id ORDER BY id DESC;');
    $queryGoods->execute([
        ':id' => $_SESSION['user_id']
    ]);
    $goods = $queryGoods->fetchAll(PDO::FETCH_ASSOC);
    if (!empty($goods)) {

        echo '<div class="row">';
        foreach ($goods as $item) {
            $filename = "inc/uploaded_files/image.jpg";
            $id = $item['id'];
            $files = glob("inc/uploaded_files/$id.*");
            foreach ($files as $file){
                $filename = $file;
            }

            echo '<div class="col-sm-4 text-dark" >';
            echo '<div class="card" <!--style="width: 18rem;"-->';
            echo '<a class="card-title h3 text-dark" href="item.php?archiveid=' . $item['id'] . '"> <img src="'.$filename.'" alt="photo" class="card-img-top img-thumbnail"></a>';
            echo '<div class="card-body">
                    <a class="card-title h3 text-dark" href="item.php?archiveid=' . $item['id'] . '">' . htmlspecialchars($item['name']) . '</a>
                    <p class="card-text">' . htmlspecialchars($item['description']) . '</p>
                    <p class="card-text">Price: ' . htmlspecialchars($item['price']) . '</p>
';

            echo '</div>';;
            echo '</div></div>';
        }
        echo '</div>'; //col ps-md-3 pt-3
        echo '</div>'; //row

    } else {
        echo '<p>Nevedem u vás žádné provedené nákupy.</p>';
    }


    echo '</div></div>'; //container, row flex
} else {
    header("Location: index.php");
    exit();
}
include __DIR__ . '/inc/footer.php';