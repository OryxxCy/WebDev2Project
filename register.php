<?php
require('connect.php');
session_start();

if($_POST['type'] == "serviceProvider"){
     header("Location: create_service_providers.php");
}else{
    header("Location: create_customers.php");
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Register</title>
</head>
<body>
    <div id="container">
        <h3>What account would you like to create</h3>
            <form method="post">
            <input type="submit" name="type" value="serviceProvider">
            <input type="submit" name="type" value="customer">
            </form>
    </div>   
</body>
</html>