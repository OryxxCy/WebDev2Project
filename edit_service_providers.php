<?php

require('connect.php');
session_start();

if (!isset($_SESSION['userName'])) {
    header('Location: login.php');
    exit();
}

$userNameError = "";
$passwordError = "";
$noError = true;

if (isset($_GET['id'])) { 
    if($id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT)){
        $query = "SELECT * FROM service_providers WHERE id = :id";
        $statement = $db->prepare($query);
        $statement->bindValue(':id', $id, PDO::PARAM_INT);
        
        $statement->execute();
        $row = $statement->fetch();

        $accountsQuery = "SELECT * FROM accounts WHERE service_Provider_Id = :id";
        $accountsStatement = $db->prepare($accountsQuery);
        $accountsStatement->bindValue(':id', $id, PDO::PARAM_INT);
        
        $accountsStatement->execute();
        $accountsRow = $accountsStatement->fetch();
    }else{
        if($_SESSION['type'] == 'admin')
        {
            header("Location: admin.php?table=service_providers&column=name");
        }else{
            header('Location: index.php');
        }
    }
}

if ($_POST && isset($_GET['id'])) {
    $id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $location = filter_input(INPUT_POST, 'location', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $phoneNumber = filter_input(INPUT_POST, 'phoneNumber', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    if($_POST['command'] == 'Update'){
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
        }else if($unavailableAccount && $accountsRow['user_Name'] != $userName){
            $userNameError = "Username is taken please select a different one.";
            $noError = false;
        }
        
        if($noError){
            $query = "UPDATE service_providers SET name = :name, description = :description, location = :location, phone_Number = :phone_Number, email_Address = :email_Address WHERE id = :id";
    
            $statement = $db->prepare($query);
            $statement->bindValue(':id', $id, PDO::PARAM_INT);
            $statement->bindValue(":name", $name);
            $statement->bindValue(":description", $description);
            $statement->bindValue(":location", $location);
            $statement->bindValue(":phone_Number", $phoneNumber);
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
    }else{
        $query = "DELETE FROM service_providers WHERE id = :id LIMIT 1";

        $statement = $db->prepare($query);
        $statement->bindValue(':id', $id, PDO::PARAM_INT);
        $statement->execute();

        $query = "DELETE FROM accounts WHERE service_Provider_Id = :id LIMIT 1";

        $statement = $db->prepare($query);
        $statement->bindValue(':id', $id, PDO::PARAM_INT);
        $statement->execute();

        if($_SESSION['type'] == 'admin')
        {
            header("Location: admin.php?table=service_providers&column=name");
        }else{
            session_destroy();
            header('Location: index.php');
        }
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
                <form method="post">
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
                        <input name="phoneNumber" id="phoneNumber" value="<?= $row['phone_Number']?>">
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
                        </p>
                        <p>
                        <label for="confirmPassword">Confirm Password</label>
                        <input name="confirmPassword" id="confirmPassword" type="password">
                        </p>
                        <p class = "errorMessage"><?= $passwordError?></p>
                        <input type="hidden" name="id" value="<?= $row['id'] ?>">
                        <input type="submit" name="command" value="Update">
                        <input type="submit" name="command" value="Delete" onclick="return confirm('Are you sure you wish to delete this? This will also delete the account associated to it.')">
                    </p>
                </form>
                </div>  
            <?php else:?>   
                <h2>Only admin and <?= $row['name']?> account owner can access this page.</h2> 
            <?php endif?>   
        </div>  
    </div>  
</body>
</html>