<?php

require('connect.php');
session_start();

if (!isset($_SESSION['userName'])) {
    header('Location: login.php');
    exit();
}

if ($_POST && isset($_GET['id'])) {
    $id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
    $userName = filter_input(INPUT_POST, 'userName', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $type = filter_input(INPUT_POST, 'type', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    if($_POST['command'] == 'Update'){
        if(trim($_POST['name']) == null){
            header("Location: error.php");
        }else{
            $query = "UPDATE accounts SET user_Name = :userName, password = :password, type = :type WHERE id = :id";
    
            $statement = $db->prepare($query);
            $statement->bindValue(':id', $id, PDO::PARAM_INT);
            $statement->bindValue(":userName", $userName);
            $statement->bindValue(":password", $password);
            $statement->bindValue(":type", $type);
           
            $statement->execute();

            header("Location: admin.php?table=accounts&column=user_Name");
        }
    }else{
        $query = "DELETE FROM accounts WHERE id = :id LIMIT 1";

        $statement = $db->prepare($query);
        $statement->bindValue(':id', $id, PDO::PARAM_INT);

        $statement->execute();

        header("Location: admin.php?table=accounts&column=user_Name");
    }
} else if (isset($_GET['id'])) { 
    if($id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT)){
        $query = "SELECT * FROM accounts WHERE id = :id";
        $statement = $db->prepare($query);
        $statement->bindValue(':id', $id, PDO::PARAM_INT);
        
        $statement->execute();
        $row = $statement->fetch();
    }else{
        header("Location: admin.php?table=accounts&column=user_Name");
    }
} 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Edit this account</title>
</head>
<body>
    <div id="container">
    <?php include('adminNavigation.php')?>
        <ul id="menu">
            <li><a href="admin.php">Back</a></li>
            <li><a href="create_accounts.php">Create New</a></li>
        </ul>
        <div>
        <form method="post">
            <p>
                <p>
                <label for="userName">User Name</label>
                <input name="userName" id="userName" value="<?= $row['user_Name']?>">
                </p>
                <p>
                <label for="password">Password</label>
                <input name="password" id="password" value="<?= $row['password']?>">
                </p>
                <p>
                <label for="type">Type</label>
                <input type="password" name="type" id="type" value="<?= $row['type']?>">
                </p>
                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                <input type="submit" name="command" value="Update">
                <input type="submit" name="command" value="Delete" onclick="return confirm('Are you sure you wish to delete this?')">
            </p>
        </form>
        </div>  
    </div>  
</body>
</html>