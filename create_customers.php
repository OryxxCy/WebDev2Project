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
             $phoneNumber = filter_input(INPUT_POST, 'phoneNumber', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

             $query = "INSERT INTO customers (name, phone_Number) VALUES (:name, :phoneNumber)";
             $statement = $db->prepare($query);

             $statement->bindValue(":name", $name);
             $statement->bindValue(":phoneNumber", $phoneNumber);
             
            if($statement->execute()){
                 header("Location: admin.php?table=customers&column=name");
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
    <?php include('adminNavigation.php')?>
    <a href="admin.php">Back</a>
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
                <input type="submit" value="Create">
                </p>
            </form>
        </div>
    </div>
</body>
</html>