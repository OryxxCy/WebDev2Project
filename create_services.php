<?php

require('connect.php');
session_start();

$serviceError = "";
$serviceDescriptionError = "";
$noError = true;

$id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

if ($_POST) {
    if(trim($_POST['name']) == null || trim($_POST['name']) == null){
        $serviceError = "Do not leave the service name empty.";
        $noError = false;
    }

    if(trim($_POST['description']) == null || trim($_POST['description']) == null){
        $serviceDescriptionError = "Do not leave the service description empty.";
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
                <a href="admin.php">Back</a>
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
                    <p class = "errorMessage"><?=$serviceError?></p>
                    <p>
                    <label for="description">Description</label>
                    <textarea name="description" id="description"></textarea>
                    </p>
                    <p class = "errorMessage"><?=$serviceDescriptionError?></p>
                    <p>
                    <input type="submit" value="Create">
                    </p>
                </form>
            </div>
    </div>   
    </div>
</body>
</html>