<?php

require('connect.php');
session_start();

if (isset($_SESSION['userName'])) {
    if($_SESSION['type'] != 'admin'){
        header('Location: login.php');
        exit();
    }
}

$nameError = "";
$phoneNumberError = "";
$userNameError = "";
$passwordError = "";
$noError = true;

if ($_POST) {
    $userName = filter_input(INPUT_POST, 'userName', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $passwordInput = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $confirmPassword = filter_input(INPUT_POST, 'confirmPassword', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $phoneNumber = filter_input(INPUT_POST, 'phoneNumber', FILTER_SANITIZE_NUMBER_INT);

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
        $phoneNumberError = "Phone number length invalid. It must be 10 digits.";
        $noError = false;
    }

    if($noError){
            $formattedPhoneNumber = '(' . substr($phoneNumber, 0, 3) . ') ' . substr($phoneNumber, 3, 3) . '-' . substr($phoneNumber, 6);

            $query = "INSERT INTO customers (name, phone_Number) VALUES (:name, :phoneNumber)";
            $statement = $db->prepare($query);
            $statement->bindValue(":name", $name);
            $statement->bindValue(":phoneNumber", $formattedPhoneNumber);
            
        if($statement->execute()){
            $id = $db->lastInsertId();
            $password = password_hash($passwordInput, PASSWORD_DEFAULT);
            $type = "customer";

            $query = "INSERT INTO accounts (user_Name, password, type, customer_Id) VALUES (:userName, :password, :type, :id)";
            $statement = $db->prepare($query);

            $statement->bindValue(":userName", $userName);
            $statement->bindValue(":password", $password);
            $statement->bindValue(":type", $type);
            $statement->bindValue(":id", $id, PDO::PARAM_INT);

            if($statement->execute()){
                if($_SESSION['type'] == 'admin')
                {
                    header("Location: admin.php?table=customers&column=name");
                }else{
                    $_SESSION['userName'] = $userName;
                    $_SESSION['type'] = $type;
                    $_SESSION['Id'] = $id;
                    header('Location: customer.php?id=' . $id);
                }       
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
    <title>Add a Customer</title>
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
            <form method="post">
                <h2>New Customer</h2>
                <p>
                <label for="name">Customer Name</label>
                <input name="name" id="name">
                </p>
                <p class = "errorMessage"><?= $nameError?></p>
                <p>
                <label for="phoneNumber">Phone number</label>
                <input type="number" name="phoneNumber" id="phoneNumber">
                </p>
                <p class = "errorMessage"><?= $phoneNumberError?></p>
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