<?php

require_once 'inc/user.php';

require_once 'inc/facebook.php';

if (!empty($_SESSION['user_id'])){

    header('Location: index.php');
    exit();
}

$errors=false;
if (!empty($_POST)){

    $userQuery=$db->prepare('SELECT * FROM users WHERE email=:email LIMIT 1;');
    $userQuery->execute([
        ':email'=>trim($_POST['email'])
    ]);
    if ($user=$userQuery->fetch(PDO::FETCH_ASSOC)){

        if (password_verify($_POST['password'],$user['password'])){
            //heslo je platné => přihlásíme uživatele
            $_SESSION['user_id']=$user['user_id'];
            $_SESSION['user_name']=$user['name'];
            header('Location: index.php');
            exit();
        }else{
            $errors=true;
        }

    }else{
        $errors=true;
    }
    #endregion zpracování formuláře
}

$fbHelper = $fb->getRedirectLoginHelper();

$permissions = ['email'];
$callbackUrl = htmlspecialchars('https://eso.vse.cz/~sinj04/market_semestralka/fb-callback.php');

$fbLoginUrl = $fbHelper->getLoginUrl($callbackUrl, $permissions);

//vložíme do stránek patičku
$pageTitle='Přihlášení uživatele';
include 'inc/header.php';
?>

    <h2>Přihlášení uživatele</h2>

    <form method="post">
        <div class="form-group">
            <label for="email">E-mail:</label>
            <input type="email" name="email" id="email" required class="form-control <?php echo ($errors?'is-invalid':''); ?>" value="<?php echo htmlspecialchars(@$_POST['email'])?>"/>
            <?php
            echo ($errors?'<div class="invalid-feedback">Neplatná kombinace přihlašovacího e-mailu a hesla.</div>':'');
            ?>
        </div>
        <div class="form-group">
            <label for="password">Heslo:</label>
            <input type="password" name="password" id="password" required class="form-control <?php echo ($errors?'is-invalid':''); ?>" />
        </div>
        <button type="submit" class="btn btn-primary">přihlásit se</button>
        <?php
         echo '<a href="'.$fbLoginUrl.'" class="btn btn-primary">Přihlášení pomocí Facebooku</a>';
        ?>
        <a href="registration.php" class="btn btn-light">registrovat se</a>
        <a href="index.php" class="btn btn-light">zrušit</a>
    </form>

<?php
//vložíme do stránek patičku
include 'inc/footer.php';
