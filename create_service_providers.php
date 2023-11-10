<?php

require('connect.php');

session_start();

if (!isset($_SESSION['userName'])) {
    header('Location: login.php');
    exit();
}

    if ($_POST) {
        if (trim($_POST['name']) == null || trim($_POST['description']) == null) {
            header("Location: error.php");
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
                 header("Location: admin.php?table=service_providers&column=name");
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
    <?php include('adminNavigation.php')?>
    <a href="admin.php">Back</a>
        <div>
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
                <input type="submit" value="Create">
                </p>
                </fieldset>
            </form>
        </div>
    </div>
</body>
</html>