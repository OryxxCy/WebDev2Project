<?php

require('connect.php');

session_start();

if($id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT))
{
    $query = "SELECT * FROM customers WHERE id = :id";
    $statement = $db->prepare($query);
    $statement->bindValue(':id', $id, PDO::PARAM_INT);
    $statement->execute();
    $customer = $statement->fetch();
}else{
    header("Location: index.php");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title><?=$customer['name']?></title>
</head>
<body>
    <div id="container">
    <?php include('navigation.php')?>
    <div class="sectionBox">
        <div>
            <?php if(isset($_SESSION['userName'])):?>
                <h2>Welcome <?= $_SESSION['userName']?></h2>
            <?php endif ?>
            <div>
                <h2><?= $customer['name']?></h2>
                <p>Phone Number: <?= $customer['phone_Number']?></p>
            </div>
        </div>
        <?php if(isset($_SESSION['userName'])):?>
            <?php if($_SESSION['userName'] == 'admin' || $_SESSION['Id'] == $id):?>
                <a href="edit_customers.php?id=<?=$id?>">Edit or Delete</a>
            <?php endif?>
        <?php endif?>
    </div>
</div> 
</body>
</html>