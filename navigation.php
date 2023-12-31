<?php

$loginAction = "login.php";
$loginButtonMessage = "Login";
$registerAction = "register.php";
$registerMessage = "Register";

if(isset($_SESSION['userName'])){
    $loginAction = "logout.php";
    $loginButtonMessage = "Logout";
    $registerMessage = $_SESSION['userName'];

    if($_SESSION['type'] == 'service provider'){
        $registerAction = "serviceProvider.php?id=" . $_SESSION['Id'];
    }else if($_SESSION['type'] == 'customer'){
        $registerAction = "customer.php?id=" . $_SESSION['Id'];
    }else{
        $registerAction = "admin.php";
    }
}

?>
<header>
    <h2 class="logo">ServicesFinders</h2>
    <nav class="navigation">
        <form method="post" action="<?= $loginAction?>">
            <a href="index.php">Home</a>
            <a href="#">Contact</a>
            <a href="<?= $registerAction?>"><?= $registerMessage?></a>
            <button type="submit" class="loginButton"><?= $loginButtonMessage?></button>
        </form>
    </nav>
</header>