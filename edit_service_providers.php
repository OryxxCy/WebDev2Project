<?php

require('connect.php');
require ('ImageResize.php');
session_start();

if (!isset($_SESSION['userName'])) {
    header('Location: login.php');
    exit();
}

$priceError ="";
$nameError = "";
$phoneNumberError = "";
$emailError = "";
$descriptionError = "";
$locationError = "";
$imageError = "";
$userNameError = "";
$passwordError = "";
$noError = true;

$servicesQuery = "SELECT * FROM services";
$serviceStatement = $db->prepare($servicesQuery);
$serviceStatement->execute();


if($id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT)){
    $query = "SELECT * FROM service_providers WHERE id = :id";
    $statement = $db->prepare($query);
    $statement->bindValue(':id', $id, PDO::PARAM_INT);
    $statement->execute();
    $row = $statement->fetch();

    if($row == null){
        if($_SESSION['type'] == 'admin')
        {
            header("Location: admin.php?table=service_providers&column=name");
        }else{
            header('Location: index.php');
        }
    }

    $currentPhoneNumber = str_replace(['(', ')', ' ', '-'], '', $row['phone_Number']);

    $accountsQuery = "SELECT * FROM accounts WHERE service_Provider_Id = :id";
    $accountsStatement = $db->prepare($accountsQuery);
    $accountsStatement->bindValue(':id', $id, PDO::PARAM_INT);
    
    $accountsStatement->execute();
    $accountsRow = $accountsStatement->fetch();

    $currentServiceQuery = "SELECT * FROM service_providers_services WHERE service_Provider_Id = :id";

    $currentServiceStatement = $db->prepare($currentServiceQuery);
    $currentServiceStatement->bindValue(':id', $id, PDO::PARAM_INT);
    $currentServiceStatement->execute();

    if($row['imageId'] != 0 || $row['imageId'] != null)
    {
        $imageQuery = "SELECT * FROM images WHERE id = :id";
        $imageStatement = $db->prepare($imageQuery);
        $imageStatement->bindValue(':id', $row['imageId']);
        $imageStatement->execute();
        $imageRow = $imageStatement->fetch();
    }
}else{
    if($_SESSION['type'] == 'admin')
    {
        header("Location: admin.php?table=service_providers&column=name");
    }else{
        header('Location: index.php');
    }
}

