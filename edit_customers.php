<?php

require('connect.php');

session_start();

if (!isset($_SESSION['userName'])) {
    header('Location: login.php');
    exit();
}

if ($_POST && isset($_GET['id'])) {
    $id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $phoneNumber = filter_input(INPUT_POST, 'phoneNumber', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    if($_POST['command'] == 'Update'){
        if(trim($_POST['name']) == null){
            header("Location: error.php");
        }else{
            $query = "UPDATE customers SET name = :name, phone_Number = :phoneNumber WHERE id = :id";
    
            $statement = $db->prepare($query);
            $statement->bindValue(':id', $id, PDO::PARAM_INT);
            $statement->bindValue(":name", $name);
            $statement->bindValue(":phoneNumber", $phoneNumber);
           
            $statement->execute();

            header("Location: admin.php?table=customers&column=name");
        }
    }else{
        $query = "DELETE FROM customers WHERE id = :id LIMIT 1";

        $statement = $db->prepare($query);
        $statement->bindValue(':id', $id, PDO::PARAM_INT);

        $statement->execute();

        header("Location: admin.php?table=customers&column=name");
    }
} else if (isset($_GET['id'])) { 
    if($id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT)){
        $query = "SELECT * FROM customers WHERE id = :id";
        $statement = $db->prepare($query);
        $statement->bindValue(':id', $id, PDO::PARAM_INT);
        
        $statement->execute();
        $row = $statement->fetch();
    }else{
        header("Location: admin.php?table=customers&column=name");
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
    <title>Edit this customer</title>
</head>
<body>
    <div id="container">
    <?php include('adminNavigation.php')?>
        <ul id="menu">
            <li><a href="admin.php">Back</a></li>
            <li><a href="create_customers.php">Create New</a></li>
        </ul>
        <div>
        <form method="post">
            <p>
                <p>
                <label for="name">Customer Name</label>
                <input name="name" id="name" value="<?= $row['name']?>">
                </p>
                <p>
                <label for="phoneNumber">Phone Number</label>
                <input name="phoneNumber" id="phoneNumber" value="<?= $row['phone_Number']?>">
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