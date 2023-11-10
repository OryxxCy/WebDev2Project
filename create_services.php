<?php

require('connect.php');

session_start();

if (!isset($_SESSION['userName'])) {
    header('Location: login.php');
    exit();
}

    if ($_POST) {
        if (trim($_POST['name']) == null) {
            header("Location: error.php");
        }else{
             $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
             $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

             $query = "INSERT INTO services (name, description) VALUES (:name, :description)";
             $statement = $db->prepare($query);

             $statement->bindValue(":name", $name);
             $statement->bindValue(":description", $description);
             
            if($statement->execute()){
                 header("Location: admin.php?table=services&column=name");
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
    <title>Add a Service</title>
</head>
<body>
    <div id="container">
    <?php include('adminNavigation.php')?>
    <a href="admin.php">Back</a>
        <div>
            <form method="post">
                <h2>New Service</h2>
                <p>
                <label for="name">Service Name</label>
                <input name="name" id="name">
                </p>
                <p>
                <label for="description">Description</label>
                <textarea name="description" id="description"></textarea>
                </p>
                <p>
                <input type="submit" value="Create">
                </p>
            </form>
        </div>
    </div>
</body>
</html>