<?php

require('connect.php');

session_start();

if (!isset($_SESSION['userName'])) {
    header('Location: login.php');
    exit();
}

$nameSelected = "";
$dateSelected = "";
$rateSelected = "";
$phoneNumberSelected ="";

if($table = filter_input(INPUT_GET, 'table', FILTER_SANITIZE_FULL_SPECIAL_CHARS)){
    $sortOrder = "name ASC";

    if(isset($_POST['sort']))
    {
        switch ($_POST['sort']) {
            case "name":
                $sortOrder = "name ASC"; 
                $nameSelected = "selected";
                break;
            case "date":
                $sortOrder = "creation_Date ASC"; 
                $dateSelected = "selected";
                break;
            case "rating":
                $sortOrder =   "avg_rating DESC";
                $rateSelected = "selected";
                break; 
            case "phone":
                $sortOrder =   "phone_Number ASC";
                $phoneNumberSelected = "selected";
                break;
        }
    }

    switch($table){
        case 'service_providers':
            $query =   "SELECT sp.*, FORMAT(avg_rating, 2) AS avg_rating
                        FROM service_providers sp
                        LEFT JOIN (
                            SELECT service_Provider_Id, AVG(rating) AS avg_rating
                            FROM ratings
                            GROUP BY service_Provider_Id
                        ) r ON sp.id = r.service_Provider_Id
                        ORDER BY $sortOrder";
        break;
        case 'customers':
            $query =   "SELECT * FROM customers ORDER BY $sortOrder";
        break;
        case 'services':
            $query =   "SELECT * FROM services ORDER BY name ASC";
        break;
    }

    $statement = $db->prepare($query);
    $statement->execute(); 

    if (isset($_POST['servicesSearchButton'])) {
        $searchTerm = filter_input(INPUT_POST, 'servicesSearchTerm', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        switch($table){
            case 'service_providers':
                $query =   "SELECT sp.*, FORMAT(avg_rating, 2) AS avg_rating
                            FROM service_providers sp
                            LEFT JOIN (
                                SELECT service_Provider_Id, AVG(rating) AS avg_rating
                                FROM ratings
                                GROUP BY service_Provider_Id
                            ) r ON sp.id = r.service_Provider_Id
                            WHERE sp.name LIKE :searchTerm";
            break;
            case 'customers':
                $query =   "SELECT * FROM customers WHERE name LIKE :searchTerm";
            break;
            case 'services':
                $query =   "SELECT * FROM services WHERE name LIKE :searchTerm";
            break;
        }

        $statement = $db->prepare($query);
        $statement->bindValue(':searchTerm', '%' . $searchTerm . '%', PDO::PARAM_STR);
        $statement->execute();
    }
}else{
    $table = "service_providers";
    $sortOrder = "name ASC";

    if(isset($_POST['sortButton']))
    {
        switch ($_POST['sort']) {
            case "name":
                $sortOrder = "name ASC"; 
                $nameSelected = "selected";
                break;
            case "date":
                $sortOrder = "creation_Date ASC"; 
                $dateSelected = "selected";
                break;
            case "rating":
                $sortOrder =   "avg_rating DESC";
                $rateSelected = "selected";
                break; 
        }
    }

    $query =   "SELECT sp.*, FORMAT(avg_rating, 2) AS avg_rating
                FROM service_providers sp
                LEFT JOIN (
                    SELECT service_Provider_Id, AVG(rating) AS avg_rating
                    FROM ratings
                    GROUP BY service_Provider_Id
                ) r ON sp.id = r.service_Provider_Id
                ORDER BY $sortOrder";
    $statement = $db->prepare($query);
    $statement->execute(); 

    if (isset($_POST['servicesSearchButton'])) {
        $searchTerm = filter_input(INPUT_POST, 'servicesSearchTerm', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        $query =   "SELECT sp.*, FORMAT(avg_rating, 2) AS avg_rating
                FROM service_providers sp
                LEFT JOIN (
                    SELECT service_Provider_Id, AVG(rating) AS avg_rating
                    FROM ratings
                    GROUP BY service_Provider_Id
                ) r ON sp.id = r.service_Provider_Id
                WHERE name LIKE :searchTerm
                ORDER BY $sortOrder";
        $statement = $db->prepare($query);
        $statement->bindValue(':searchTerm', '%' . $searchTerm . '%', PDO::PARAM_STR);
        $statement->execute();
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="style.css">
    <title>Admin</title>
</head>
<body>
<div id="container">
    <?php if($_SESSION['type'] == 'admin'):?>
    <?php include('adminNavigation.php')?>
    <div id="content">
    <div class="sectionBox">
        <h2>Your are login as <?= $_SESSION['userName']?></h2>
        <a href="logout.php">logout</a>
        <div id="services">
            <section class="searchBar">
                <h2><?=$table?></h2>
                <form method="post">
                    <?php if($table == 'service_providers' || $table == 'customers'):?>
                    <label for="sort">Sort by:</label>
                        <select name="sort" id="sort">
                        <?php if($table == 'service_providers' || !(isset($_GET['table']))):?>
                            <option value="name" <?= $nameSelected?>>Name</option>
                            <option value="date" <?= $dateSelected?>>Created Date</option>
                            <option value="rating" <?= $rateSelected?>>Rating</option>
                        <?php elseif($table == 'customers'):?> 
                            <option value="name" <?= $nameSelected?>>Name</option>
                            <option value="date" <?= $dateSelected?>>Created Date</option>
                            <option value="phone" <?= $phoneNumberSelected?>>Phone Number</option>
                        <?php endif?>
                        </select>
                    <button type="submit" name="sortButton">Sort</button>
                    <?php endif?>
                    <input type="text" name="servicesSearchTerm" placeholder="Search for a row">
                    <button type="submit" name="servicesSearchButton">Search</button>
                </form>
            </section>
            <a href="create_<?=$table?>.php">Create new</a>
            <div>
                <form method="post">
                    <table>
                    <?php if($table == 'service_providers'):?>
                        <tr>
                            <th></th>
                            <th>Service Provider</th>
                            <th>Phone Number</th>
                            <th>Email Address</th>
                            <th>Creation date</th>
                            <th>Rate</th>
                        </tr>
                        <?php while($row = $statement->fetch()): ?>
                            <tr>
                                <td>
                                    <a href="edit_<?=$table?>.php?id=<?=$row['id']?>">Edit or Delete</a>
                                </td>
                                <td>
                                    <a href="serviceProvider.php?id=<?=$row['id']?>"><?=$row['name']?></a>
                                </td>
                                <td>
                                    <?=$row['phone_Number']?>
                                </td>
                                <td>
                                    <?=$row['email_Address']?>
                                </td>
                                <td>
                                    <?=$row['creation_Date']?>
                                </td>
                                <td>
                                    <?= $row['avg_rating'] !== null ? $row['avg_rating'] . '⭐' : 'Not rated' ?>
                                </td>
                            </tr>
                        <?php endwhile ?> 
                    <?php elseif($table == 'customers'):?>     
                        <tr>
                            <th></th>
                            <th>Customer</th>
                            <th>Phone Number</th>
                            <th>Creation date</th>
                        </tr>
                        <?php while($row = $statement->fetch()): ?>
                            <tr>
                                <td>
                                    <a href="edit_<?=$table?>.php?id=<?=$row['id']?>">Edit or Delete</a>
                                </td>
                                <td>
                                    <a href="customer.php?id=<?=$row['id']?>"><?=$row['name']?></a>
                                </td>
                                <td>
                                    <?=$row['phone_Number']?>
                                </td>
                                <td>
                                    <?=$row['creation_Date']?>
                                </td>
                            </tr>
                        <?php endwhile ?>   
                    <?php elseif($table == 'services'):?> 
                        <tr>
                            <th></th>
                            <th>Service</th>
                            <th>description</th>
                        </tr>
                        <?php while($row = $statement->fetch()): ?>
                            <tr>
                                <td>
                                    <a href="edit_<?=$table?>.php?id=<?=$row['id']?>">Edit or Delete</a>
                                </td>
                                <td>
                                    <?=$row['name']?>
                                </td>
                                <td>
                                    <?=$row['description']?>
                                </td>
                            </tr>
                        <?php endwhile ?>  
                    <?php endif?>  
                    </table>
                </form>   
            </div>
        </div>
    </div>
    <?php else:?>
        <h2>Only admins can access this page.</h2>
    <?php endif?>
    </div>
</div>
</body>
</html>