<?php

require('connect.php');

if($id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT))
{
    $query = "SELECT * FROM service_providers WHERE id = :id";
    $statement = $db->prepare($query);
    $statement->bindValue(':id', $id, PDO::PARAM_INT);
    $statement->execute();
    $serviceProvider = $statement->fetch();
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
    <title><?=$serviceProvider['name']?></title>
</head>
<body>
    <div id="container">
    <?php include('navigation.php')?>
        <div>
            <div>
                <h2><?= $serviceProvider['name']?></h2>
                <div>
                    <?= $serviceProvider['description']?>
                </div>
                <p>Location: <?= $serviceProvider['location']?></p>
                <p>Phone Number: <?= $serviceProvider['phone_Number']?></p>
                <p>Email address: <?= $serviceProvider['email_Address']?></p>
            </div>
        </div>
    </div>
</body>
</html>