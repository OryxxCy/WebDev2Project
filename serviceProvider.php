<?php

require('connect.php');

session_start();

$ratingError ="";

if(isset($_GET['id']))
{
    $id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
    
    $query = "SELECT * FROM service_providers WHERE id = :id";
    $statement = $db->prepare($query);
    $statement->bindValue(':id', $id, PDO::PARAM_INT);
    $statement->execute();
    $serviceProvider = $statement->fetch();

    if($serviceProvider != null)
    {
        if($serviceProvider['imageId'] != 0 || $serviceProvider['imageId'] != null ){
            $imageQuery = "SELECT * FROM images WHERE id = :id";
            $imageStatement = $db->prepare($imageQuery);
            $imageStatement->bindValue(':id', $serviceProvider['imageId']);
            $imageStatement->execute();
            $imageRow = $imageStatement->fetch();
        }
    }else{
        header("Location: index.php");
    }
   
}else{
    header("Location: index.php");
}

if(isset($_POST['command'])){
    $customerRate;

    if (!isset($_POST['rating']) || empty($_POST['rating'])) {
        $ratingError = "Please select a rate.";
    }else{
        switch ($_POST['rating']) {
            case 5:
                $customerRate = 5;
                break;
            case 4:
                $customerRate = 4;
                break;
            case 3:
                $customerRate = 3;
                break;
            case 2:
                $customerRate = 2;
                break;
            case 1:
                $customerRate = 1;
                break;        
        }
      
        $customerQuery = "SELECT * FROM accounts WHERE user_Name = :userName";
        $customerStatement = $db->prepare($customerQuery);
        $customerStatement->bindValue(':userName', $_SESSION['userName']);
        $customerStatement->execute();
        $customer = $customerStatement->fetch();

        $ratingQuery = "INSERT INTO ratings (service_Id, customer_Id, rating) VALUES (:service_Id, :customer_Id, :rating)";
        $ratingStatement = $db->prepare($ratingQuery);
        $ratingStatement->bindValue(":service_Id", $id);
        $ratingStatement->bindValue(":customer_Id", $customer['customer_Id']);
        $ratingStatement->bindValue(":rating", $customerRate);
        $ratingStatement->execute();
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
    <title><?=$serviceProvider['name']?></title>
</head>
<body>
    <div id="container">
    <?php include('navigation.php')?>
    <div class="serviceProviderPage">
            <div>
                <?php if(isset($_SESSION['userName'])):?>
                    <h2>Welcome <?= $_SESSION['userName']?></h2>
                <?php endif ?>
                <div>
                    <h2><?= $serviceProvider['name']?></h2>
                    <?php if($serviceProvider['imageId'] != 0 || $serviceProvider['imageId'] != null ):?>
                        <img src="<?=$imageRow['banner']?>" alt="Banner photo of <?= $serviceProvider['name']?>">
                    <?php endif?>
                    <div>
                        <?= $serviceProvider['description']?>
                    </div>
                    <p>Location: <?= $serviceProvider['location']?></p>
                    <p>Phone Number: <?= $serviceProvider['phone_Number']?></p>
                    <p>Email address: <?= $serviceProvider['email_Address']?></p>
                </div>
            </div>
            <?php if($_SESSION['type'] == 'customer'):?>
                <form method = 'post'>
                    <label for="rating">Rate</label>
                    <input type="radio" id="5stars" name="rating" value="5">
                    <label for="5stars">5⭐</label>

                    <input type="radio" id="4stars" name="rating" value="4">
                    <label for="4stars">4⭐</label>

                    <input type="radio" id="3stars" name="rating" value="3">
                    <label for="3stars">3⭐</label>

                    <input type="radio" id="2stars" name="rating" value="2">
                    <label for="2stars">2⭐</label>

                    <input type="radio" id="1star" name="rating" value="1">
                    <label for="1star">1⭐</label>

                    <input type="submit" name='command' value="Submit Rating">
                </form>
                <p class = "errorMessage"><?= $ratingError?></p>
            <?php endif?>
            <?php if(isset($_SESSION['userName'])):?>
                <?php if($_SESSION['type'] == 'admin' || ($_SESSION['type'] != 'customer' && $_SESSION['Id'] == $id)):?>
                    <a href="edit_service_providers.php?id=<?=$id?>">Edit or Delete</a>
                <?php endif?>
            <?php endif?>
    </div>
    </div>
</body>
</html>