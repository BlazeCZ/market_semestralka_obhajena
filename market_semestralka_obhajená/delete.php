<?php

require 'inc/user.php';

if (empty($_SESSION['user_id'])){
    //uživatel není přihlášen
    header("Location: index.php?error=login");
    exit();
}
if (!empty($_GET['id'])) {
    $query = $db->prepare('SELECT * FROM goods WHERE id=?');
    $query->execute(array($_GET['id']));
    $item = $query->fetch(PDO::FETCH_ASSOC);


    if ($_SESSION['user_id'] == $item['user_id']) {
        $query = $db->prepare('DELETE FROM goods WHERE id=?');
        $query->execute(array($_GET['id']));
        header("Location: index.php");
    } else {
        header("Location: index.php");
        exit();
    }
} else {
   /* header("Location: index.php");
    exit();*/



if (!empty($_GET['categoryid'])) {
    $categoryQuery = $db->prepare('SELECT * FROM categories WHERE category_id=? LIMIT 1;');
    $categoryQuery->execute([$_GET['categoryid']]);
    if ($category = $categoryQuery->fetch(PDO::FETCH_ASSOC)) {
        $categoryId = $category['category_id'];
    } else {
        exit('Kategorie neexistuje.');
    }
    if ($currentUser['role'] == 'admin') {
        $stmt = $db->prepare("DELETE FROM categories WHERE category_id=?");
        $stmt->execute([$_GET['categoryid']]);
        header('Location: categoryNew.php?phase=delete');
    } else {
        header("Location: index.php?error=admin");
        exit();
    }

}
}