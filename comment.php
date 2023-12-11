<?php

require('connect.php');

session_start();

    if(($id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT)) &&
       ($serviceProviderId = filter_input(INPUT_GET, 'serviceProviderId', FILTER_VALIDATE_INT)))
    {
        $query = $query = " SELECT 
                            cc.*,
                            c.name AS customerName,
                            s.name AS serviceProviderName
                            FROM customercomments cc
                            JOIN customers c ON cc.customer_id = c.id
                            JOIN service_providers s ON cc.service_provider_id = s.id
                            WHERE cc.id = :id";
        $statement = $db->prepare($query);
        $statement->bindValue(':id', $id, PDO::PARAM_INT);
        $statement->execute();
        $comment = $statement->fetch();
        if($comment == null)
        {
            header("Location: serviceProvider.php?id=$serviceProviderId");
        }
    }else{
        header("Location: index.php");
    }

    if($_POST)
    {
        if(isset($_POST['delete'])){
            $query = "DELETE FROM customercomments WHERE id = :id LIMIT 1";
            $statement = $db->prepare($query);
            $statement->bindValue(':id', $id, PDO::PARAM_INT);
            $statement->execute();
    
            header("Location: serviceProvider.php?id=$serviceProviderId");        
        }else if(isset($_POST['disembowel'])){
            $disemvoweledComment = disemvowel($comment['comment']);

            $query = "UPDATE customercomments SET comment = :comment WHERE id = :id";
            $statement = $db->prepare($query);
            $statement->bindValue(':id', $id, PDO::PARAM_INT);
            $statement->bindValue(":comment", $disemvoweledComment);
            $statement->execute();

            header("Location: comment.php?id=$id&serviceProviderId=$serviceProviderId");
        }
 
    }

    /*
    *Replaces the vowels in the comment with *.
    *
    *Param comment : the comment that need to be disembowled
    *Return disemvoweledComment : the disemboweled comment
    */
    function disemvowel($comment) {
        $disemvoweledComment = preg_replace('/[aeiouAEIOU]/', '*', $comment);
        
        return $disemvoweledComment;
    }

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title></title>
</head>
<body>
    <div id="container">
    <?php include('navigation.php')?>
    <div class="serviceProviderPage">
        <a href="serviceProvider.php?id=<?=$serviceProviderId?>">Back</a>
        <form method='post'>
            <p>Commented By : <?= $comment['customerName']?></p>
            <p>Date and time : <?= $comment['timeStamp']?></p>
            <p>Service Provider : <?= $comment['serviceProviderName']?></p>
            <div class='comments'>
                <p><?=$comment['comment']?></p>
            </div>
            <?php if(isset($_SESSION['userName']) && $_SESSION['type'] == 'admin'):?>
                <button type="submit" name="delete" onclick="return confirm('Are you sure you wish to delete this?')">Delete</button>
                <button type="submit" name="disembowel">Disembowel</button>
            <?php endif?>
        </form>   
    </div>   
    </div>   
</body>
</html>