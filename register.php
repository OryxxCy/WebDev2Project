<?php
require('connect.php');
session_start();

if (isset($_SESSION['userName'])) {
    header('Location: index.php');
    exit();
}
$errorMessage = "";

if ($_POST) {
    if (trim($_POST['name']) == null || trim($_POST['description']) == null) {
        $errorMessage = "Please dont leave any inout blanks";
    }else if($_POST['password'] != $_POST['confirmPassword']){
        $errorMessage = "Password inputs didn't match.";
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
            $type = filter_input(INPUT_POST, 'type', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

            $query = "INSERT INTO accounts (user_Name, password, type, service_Provider_Id) VALUES (:userName, :password, :type, :id)";
            $statement = $db->prepare($query);

            $statement->bindValue(":userName", $userName);
            $statement->bindValue(":password", $password);
            $statement->bindValue(":type", $type);
            $statement->bindValue(":id", $id);

           if($statement->execute()){
                $_SESSION['userName'] = $userName;
                $_SESSION['type'] = $type;
                $_SESSION['Id'] = $id;
                header('Location: serviceProvider.php?id=' . $id);
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
    <title>Register</title>
</head>
<body>
    <div id="container">
        <h3>Create Service Provider account</h3>
            <form method="post">
                <fieldset>
                <legend>New Service Provider</legend>
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
                <p>
                <label for="type">Type</label>
                    <select id="type" name="type">
                        <option value="customer">Customer</option>
                        <option value="service provider">Service Provider</option>
                    </select>
                </p>
                <p>
                <input type="submit" value="Create">
                </p>
                <h3><?= $errorMessage?></h3>
                </fieldset>
            </form>
    </div>   
</body>
</html>