<?php

require('connect.php');
session_start();

$nameError = "";
$descriptionError = "";
$noError = true;

$id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

if ($_POST) {
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    if(trim($name) == ''){
        $nameError = "Please dont leave the name empty.";
        $noError = false;
    }else if (strlen($name) > 50){
        $nameError = "Input a name that is less than 50 Characters.";
        $noError = false;
    }

    if(trim($description) == ''){
        $descriptionError = "Please dont leave the description empty.";
        $noError = false;
    }else if (strlen($description) > 500){
        $descriptionError = "Description must be less than 500 Characters.";
        $noError = false;
    }

    if($noError){
        $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        $query = "INSERT INTO services (name, description) VALUES (:name, :description)";
        $statement = $db->prepare($query);

        $statement->bindValue(":name", $name);
        $statement->bindValue(":description", $description);
        
        if($statement->execute()){
            if (isset($_SESSION['userName'])) {
                header('Location: edit_service_providers.php?id=' . $id);
            }else{
                header('Location: create_service_providers.php');
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
    <title>Add a Service</title>
</head>
<body>
    <div id="container">
    <div class ="formBox"> 
        <?php if(isset($_SESSION['userName'])):?>
            <?php if($_SESSION['type'] == 'admin'):?>
                <a href="edit_service_providers.php?id=<?=$id?>">Back</a>
            <?php elseif($_SESSION['type'] == 'service provider'):?>
                <a href="edit_service_providers.php?id=<?=$id?>">Back</a>
            <?php endif?>
        <?php else:?>
            <a href="index.php">Back</a>
        <?php endif?>
            <div>
                <form method="post">
                    <h2>New Service</h2>
                    <p>
                    <label for="name">Service Name</label>
                    <input name="name" id="name">
                    </p>
                    <p class = "errorMessage"><?=$nameError?></p>
                    <p>
                    <label for="description">Description</label>
                    <textarea name="description" id="description"></textarea>
                    </p>
                    <p class = "errorMessage"><?=$descriptionError?></p>
                    <p>
                    <input type="submit" value="Create">
                    </p>
                </form>
            </div>
    </div>   
    </div>
</body>
</html>