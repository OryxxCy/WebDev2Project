<?php

require('connect.php');
session_start();

$nameSelected = "";
$locationSelected = "";
$rateSelected = "";
$sortfocus = "";
$searchfocus = "";

$currentOffset = isset($_GET['page']) ? $_GET['page'] : 0;
$pageNumber = 1;
$totalPage = 0;

$serviceProviderHeader = "Service Providers";
$slideNum = 1;

$query = "SELECT * FROM services";
$statement = $db->prepare($query);
$statement->execute(); 

$serviceProvidersQuery =   "SELECT sp.*, FORMAT(avg_rating, 2) AS avg_rating
                            FROM service_providers sp
                            LEFT JOIN (
                                SELECT service_Provider_Id, AVG(rating) AS avg_rating
                                FROM ratings
                                GROUP BY service_Provider_Id
                            ) r ON sp.id = r.service_Provider_Id
                            ORDER BY sp.name ASC";

$serviceProvidersStatement = $db->prepare($serviceProvidersQuery);
$serviceProvidersStatement->execute(); 

$searchTerm = filter_input(INPUT_GET, 'searchTerm', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

if(isset($_GET['searchTerm']))
{
    $serviceProvidersQuery = "SELECT * FROM service_providers WHERE name LIKE :searchTerm";
    $serviceProvidersStatement = $db->prepare($serviceProvidersQuery);
    $serviceProvidersStatement->bindValue(':searchTerm', '%' . $searchTerm . '%', PDO::PARAM_STR);
    $serviceProvidersStatement->execute();
    $totalPage = $serviceProvidersStatement->rowCount();

    $serviceProvidersQuery =   "SELECT *, FORMAT(AVG(avg_rating), 2) AS avg_rating
                                FROM Service_Providers sp
                                LEFT JOIN (
                                    SELECT service_Provider_Id, AVG(rating) AS avg_rating
                                    FROM ratings 
                                    GROUP BY service_Provider_Id
                                ) r ON sp.id = r.service_Provider_Id
                                WHERE sp.name LIKE :searchTerm
                                GROUP BY sp.id
                                LIMIT 5 OFFSET $currentOffset";
    $serviceProvidersStatement = $db->prepare($serviceProvidersQuery);
    $serviceProvidersStatement->bindValue(':searchTerm', '%' . $searchTerm . '%', PDO::PARAM_STR);
    $serviceProvidersStatement->execute();

    $searchfocus = "autofocus";
}

if($_POST)
{
    if (isset($_POST['servicesSearchButton'])) {
        $searchTerm = filter_input(INPUT_POST, 'servicesSearchTerm', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    
        $query = "SELECT * FROM services WHERE name LIKE :searchTerm";
        $statement = $db->prepare($query);
        $statement->bindValue(':searchTerm', '%' . $searchTerm . '%', PDO::PARAM_STR);
        $statement->execute();
        $serviceProviderHeader = "Service Providers";
    }else if(isset($_POST['serviceProvidersResetButton'])){
        header("Location: index.php");
    }else if (isset($_POST['serviceProvidersSearchButton'])) {
        $searchTerm = filter_input(INPUT_POST, 'serviceProvidersSearchTerm', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        header("Location: index.php?searchTerm=". $searchTerm);
    } else if (isset($_POST['sort'])) {
        $sortOrder;
         
         switch ($_POST['sort']) {
             case "name":
                 $sortOrder = "name ASC"; 
                 $nameSelected = "selected";
                 break;
             case "location":
                 $sortOrder = "location ASC"; 
                 $locationSelected = "selected";
                 break;
             case "rating":
                 $sortOrder =   "avg_rating DESC";
                 $rateSelected = "selected";
                 break; 
         }
     
         $serviceProvidersQuery =  "SELECT sp.*, FORMAT(avg_rating, 2) AS avg_rating
                                    FROM service_providers sp
                                    LEFT JOIN (
                                        SELECT service_Provider_Id, AVG(rating) AS avg_rating
                                        FROM ratings
                                        GROUP BY service_Provider_Id
                                    ) r ON sp.id = r.service_Provider_Id
                                    ORDER BY $sortOrder";
         $serviceProvidersStatement = $db->prepare($serviceProvidersQuery);
         $serviceProvidersStatement->execute();

         $sortfocus = "autofocus";
     }
    
    
    if (isset($_POST['selectedServiceId'])) {
        $selectedServiceId = $_POST['selectedServiceId'];
    
        $selectedServiceQuery = "SELECT * FROM service_providers_services WHERE service_Id = :selectedServiceId";
        $selectedServiceStatement = $db->prepare($selectedServiceQuery);
        $selectedServiceStatement->bindValue(':selectedServiceId', $selectedServiceId, PDO::PARAM_INT);
        $selectedServiceStatement->execute();
    
        $selectedServiceNameQuery = "SELECT * FROM services WHERE id = :selectedServiceId";
        $selectedServiceNameStatement = $db->prepare($selectedServiceNameQuery);
        $selectedServiceNameStatement->bindValue(':selectedServiceId', $selectedServiceId, PDO::PARAM_INT);
        $selectedServiceNameStatement->execute(); 
        $selectedServiceName = $selectedServiceNameStatement->fetch();
        $serviceProviderHeader = $selectedServiceName['name'] . '-' . $selectedServiceName['description'];
    }
}

/*
*Retrieves a service provider information.
*
*Param comselectedServiceProviderIdment : the id of the service provider.
*Return The name of the service provider.
*/
$findServiceProvider = function($selectedServiceProviderId) use ($db)  {
    $selectedServiceProviderQuery = "SELECT * FROM service_providers WHERE id = :selectedServiceProviderId";
    $selectedServiceProviderStatement = $db->prepare($selectedServiceProviderQuery);
    $selectedServiceProviderStatement->bindValue(':selectedServiceProviderId', $selectedServiceProviderId, PDO::PARAM_INT);
    $selectedServiceProviderStatement->execute();
    $selectedServiceProvider = $selectedServiceProviderStatement->fetch();
    return $selectedServiceProvider['name'];
};
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="style.css">
    <title>Document</title>
</head>
<body>
<div id="container">
<?php include('navigation.php')?>
<div id="content">
    <div id="about">
        <h2>About ServicesFinders</h2>
        <div>
        <p>
            "ServicesFinders" is a business dedicated to simplifying theprocess of finding various services, including cleaning, plumbing,and electrical services. 
            The company's mission is to help users easily find the services they need in one convenient place. 
            This not only makes searching for services easier but also supports small localservice providers in getting noticed and building their businesses.
        </p>
        </div>
    </div>
    <div class="sectionBox">
        <div class="searchBar">
            <h2>Services</h2>
            <form method="post">
                <input type="text" name="servicesSearchTerm" placeholder="Search for a service">
                <button type="submit" name="servicesSearchButton">Search</button>
            </form>
        </div>
        <div>
            <form method="post">
                <?php while($row = $statement->fetch()): ?>
                    <button type="submit" name="selectedServiceId" value="<?= $row['id'] ?>"><?=$row['name']?></button>
                <?php endwhile ?>
            </form>   
        </div>
    </div>

    <div class="sectionBox">
        <div class="searchBar" >
            <form method="post">
                <label for="sort">Sort by:</label>
                <select name="sort" id="sort" <?= $sortfocus?>>
                    <option value="name" <?= $nameSelected?>>Name</option>
                    <option value="location" <?= $locationSelected?>>Location</option>
                    <option value="rating" <?= $rateSelected?>>Rate</option>
                </select>
                <button type="submit" name="sortServiceProviderButton">Sort</button>
                <input type="text" name="serviceProvidersSearchTerm" placeholder="Search for a service provider" <?=$searchfocus?>>
                <button type="submit" name="serviceProvidersSearchButton">Search</button>
                <button type="submit" name="serviceProvidersResetButton">Reset</button>
            </form>
        </div>
        <h2><?=$serviceProviderHeader?></h2>
        <div>
            <?php if(isset($_POST['selectedServiceId'])):?>
                <?php while($serviceProvidersServices = $selectedServiceStatement->fetch()): ?>
                    <p><a href="serviceProvider.php?id=<?=$serviceProvidersServices['service_Provider_Id']?>"><?=$findServiceProvider($serviceProvidersServices['service_Provider_Id'])?></a></p>
                    <p>$<?= $serviceProvidersServices['price']?></p>
                <?php endwhile ?>  
            <?php else:?>
                <table>
                    <tr>
                        <th>Service Provider</th>
                        <th>Phone Number</th>
                        <th>Email Address</th>
                        <th>Creation date</th>
                        <th>Rate</th>
                    </tr>
                    <?php while($serviceProviderRow = $serviceProvidersStatement->fetch()): ?>
                        <tr>
                            <td>
                                <a href="serviceProvider.php?id=<?=$serviceProviderRow['id']?>"><?=$serviceProviderRow['name']?></a>
                            </td>
                            <td>
                                <?=$serviceProviderRow['phone_Number']?>
                            </td>
                            <td>
                                <?=$serviceProviderRow['email_Address']?>
                            </td>
                            <td>
                                <?=$serviceProviderRow['location']?>
                            </td>
                            <td>
                                <?= $serviceProviderRow['avg_rating'] !== null ? $serviceProviderRow['avg_rating'] . '⭐' : 'Not rated' ?>
                            </td>
                        </tr>
                    <?php endwhile ?> 
                </table>
                <div class="pageNav">
                <?php if($totalPage > 5 && !(isset($_POST['sortServiceProviderButton']))):?>
                    <?php $previous =  $currentOffset - 5?>
                    <?php if($previous >= 0):?>
                        <a href="index.php?page=<?=$previous?>&searchTerm=<?=$searchTerm?>">Previous</a>
                    <?php endif?>
                    <?php for($i=0; $i<$totalPage ; $i+=5):?>
                        <a href="index.php?page=<?=$i?>&searchTerm=<?=$searchTerm?>"><?=$pageNumber?></a>
                        <?php $pageNumber++?>
                    <?php endfor?>
                    <?php $next =  $currentOffset + 5?>
                    <?php if($next < $totalPage):?>
                        <a href="index.php?page=<?=$next?>&searchTerm=<?=$searchTerm?>">Next</a>
                    <?php endif?>
                <p><?=$totalPage?> search results.</p>
                <?php endif?>
                <?php endif?>
                </div>
        </div>
    </div>
</div>
</div>
</body>
</html>