if ($_POST) {
    $userName = filter_input(INPUT_POST, 'userName', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $passwordInput = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $confirmPassword = filter_input(INPUT_POST, 'confirmPassword', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $location = filter_input(INPUT_POST, 'location', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $phoneNumber = filter_input(INPUT_POST, 'phoneNumber', FILTER_SANITIZE_NUMBER_INT);
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $price = filter_input(INPUT_POST, 'price', FILTER_SANITIZE_NUMBER_INT);

    if($_POST['command'] == 'Update'){
        $userName = filter_input(INPUT_POST, 'userName', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        $query = "SELECT * FROM accounts WHERE user_Name = :userName";
        $statement = $db->prepare($query);
        $statement->bindValue(':userName', $userName);
        $statement->execute();
        $unavailableAccount = $statement->fetch();

        if($passwordInput !=  $confirmPassword) {
            $passwordError = "The passwords do not match.";
            $noError = false;
        }else if (strlen($passwordInput) < 5 || strlen($passwordInput) > 30){
            $passwordError = "The passwords length is invalid please use a password with a length of 5 to 30 characters.";
            $noError = false;
        }
    
        if(trim($userName) == ''){
            $userNameError = "Please dont leave the username empty.";
            $noError = false;
        }else if($unavailableAccount){
            $userNameError = "Username is taken please select a different one.";
            $noError = false;
        }else if (strlen($userName) > 50){
            $passwordError = "Use a user name that is less than 50 Characters.";
            $noError = false;
        }
    
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
    
        if(trim($location) == ''){
            $locationError = "Please dont leave the location empty.";
            $noError = false;
        }else if (strlen($location) > 50){
            $locationError = "Location must be less than 50 Characters.";
            $noError = false;
        }
    
        if(!(strlen(trim($phoneNumber)) == 10)){
            $phoneNumberError = "Phone number length invalid. It must be 10 digits.";
            $noError = false;
        }
    
        if(!$email){
            $emailError = "Invalid email";
            $noError = false;
        }
        
        if($noError){
            $formattedPhoneNumber = '(' . substr($phoneNumber, 0, 3) . ') ' . substr($phoneNumber, 3, 3) . '-' . substr($phoneNumber, 6);

            $query = "UPDATE service_providers SET name = :name, description = :description, location = :location, phone_Number = :phone_Number, email_Address = :email_Address WHERE id = :id";
            $statement = $db->prepare($query);
            $statement->bindValue(':id', $id, PDO::PARAM_INT);
            $statement->bindValue(":name", $name);
            $statement->bindValue(":description", $description);
            $statement->bindValue(":location", $location);
            $statement->bindValue(":phone_Number", $formattedPhoneNumber);
            $statement->bindValue(":email_Address", $email);
           
            if($statement->execute()){
                $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
   
                $query = "UPDATE accounts SET user_Name = :userName, password = :password WHERE service_Provider_Id = :id";
                $statement = $db->prepare($query);
    
                $statement->bindValue(":userName", $userName);
                $statement->bindValue(":password", $password);
                $statement->bindValue(":id", $id);
                
                if($statement->execute()){
                    $_SESSION['userName'] = $userName;
                }
            }
    
            if($_SESSION['type'] == 'admin')
            {
                header("Location: admin.php?table=service_providers&column=name");
            }else{
                header("Location: serviceProvider.php?id=" . $id);
            }
        }
    }else if($_POST['command'] == 'Delete') {
        $query = "DELETE FROM service_providers WHERE id = :id LIMIT 1";

        $statement = $db->prepare($query);
        $statement->bindValue(':id', $id, PDO::PARAM_INT);
        $statement->execute();

        $query = "DELETE FROM accounts WHERE service_Provider_Id = :id LIMIT 1";

        $statement = $db->prepare($query);
        $statement->bindValue(':id', $id, PDO::PARAM_INT);
        $statement->execute();

        $query = "DELETE FROM service_providers_services WHERE service_Provider_Id = :id LIMIT 1";

        $statement = $db->prepare($query);
        $statement->bindValue(':id', $id, PDO::PARAM_INT);
        $statement->execute();

        if($row['imageId'] != 0 || $row['imageId'] != null){
            unlink($imageRow['banner']);

            $query = "DELETE FROM images WHERE id = :id LIMIT 1";

            $statement = $db->prepare($query);
            $statement->bindValue(':id', $row['imageId']);
            $statement->execute();
        }

        if($_SESSION['type'] == 'admin')
        {
            header("Location: admin.php?table=service_providers&column=name");
        }else{
            session_destroy();
            header('Location: index.php');
        }
    }else if($_POST['command'] == 'Upload Banner'){
        if(isset($_FILES['bannerImage']) && $_FILES['bannerImage']['error'] === 0){
            $image_filename        = $_FILES['bannerImage']['name'];
            $temporary_image_path  = $_FILES['bannerImage']['tmp_name'];
            $new_image_path        = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Images' . DIRECTORY_SEPARATOR. 'Banner' . DIRECTORY_SEPARATOR.  $image_filename;

            if(file_is_a_valid_type($temporary_image_path, $new_image_path)){                
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

                $imageError = "Uploaded";
                header("Location: serviceProvider.php?id=" . $id);

            }else{
                $imageError = "Please upload images only.";
            }
        }else{
            $imageError = "No image was uploaded.";
        }
    }else if($_POST['command'] == 'Remove Banner'){
        $query = "UPDATE service_providers SET imageId = :bannerPictureId WHERE Id = :id";
        $statement = $db->prepare($query);

        $bannerPictureId = 0;
        $statement->bindValue(":bannerPictureId", $bannerPictureId); 
        $statement->bindValue(":id", $id, PDO::PARAM_INT);             
                    
        if($statement->execute())
        {
            unlink($imageRow['banner']);

            $query = "DELETE FROM images WHERE id = :id LIMIT 1";

            $statement = $db->prepare($query);
            $statement->bindValue(':id', $imageRow['id']);
            $statement->execute();
            header("Location: serviceProvider.php?id=" . $id);
        }
    }else if($_POST['command'] == 'Update Service'){

        if(trim($price) == ''){
            $priceError = "Price must not be empty.";
            $noError = false;
        }
    
        if($noError){
            $insertServiceQuery = "SELECT * FROM services WHERE name LIKE :name";
            $insertServiceStatement = $db->prepare($insertServiceQuery);
            $insertServiceStatement->bindValue(":name", $_POST['service']);
    
            if($insertServiceStatement->execute()){
                $insertServiceRow = $insertServiceStatement->fetch();
                $query = "UPDATE service_providers_services SET service_Id = :service_Id, price = :price WHERE service_Provider_Id = :id";
                $statement = $db->prepare($query);
                $statement->bindValue(":service_Id", $insertServiceRow['id']);
                $statement->bindValue(":price", $_POST['price']);
                $statement->bindValue(':id', $id, PDO::PARAM_INT);
    
                $statement->execute();
            }
            header("Location: serviceProvider.php?id=" . $id);
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
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Edit this service provider</title>
</head>
<body>
    <div id="container">
        <div class ="formBox">
        <?php if($_SESSION['type'] == 'admin' || ($_SESSION['type'] != 'customer' && $_SESSION['Id'] == $id)):?>
            <?php if($_SESSION['type'] == 'admin'):?>
                <ul id="menu">
                    <li><a href="admin.php">Back</a></li>
                    <li><a href="create_services.php">Create New</a></li>
                </ul>
            <?php else:?>
                <a href="serviceProvider.php?id=<?=$id?>">Back</a>
            <?php endif?> 
            <div>
                <form method="post" enctype="multipart/form-data">
                    <p>
                        <?php if($row['imageId'] == 0 || $row['imageId'] == null):?>
                            <input type='file' name='bannerImage'>
                            <input type='submit' name="command" value="Upload Banner">
                        <?php else:?>
                            <input type='submit' name="command" value="Remove Banner" onclick="return confirm('Are you sure you wish to delete this banner photo?')">
                        <?php endif?>  
                    </p>
                    <p class = "errorMessage"><?= $imageError?></p>
                    <p>
                        <label for="service">Service</label>
                            <select name="service" id="service">
                                <?php while($serviceRow = $serviceStatement->fetch()):?>
                                <option value="<?= $serviceRow['name']?>"><?= $serviceRow['name']?></option>
                                <?php endwhile?>
                            </select>
                            <a href="create_services.php?id=<?=$id?>">Add New Service</a>
                            <label for="price">Service Price</label> 
                            <input name="price" type ="number" id="price">
                            <p class = "errorMessage"><?= $priceError?></p>
                            <input type='submit' name="command" value="Update Service">
                    <p>
                        <p>
                        <label for="name">Service Provider Name</label>
                        <input name="name" id="name" value="<?= $row['name']?>">
                        </p>
                        <p>
                        <label for="description">Description</label>
                        <textarea name="description" id="description"><?= $row['description']?></textarea>
                        </p>
                        <p>
                        <label for="location">Location</label>
                        <input name="location" id="location" value="<?= $row['location']?>">
                        </p>
                        <p>
                        <label for="phoneNumber">Phone Number</label>
                        <input name="phoneNumber" id="phoneNumber" value="<?= $currentPhoneNumber?>">
                        </p>
                        <p>
                        <label for="email">Email Address</label>
                        <input name="email" id="email" value="<?= $row['email_Address']?>">
                        </p>
                        <p>
                        <label for="userName">User Name</label>
                        <input name="userName" id="userName" value="<?= $accountsRow['user_Name']?>">
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
                        <input type="hidden" name="id" value="<?= $row['id'] ?>">
                        <input type="submit" name="command" value="Update">
                        <input type="submit" name="command" value="Delete" onclick="return confirm('Are you sure you wish to delete this? This will also delete the account associated to it.')">
                </form>
                </div>  
            <?php else:?>   
                <h2>Only admin and <?= $row['name']?> account owner can access this page.</h2> 
            <?php endif?>   
        </div>  
    </div>  
</body>
</html>