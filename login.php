<?php

require('connect.php');

session_start();

$message = "";

if ($_POST) {
    $userName = $_POST['userName'];
    $password = $_POST['password'];

    $query = "SELECT * FROM accounts WHERE user_Name LIKE :userName";
    $statement = $db->prepare($query);
    $statement->bindValue(':userName', $userName);
    $statement->execute();
    $row = $statement->fetch();

    if($row == false){
        $message = "Incorrect username";
    }else{      
        $query = "SELECT * FROM accounts WHERE password LIKE :password";
        $statement = $db->prepare($query);
        $statement->bindValue(':password', $password);
        $statement->execute();
        $row = $statement->fetch();
        
        if($row == false){
            $message = "Incorrect password";
        }else{
            $_SESSION['userName'] = $userName;
            if($row['type'] == 'admin'){
                $_SESSION['type'] = $row['type'];
                header('Location: admin.php'); 
            }else if ($row['type'] == 'service provider'){
                $_SESSION['type'] = $row['type'];
                $_SESSION['Id'] = $row['service_Provider_Id'];
                header('Location: serviceProvider.php?id=' . $row['service_Provider_Id']);
            }else if ($row['type'] == 'customer'){
                $_SESSION['type'] = $row['type'];
                $_SESSION['Id'] = $row['service_Provider_Id'];
                header('Location: serviceProvider.php?id=' . $row['service_Provider_Id']); 
            }
        }
    }
}
?>


<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="style.css" />
    <title>Login</title>
</head>
<body>
    <?php if(!isset($_SESSION['userName'])):?>
        <h2>Login</h2>
        <form method="post">
            <label for="userName">Username:</label>
            <input type="text" name="userName">
            <label for="password">Password:</label>
            <input type="password" name="password">
            <input type="submit" value="Login">
            <p><?= $message?></p>
        </form>
    <?php else:?>
        <h2>You are already login as <?= ($_SESSION['userName'])?></h2>
    <?php endif ?>
</body>
</html>