<?php

require('connect.php');
session_start();

if (!isset($_SESSION['userName'])) {
    header('Location: login.php');
    exit();
}

$nameError = "";
$phoneNumberError = "";
$userNameError = "";
$passwordError = "";
$noError = true;

if($id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT)){
    $query = "SELECT * FROM customers WHERE id = :id";
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

    $accountsQuery = "SELECT * FROM accounts WHERE customer_Id = :id";
    $accountsStatement = $db->prepare($accountsQuery);
    $accountsStatement->bindValue(':id', $id, PDO::PARAM_INT);
    
    $accountsStatement->execute();
    $accountsRow = $accountsStatement->fetch();
}else{
    if($_SESSION['type'] == 'admin')
    {
        header("Location: admin.php?table=customers&column=name");
    }else{
        header('Location: index.php');
    }
}

if ($_POST) {
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $phoneNumber = filter_input(INPUT_POST, 'phoneNumber', FILTER_SANITIZE_NUMBER_INT);
    $userName = filter_input(INPUT_POST, 'userName', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $passwordInput = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $confirmPassword = filter_input(INPUT_POST, 'confirmPassword', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    if($_POST['command'] == 'Update'){
        $query = "SELECT * FROM accounts WHERE user_Name = :userName";
        $statement = $db->prepare($query);
        $statement->bindValue(':userName', $userName);
        $statement->execute();
        $unavailableAccount = $statement->fetch();

        if($passwordInput !=  $confirmPassword) {
            $passwordError = "The passwords do not match.";
            $noError = false;
        }else if (strlen($passwordInput) < 5 || strlen($passwordInput) > 30){
            $passwordError = "The password length must be 5 to 30 characters.";
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
    
        if(!(strlen(trim($phoneNumber)) == 10)){
            $phoneNumberError = "Phone number invalid. It must be 10 digits.";
            $noError = false;
        }
        
        if($noError){
            $formattedPhoneNumber = '(' . substr($phoneNumber, 0, 3) . ') ' . substr($phoneNumber, 3, 3) . '-' . substr($phoneNumber, 6);

            $query = "UPDATE customers SET name = :name, phone_Number = :phoneNumber WHERE id = :id";
            $statement = $db->prepare($query);
            $statement->bindValue(':id', $id, PDO::PARAM_INT);
            $statement->bindValue(":name", $name);
            $statement->bindValue(":phoneNumber", $formattedPhoneNumber);
           
            if($statement->execute()){
                $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
   
                $query = "UPDATE accounts SET user_Name = :userName, password = :password WHERE customer_Id = :id";
                $statement = $db->prepare($query);
    
                $statement->bindValue(":userName", $userName);
                $statement->bindValue(":password", $password);
                $statement->bindValue(":id", $id, PDO::PARAM_INT);
                
                if($statement->execute()){
                    $_SESSION['userName'] = $userName;
                }
            }

            if($_SESSION['type'] == 'admin')
            {
                header("Location: admin.php?table=service_providers&column=name");
            }else{
                header("Location: customer.php?id=" . $id);
            }
        }
    }else{
        $query = "DELETE FROM customers WHERE id = :id LIMIT 1";

        $statement = $db->prepare($query);
        $statement->bindValue(':id', $id, PDO::PARAM_INT);
        $statement->execute();

        $query = "DELETE FROM accounts WHERE customer_Id = :id LIMIT 1";

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
    <title>Edit this customer</title>
</head>
<body>
    <div id="container">
    <div class ="formBox">
        <?php if($_SESSION['type'] == 'admin' || $_SESSION['Id'] == $id):?>
            <?php if($_SESSION['type'] == 'admin'):?>
                <ul id="menu">
                    <li><a href="admin.php?table=customers&column=name">Back</a></li>
                    <li><a href="create_customers.php">Create New</a></li>
                </ul>
            <?php else:?>
                <a href="customer.php?id=<?=$id?>">Back</a>
            <?php endif?>    
            <div>
            <form method="post">
                <p>
                    <p>
                    <label for="name">Customer Name</label>
                    <input name="name" id="name" value="<?= $row['name']?>">
                    </p>
                    <p class = "errorMessage"><?= $nameError?></p>
                    <p>
                    <label for="phoneNumber">Phone Number</label>
                    <input type="number" name="phoneNumber" id="phoneNumber" value="<?= $currentPhoneNumber?>">
                    </p>
                    <p class = "errorMessage"><?= $phoneNumberError?></p>
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