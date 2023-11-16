<?php

require('connect.php');
session_start();

if (isset($_SESSION['userName'])) {
    if($_SESSION['type'] != 'admin'){
        header('Location: login.php');
        exit();
    } 
}

$passwordError = "";

if ($_POST) {
    if ($_POST['password'] !=  $_POST['confirmPassword']) {
        $passwordError = "The passwords do not match.";
    }else{
            $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $location = filter_input(INPUT_POST, 'location', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $phoneNumber = filter_input(INPUT_POST, 'phoneNumber', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

            $query = "INSERT INTO service_providers (name, description, location, phone_Number, email_Address) VALUES (:name, :description, :location, :phone_Number, :email_Address)";
            $statement = $db->prepare($query);

            $statement->bindValue(":name", $name);
            $statement->bindValue(":description", $description);
            $statement->bindValue(":location", $location);
            $statement->bindValue(":phone_Number", $phoneNumber);
            $statement->bindValue(":email_Address", $email);

        if($statement->execute()){
            $id = $db->lastInsertId();
            $userName = filter_input(INPUT_POST, 'userName', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $type = "service provider";

            $query = "INSERT INTO accounts (user_Name, password, type, service_Provider_Id) VALUES (:userName, :password, :type, :id)";
            $statement = $db->prepare($query);

            $statement->bindValue(":userName", $userName);
            $statement->bindValue(":password", $password);
            $statement->bindValue(":type", $type);
            $statement->bindValue(":id", $id);

            if($statement->execute()){
                if($_SESSION['type'] == 'admin')
                {
                    header("Location: admin.php?table=service_providers&column=name");
                }else{
                    $_SESSION['userName'] = $userName;
                    $_SESSION['type'] = $type;
                    $_SESSION['Id'] = $id;
                    header('Location: serviceProvider.php?id=' . $id);
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
    <title>Add a Service Provider</title>
</head>
<body>
    <div id="container">
    <div class ="formBox">
    <?php if(isset($_SESSION['userName'])):?>
        <a href="admin.php">Back</a>
    <?php else:?>
        <a href="index.php">Back</a>
    <?php endif?>
        <div>
            <form method="post">
                <h2>New Service Provider</h2>
                <p>
                <label for="name">Service Provider Name</label>
                <input name="name" id="name">
                </p>
                <p>
                <label for="description">Description</label>
                <textarea name="description" id="description"></textarea>
                </p>
                <p>
                <label for="location">Location</label>
                <input name="location" id="location">
                </p>
                <p>
                <label for="phoneNumber">Phone Number</label>
                <input name="phoneNumber" id="phoneNumber">
                </p>
                <p>
                <label for="email">Email Address</label>
                <input name="email" id="email">
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
                <p class = "errorMessage"><?= $passwordError?></p>
                <p>
                <input type="submit" value="Create">
                </p>
            </form>
        </div>
        </div>
    </div>
</body>
</html>