<?php

require('connect.php');
require ('ImageResize.php');
session_start();

if (isset($_SESSION['userName'])) {
    if($_SESSION['type'] != 'admin'){
        header('Location: login.php');
        exit();
    } 
}

$imageError = "";
$userNameError = "";
$passwordError = "";
$noError;

if ($_POST) {
    $userName = filter_input(INPUT_POST, 'userName', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    $query = "SELECT * FROM accounts WHERE user_Name = :userName";
    $statement = $db->prepare($query);
    $statement->bindValue(':userName', $userName);
    $statement->execute();
    $unavailableAccount = $statement->fetch();

    if(trim($_POST['password']) == null || trim($_POST['confirmPassword']) == null){
        $passwordError = "Do not leave the password empty.";
        $noError = false;
    }else if($_POST['password'] !=  $_POST['confirmPassword']) {
        $passwordError = "The passwords do not match.";
        $noError = false;
    }

    if(trim($_POST['userName']) == ''){
        $userNameError = "Please dont leave the username empty.";
        $noError = false;
    }else if($unavailableAccount){
        $userNameError = "Username is taken please select a different one.";
        $noError = false;
    }

    if(isset($_FILES['bannerImage']) && $_FILES['bannerImage']['error'] === 0){
        $image_filename        = $_FILES['bannerImage']['name'];
        $temporary_image_path  = $_FILES['bannerImage']['tmp_name'];
        $new_image_path        = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Images' . DIRECTORY_SEPARATOR. 'Banner' . DIRECTORY_SEPARATOR.  $image_filename;

        if(!(file_is_a_valid_type($temporary_image_path, $new_image_path))){
           $imageError = "Please upload images only.";
           $noError = false;
        }

        $imageUploaded = true;
    }else{
        $imageUploaded = false;
    }
    
    if($noError){
            $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $location = filter_input(INPUT_POST, 'location', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $phoneNumber = filter_input(INPUT_POST, 'phoneNumber', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

            $query = "INSERT INTO service_providers (name, description, location, phone_Number, email_Address) VALUES (:name, :description, :location, :phone_Number, :email_Address)";
            $statement = $db->prepare($query);

            $statement->bindValue(":name", $name);
            $statement->bindValue(":description", $description);
            $statement->bindValue(":location", $location);
            $statement->bindValue(":phone_Number", $phoneNumber);
            $statement->bindValue(":email_Address", $email);

        if($statement->execute()){
            $id = $db->lastInsertId();
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $type = "service provider";

            $query = "INSERT INTO accounts (user_Name, password, type, service_Provider_Id) VALUES (:userName, :password, :type, :id)";
            $statement = $db->prepare($query);

            $statement->bindValue(":userName", $userName);
            $statement->bindValue(":password", $password);
            $statement->bindValue(":type", $type);
            $statement->bindValue(":id", $id);

            if($statement->execute()){
                if($imageUploaded){
                    $query = "INSERT INTO images (banner) VALUES (:banner)";
                    $statement = $db->prepare($query);
    
                    $bannerPicturePath ='Images' . DIRECTORY_SEPARATOR. 'Banner' . DIRECTORY_SEPARATOR. $image_filename; 
                    $statement->bindValue(":banner", $bannerPicturePath);
    
                    if($statement->execute())
                    {
                        $bannerId = $db->lastInsertId();
                        $query = "UPDATE service_providers SET imageId = :bannerId WHERE Id = :id";
                        $statement = $db->prepare($query);
    
                        $statement->bindValue(":bannerId", $bannerId); 
                        $statement->bindValue(":id", $id);             
                        
                        if($statement->execute()){
                            $image_filename =  $bannerId . $_FILES['bannerImage']['name'];
                            $new_image_path = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Images' . DIRECTORY_SEPARATOR. 'Banner' . DIRECTORY_SEPARATOR.  $image_filename;
        
                            $image = new \Gumlet\ImageResize($temporary_image_path);
                            $image->resize(820, 380);
                            $image->save($new_image_path);

                            $bannerPicturePath ='Images' . DIRECTORY_SEPARATOR. 'Banner' . DIRECTORY_SEPARATOR. $image_filename; 
                            $query = "UPDATE images SET banner = :banner WHERE id = :bannerId";
                            $statement = $db->prepare($query);
                
                            $statement->bindValue(":banner", $bannerPicturePath);
                            $statement->bindValue(":bannerId", $bannerId);
    
                            $statement->execute();

                        }
                    }      
                }
                
                if($_SESSION['type'] == 'admin')
                {
                    header("Location: admin.php?table=service_providers&column=name");
                }else{
                    $_SESSION['userName'] = $userName;
                    $_SESSION['type'] = $type;
                    $_SESSION['Id'] = $id;
                    header('Location: serviceProvider.php?id=' . $id);
                }       
            }
        }
    }
}

function file_is_a_valid_type($temporary_path, $new_path) {
    $valid_Extension = null;

    $allowed_mime_types      = ['image/jpeg', 'image/png'];
    $allowed_file_extensions = ['jpg', 'jpeg', 'png'];

    $actual_file_extension   = pathinfo($new_path, PATHINFO_EXTENSION); 
    $actual_mime_type        = mime_content_type($temporary_path);

    $file_extension_is_valid = in_array($actual_file_extension, $allowed_file_extensions);
    $mime_type_is_valid      = in_array($actual_mime_type, $allowed_mime_types);

    return $file_extension_is_valid && $mime_type_is_valid;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Add a Service Provider</title>
</head>
<body>
    <div id="container">
    <div class ="formBox">
    <?php if(isset($_SESSION['userName'])):?>
        <a href="admin.php">Back</a>
    <?php else:?>
        <a href="index.php">Back</a>
    <?php endif?>
        <div>
            <form method="post" enctype="multipart/form-data">
                <h2>New Service Provider</h2>
                <p>
                <input type='file' name='bannerImage'>
                </p>
                <p class = "errorMessage"><?= $imageError?></p>
                <p>
                <label for="name">Service Provider Name</label>
                <input name="name" id="name">
                </p>
                <p>
                <label for="description">Description</label>
                <textarea name="description" id="description"></textarea>
                </p>
                <p>
                <label for="location">Location</label>
                <input name="location" id="location">
                </p>
                <p>
                <label for="phoneNumber">Phone Number</label>
                <input name="phoneNumber" id="phoneNumber">
                </p>
                <p>
                <label for="email">Email Address</label>
                <input name="email" id="email">
                </p>
                <p>
                <label for="userName">User Name</label>
                <input name="userName" id="userName">
                </p>
                <p class = "errorMessage"><?= $userNameError?></p>
                <p>
                <label for="password">Password</label>
                <input name="password" id="password" type="password">
                </p>
                <p>
                <label for="confirmPassword">Confirm Password</label>
                <input name="confirmPassword" id="confirmPassword" type="password">
                </p>
                <p class = "errorMessage"><?= $passwordError?></p>
                <p>
                <input type="submit" value="Create">
                </p>
            </form>
        </div>
        </div>
    </div>
</body>
</html>