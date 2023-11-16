<?php

require('connect.php');

session_start();

if (!isset($_SESSION['userName'])) {
    header('Location: login.php');
    exit();
}

if(isset($_GET['table'])){
    $table = filter_input(INPUT_GET, 'table', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $columnSearch = filter_input(INPUT_GET, 'column', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    $query = "SELECT * FROM $table";
    $statement = $db->prepare($query);
    $statement->execute(); 

    if (isset($_POST['servicesSearchButton'])) {
        $searchTerm = filter_input(INPUT_POST, 'servicesSearchTerm', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        $query = "SELECT * FROM $table WHERE $columnSearch LIKE :searchTerm";
        $statement = $db->prepare($query);
        $statement->bindValue(':searchTerm', '%' . $searchTerm . '%', PDO::PARAM_STR);
        $statement->execute();
    }
}else{
    $table = "service_providers";
    $columnSearch = "name";
    $query = "SELECT * FROM service_providers";
    $statement = $db->prepare($query);
    $statement->execute(); 

    if (isset($_POST['servicesSearchButton'])) {
        $searchTerm = filter_input(INPUT_POST, 'servicesSearchTerm', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        $query = "SELECT * FROM service_providers WHERE name LIKE :searchTerm";
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
    <link rel="stylesheet" type="text/css" href="style.css" />
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
                    <input type="text" name="servicesSearchTerm" placeholder="Search for a service">
                    <button type="submit" name="servicesSearchButton">Search</button>
                </form>
            </section>
            <a href="create_<?=$table?>.php">Create new</a>
            <section>
                <form method="post">
                    <?php while($row = $statement->fetch()): ?>
                        <p><?=$row[$columnSearch]?></p>
                        <a href="edit_<?=$table?>.php?id=<?=$row['id']?>">Edit or Delete</a>
                    <?php endwhile ?>
                </form>   
            </section>
        </div>
    </div>
    <?php else:?>
        <h2>Only admins can access this page.</h2>
    <?php endif?>
    </div>
</div>
</body>
</html>