<?php

require 'inc/user.php';

if (empty($_SESSION['user_id'])){
    //uživatel není přihlášen
    header("Location: index.php?error=login");
    exit();
}



if (!empty($_GET['id'])) {

    $queryArchive = $db->prepare('SELECT * FROM archive WHERE (buyer_id=:id) AND (user_id=:user)');
    $queryArchive->execute ([
        ':id'=> $_SESSION['user_id'],
        ':user'=>$_GET['id']
    ]);
    $archive = $queryArchive->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($archive)){
        $queryReview = $db->prepare('SELECT * FROM review WHERE (user_id=:user) AND (evaluator_id=:id)');
        $queryReview->execute ([
            ':user'=>$_GET['id'],
            ':id'=>$_SESSION['user_id']
        ]);
        $reviewDone = $queryReview->fetch(PDO::FETCH_ASSOC);
    } else { $reviewDone = 1;}

    $queryUser= $db->prepare('SELECT * FROM users WHERE user_id=:id LIMIT 1;');
    $queryUser->execute([
        ':id' => $_GET['id']
    ]);
    $user = $queryUser->fetch(PDO::FETCH_ASSOC);

    if (empty($reviewDone)){

    $errors = [];
    if (!empty($_POST)) {

        if(empty($_POST['recomend'])) {
            $errors['recomend'] = 'Musíte vybrat jednu z možností.';
        }

        $reviewText = trim(@$_POST['text']);
        if (empty($reviewText)) {
            $errors['text'] = 'Je potřeba zadat alespoň nějaké hodnocení.';
        }



        if (empty($errors)) {
            $saveQuery = $db->prepare('INSERT INTO review (user_id, text, evaluator_id, recomend) VALUES (:user, :text, :evaluator, :recomend);');
            $saveQuery->execute([
                ':user' => $user['user_id'],
                ':text' => $reviewText,
                ':evaluator' => $_SESSION['user_id'],
                ':recomend' => $_POST['recomend']
            ]);

            header('Location: profile.php?id=' . $_GET['id']);
            exit();
        }
    }

    include __DIR__ . '/inc/header.php';

    $postCategory = 0;
} else {
        header('Location: profile.php?id=' . $_GET['id']);
    exit();
}

?>
<h2>Hodnocení uživatele:  <?php echo ($user['name'])?></h2>
    <form method="post" >
        <div class="form-group">
            <label for="recomend">Doporučil by jste tohoto prodejce dalším uživatelům?</label>
            <select name="recomend" id="recomend" required class="form-control <?php echo (!empty($errors['recomend'])?'is-invalid':''); ?>">
                <option value="">--vyberte--</option>
                <option value="ano">Ano</option>
                <option value="ne">Ne</option>
            </select>
            <?php
            if (!empty($errors['recomend'])){
                echo '<div class="text-danger"><small>'.$errors['recomend'].'</small></div>';
            }
            ?>
        </div>
        <div class="form-group">
            <label for="text">Slovní hodnocení:</label>
            <textarea name="text" id="text" required class="form-control" ><?php echo isset($_POST["text"]) ? $_POST["text"] : ''; ?></textarea>
            <?php
            if (!empty($errors['text'])){
                echo '<div class="text-danger"><small>'.$errors['text'].'</small></div>';
            }
            ?>
        </div>
        <button type="submit" class="btn btn-primary">Vložit</button>
        <a href="profile.php?id=<?php echo $_GET['id'];?>" class="btn btn-light">zrušit</a>
    </form>

<?php
} else {
    header('Location: index.php');
    exit();
}
include __DIR__ . '/inc/footer.php';