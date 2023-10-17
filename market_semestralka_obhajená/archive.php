<?php

require 'inc/user.php';


include __DIR__ . '/inc/header.php';

if (empty($_SESSION['user_id'])) {
    header("Location: index.php?error=login");
    exit();
}

if (!empty($_POST)){
    if (!empty($_POST['archive'])){
        $queryItem = $db->prepare('SELECT * FROM goods WHERE id=:id LIMIT 1;');
        $queryItem->execute([
            ':id' => $_GET['id']
        ]);
        $item = $queryItem->fetch(PDO::FETCH_ASSOC);

        $queryDelete = $db->prepare('DELETE FROM goods WHERE id=:id LIMIT 1;');
        $queryDelete->execute([
            ':id' => $_GET['id']
        ]);

        $queryArchive = $db->prepare('INSERT INTO archive (id,name,description,price,buyer_id,category_id,user_id) VALUES (:id, :name, :description, :price, :buyer, :category, :user)');
        $queryArchive->execute([
            ':id'=>$item['id'],
            ':name'=>$item['name'],
            ':description'=>$item['description'],
            ':price'=>$item['price'],
            ':buyer'=>$item['interested_id'],
            ':category'=>$item['category_id'],
            ':user'=>$item['user_id']
        ]);
    }

    if (!empty($_POST['ano'])){
        $queryItem = $db->prepare('UPDATE goods SET interested_id=:interested WHERE id=:id LIMIT 1;');
        $queryItem->execute([
            ':interested' => '0',
            ':id' => $_GET['id']
        ]);

    }

}


if ((!empty($_GET['id'])) && (!empty($_GET['prodano']))) {
    $queryGoods = $db->prepare('SELECT * FROM goods WHERE id=:id LIMIT 1;');
    $queryGoods->execute([
        ':id' => $_GET['id']
    ]);
    $goods = $queryGoods->fetch(PDO::FETCH_ASSOC);
    if (!empty($goods)) {

        if (($goods['interested_id'] > 0) && ($goods['user_id'] == $_SESSION['user_id']) && $_GET['prodano']=="ano") {

            echo '<form method="post">
                         <div class="form-group">
                         <input type="hidden" name="archive" value="archive">
                        <label  class="h4 form-label">Opravdu se prodal předmět:   '.htmlspecialchars($goods['name']).'</label>
                        </div>
                        <div class="form-group">
                        <button type="submit" class="btn btn-primary">Archivovat</button>
                        <a href="index.php" class="btn btn-light">Zrušit</a>
                        </div>
                    </form>';



        } else {
            if (($goods['interested_id'] > 0) && ($goods['user_id'] == $_SESSION['user_id']) && $_GET['prodano']=="ne") {
                echo '<form method="post">
                         <div class="form-group">
                         <input type="hidden" name="ano" value="ano">
                        <label  class="h4 form-label">Opravdu se nepovedlo předmět '.htmlspecialchars($goods['name']).' prodat?</label>
                        </div>
                        <div class="form-group">
                        <button type="submit" class="btn btn-primary">Ano</button>
                        <a href="index.php" class="btn btn-light">Zrušit</a>
                        </div>
                    </form>';
            } else {
                header("Location: index.php");
                exit();
            }
        }

    } else {

       header("Location: index.php");
       exit();
    }


} else {

    header("Location: index.php");
    exit();
}


include __DIR__ . '/inc/footer.php';