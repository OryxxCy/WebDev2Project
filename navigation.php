<?php

$loginAction = "login.php";
$loginButtonMessage = "Login";

if(isset($_SESSION['userName'])){
    $loginAction = "logout.php";
    $loginButtonMessage = "Logout";
}

?>
<header>
    <h2 class="logo">ServicesFinders</h2>
    <nav class="navigation">
        <form method="post" action="<?= $loginAction?>">
            <a href="index.php">Home</a>
            <a href="#">Contact</a>
            <a href="#">Register</a>
            <button type="submit" class="loginButton"><?= $loginButtonMessage?></button>
        </form>
    </nav>
</header>