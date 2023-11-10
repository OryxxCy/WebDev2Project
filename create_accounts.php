<?php

require('connect.php');
session_start();

if (!isset($_SESSION['userName'])) {
    header('Location: login.php');
    exit();
}

    if ($_POST) {
        if (trim($_POST['userName']) == null) {
            header("Location: error.php");
        }else{
             $userName = filter_input(INPUT_POST, 'userName', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
             $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
             $type = filter_input(INPUT_POST, 'type', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

             $query = "INSERT INTO accounts (user_Name, password, type) VALUES (:userName, :password, :type)";
             $statement = $db->prepare($query);

             $statement->bindValue(":userName", $userName);
             $statement->bindValue(":password", $password);
             $statement->bindValue(":type", $type);

            if($statement->execute()){
                 header("Location: admin.php?table=accounts&column=user_Name");
            }
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Add an account</title>
</head>
<body>
    <div id="container">
    <?php include('adminNavigation.php')?>
    <a href="admin.php">Back</a>
        <div>
            <form method="post">
                <h2>New Account</h2>
                <p>
                <label for="userName">User Name</label>
                <input name="userName" id="userName">
                </p>
                <p>
                <label for="password">Password</label>
                <input name="password" id="password">
                </p>
                <p>
                <label for="type">Type</label>
                <input name="type" id="type">
                </p>
                <p>
                <input type="submit" value="Create">
                </p>
            </form>
        </div>
    </div>
</body>
</html>