<?php

require('connect.php');
session_start();

if (isset($_SESSION['userName'])) {
    if($_SESSION['type'] != 'admin'){
        header('Location: login.php');
        exit();
    }
}

if ($_POST) {
    if (trim($_POST['name']) == null) {
        header("Location: error.php");
    }else{
            $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $phoneNumber = filter_input(INPUT_POST, 'phoneNumber', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

            $query = "INSERT INTO customers (name, phone_Number) VALUES (:name, :phoneNumber)";
            $statement = $db->prepare($query);

            $statement->bindValue(":name", $name);
            $statement->bindValue(":phoneNumber", $phoneNumber);
            
        if($statement->execute()){
            $id = $db->lastInsertId();
            $userName = filter_input(INPUT_POST, 'userName', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $type = "customer";

            $query = "INSERT INTO accounts (user_Name, password, type, service_Provider_Id, customer_Id) VALUES (:userName, :password, :type, :id)";
            $statement = $db->prepare($query);

            $statement->bindValue(":userName", $userName);
            $statement->bindValue(":password", $password);
            $statement->bindValue(":type", $type);
            $statement->bindValue(":id", $id);

            if($statement->execute()){
                if($_SESSION['type'] == 'admin')
                {
                    header("Location: admin.php?table=customers&column=name");
                }else{
                    $_SESSION['userName'] = $userName;
                    $_SESSION['type'] = $type;
                    $_SESSION['Id'] = $id;
                    header('Location: customer.php?id=' . $id);
                }       
            }
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
    <title>Add a Customer</title>
</head>
<body>
    <div id="container">
    <?php if(isset($_SESSION['userName'])):?>
        <?php include('adminNavigation.php')?>
        <a href="admin.php">Back</a>
    <?php else:?>
        <a href="index.php">Back</a>
    <?php endif?>
        <div>
            <form method="post">
                <h2>New Customer</h2>
                <p>
                <label for="name">Customer Name</label>
                <input name="name" id="name">
                </p>
                <p>
                <label for="phoneNumber">Phone number</label>
                <input name="phoneNumber" id="phoneNumber">
                </p>
                <p>
                <label for="userName">User Name</label>
                <input name="userName" id="userName">
                </p>
                <p>
                <label for="password">Password</label>
                <input name="password" id="password" type="password">
                </p>
                <p>
                <label for="confirmPassword">Confirm Password</label>
                <input name="confirmPassword" id="confirmPassword" type="password">
                </p>
                <p>
                <input type="submit" value="Create">
                </p>
            </form>
        </div>
    </div>
</body>
</html>