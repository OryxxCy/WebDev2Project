<?php

require('connect.php');

session_start();

$ratingError ="";

if($id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT))
{
    if(isset($_SESSION['userName']))
    {
        $accountQuery = "SELECT * FROM accounts WHERE user_Name = :userName";
        $accountStatement = $db->prepare($accountQuery);
        $accountStatement->bindValue(':userName', $_SESSION['userName']);
        $accountStatement->execute();
        $account = $accountStatement->fetch();

        
        $customerRateQuery = "SELECT * FROM ratings WHERE customer_Id = :customer_Id AND service_Provider_Id = :service_Provider_Id";
        $customerRateStatement = $db->prepare($customerRateQuery);
        $customerRateStatement->bindValue(":service_Provider_Id", $id);
        $customerRateStatement->bindValue(":customer_Id", $account['customer_Id']);
        $customerRateStatement->execute();
    }

    $selectedServiceQuery = "SELECT * FROM service_providers_services WHERE service_Provider_Id = :currentServiceProviderId";
    $selectedServiceStatement = $db->prepare($selectedServiceQuery);
    $selectedServiceStatement->bindValue(':currentServiceProviderId', $id, PDO::PARAM_INT);
    $selectedServiceStatement->execute();
    $selectedServiceRow = $selectedServiceStatement->fetch();

    $selectedServiceNameQuery = "SELECT * FROM services WHERE id = :selectedServiceId";
    $selectedServiceNameStatement = $db->prepare($selectedServiceNameQuery);
    $selectedServiceNameStatement->bindValue(':selectedServiceId', $selectedServiceRow['service_Id'], PDO::PARAM_INT);
    $selectedServiceNameStatement->execute(); 
    $selectedServiceName = $selectedServiceNameStatement->fetch();

    $query =  " SELECT sp.*, FORMAT(avg_rating, 2) AS avg_rating
                FROM service_providers sp
                LEFT JOIN (
                    SELECT service_Provider_Id, AVG(rating) AS avg_rating
                    FROM ratings
                    GROUP BY service_Provider_Id
                ) r ON sp.id = r.service_Provider_Id
                WHERE sp.id = :id";
    $statement = $db->prepare($query);
    $statement->bindValue(':id', $id, PDO::PARAM_INT);
    $statement->execute();
    $serviceProvider = $statement->fetch();

    $commentQuery = "SELECT cc.*, c.name
                     FROM customercomments cc
                     JOIN customers c ON cc.customer_id = c.id
                     WHERE cc.service_Provider_Id = :id
                     ORDER BY timeStamp DESC";
    $commentStatement = $db->prepare($commentQuery);
    $commentStatement->bindValue(':id', $id, PDO::PARAM_INT);
    $commentStatement->execute();

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

if($_POST){
    if(isset($_POST['rate'])){
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
            $ratingQuery = "INSERT INTO ratings (service_Provider_Id, customer_Id, rating) VALUES (:service_Provider_Id, :customer_Id, :rating)";
            $ratingStatement = $db->prepare($ratingQuery);
            $ratingStatement->bindValue(":service_Provider_Id", $id);
            $ratingStatement->bindValue(":customer_Id", $account['customer_Id']);
            $ratingStatement->bindValue(":rating", $customerRate);
            $ratingStatement->execute();
        }
    
        $accountQuery = "SELECT * FROM accounts WHERE user_Name = :userName";
        $accountStatement = $db->prepare($accountQuery);
        $accountStatement->bindValue(':userName', $_SESSION['userName']);
        $accountStatement->execute();
        $account = $accountStatement->fetch();
    
        $customerRateQuery = "SELECT * FROM ratings WHERE customer_Id = :customer_Id AND service_Provider_Id = :service_Provider_Id";
        $customerRateStatement = $db->prepare($customerRateQuery);
        $customerRateStatement->bindValue(":service_Provider_Id", $id);
        $customerRateStatement->bindValue(":customer_Id", $account['customer_Id']);
        $customerRateStatement->execute();
    }
    
    if(isset($_POST['commentButton'])){
        $query = "  INSERT INTO customercomments (customer_Id, service_Provider_Id, comment) VALUES (:customer_Id, :service_Provider_Id, :comment)";
                    $statement = $db->prepare($query);
                    $statement->bindValue(":customer_Id", $_SESSION['Id']);
                    $statement->bindValue(":service_Provider_Id", $id);
                    $statement->bindValue(":comment", $_POST['comment']);
                    $statement->execute();
    }

    header("Location: serviceProvider.php?id=$id");
    exit();
}

//
$findBannerCustomer = function($customerId) use ($db) {
    $customerInfoQuery = "SELECT * FROM customers WHERE id = :customerId";
    $customerInfoStatement = $db->prepare($customerInfoQuery);
    $customerInfoStatement->bindValue(':customerId', $customerId, PDO::PARAM_INT);
    $customerInfoStatement->execute();
    $customerInfo = $customerInfoStatement->fetch();

    $customerName = $customerInfo['name'];

    return $customerName; 
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
                    <p>Service : <?=$selectedServiceName['name']?> Price : $<?=$selectedServiceRow['price']?></p>
                    <p>Rating: <?= $serviceProvider['avg_rating'] == null ? 'Not yet Rated' : $serviceProvider['avg_rating']?>⭐</p>

                </div>
            </div>
            <?php if((isset($_SESSION['userName']) && $customerRateStatement->rowCount() > 0)):?>
                <?php $customerRateRow = $customerRateStatement->fetch()?>
                <p>You rated it <?=$customerRateRow['rating']?>⭐</p>
            <?php else:?>
                <?php if(isset($_SESSION['userName']) && $_SESSION['type'] == 'customer'):?>
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
                        <button type="submit" name="rate">Submit Rating</button>
                    </form>
                    <p class = "errorMessage"><?= $ratingError?></p>
                <?php endif?>
            <?php endif?>
            <?php if(isset($_SESSION['userName'])):?>
                <?php if($_SESSION['type'] == 'admin' || ($_SESSION['type'] != 'customer' && $_SESSION['Id'] == $id)):?>
                    <a href="edit_service_providers.php?id=<?=$id?>">Edit or Delete</a>
                <?php endif?>
            <?php endif?>
    </div>
    <div class="serviceProviderPage">
        <form method="post">
        <?php while($comments = $commentStatement->fetch()): ?>
            <div class="comments">
            <p>
                <a href="comment.php?id=<?=$comments['id']?>&serviceProviderId=<?=$id?>">Commented by: <?=$findBannerCustomer($comments['customer_Id'])?>
                at <?=$comments['timeStamp']?></a>
            </p>
            <p>
                <?= $comments['comment']?>
            </p>
            </div>
        <?php endwhile?> 
        <?php if(isset($_SESSION['userName']) && $_SESSION['type'] == 'customer'):?>
            <div class="formBox">
                <p>
                    <label for="comment">Add a comment</label>
                    <textarea name="comment" id="comment"></textarea>
                </p>
                <p>
                    <button type="submit" name="commentButton">Post</button>
                </p>  
            </div>   
        <?php endif?>      
        </form>
    </div>
    </div>
</body>
</html>