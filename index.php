<?php

require('connect.php');

$query = "SELECT * FROM services";
$statement = $db->prepare($query);
$statement->execute(); 

if (isset($_POST['servicesSearchButton'])) {
    $searchTerm = filter_input(INPUT_POST, 'servicesSearchTerm', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    $query = "SELECT * FROM services WHERE name LIKE :searchTerm";
    $statement = $db->prepare($query);
    $statement->bindValue(':searchTerm', '%' . $searchTerm . '%', PDO::PARAM_STR);
    $statement->execute();
}

$serviceProvidersQuery = "SELECT * FROM service_Providers";
$serviceProvidersStatement = $db->prepare($serviceProvidersQuery);
$serviceProvidersStatement->execute(); 

if (isset($_POST['serviceProvidersSearchButton'])) {
    $searchTerm = filter_input(INPUT_POST, 'serviceProvidersSearchTerm', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    $serviceProvidersQuery = "SELECT * FROM service_providers WHERE name LIKE :searchTerm";
    $serviceProvidersStatement = $db->prepare($serviceProvidersQuery);
    $serviceProvidersStatement->bindValue(':searchTerm', '%' . $searchTerm . '%', PDO::PARAM_STR);
    $serviceProvidersStatement->execute();
}

// if (isset($_POST['selectedServiceId'])) {
//     $selectedServiceId = $_POST['selectedServiceId'];

//     $selectedServiceQuery = "SELECT * FROM service_providers_services WHERE service_Id = :selectedServiceId";
//     $selectedServiceStatement = $db->prepare($selectedServiceQuery);
//     $selectedServiceStatement->bindValue(':selectedServiceId', $selectedServiceId, PDO::PARAM_INT);
//     $selectedServiceStatement->execute();

//     while($serviceProvidersServices = $selectedServiceStatement->fetch()){
//         $selectedServiceProviderId = $serviceProvidersServices['service_Provider_Id'];

//         $selectedServiceProviderQuery = "SELECT * FROM service_providers WHERE service_Provider_Id = :selectedServiceProviderId";
//         $selectedServiceProviderStatement = $db->prepare($selectedServiceProviderQuery);
//         $selectedServiceProviderStatement->bindValue(':selectedServiceProviderId', $selectedServiceProviderId, PDO::PARAM_INT);
//         $selectedServiceProviderStatement->execute();
//     }
//}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="style.css" />
    <title>Document</title>
</head>
<body>
<div id="container">
<?php include('navigation.php')?>
<div id="content">
    <div id="about">
        <h2>About ServicesFinders</h2>
        <section>
        <p>
            "ServicesFinders" is a business dedicated to simplifying theprocess of finding various services, including cleaning, plumbing,and electrical services. 
            The company's mission is to help users easily find the services they need in one convenient place. 
            This not only makes searching for services easier but also supports small localservice providers in getting noticed and building their businesses.
        </p>
        </section>
    </div>

    <div id="services">
        <section class="searchBar">
            <h2>Services</h2>
            <form method="post">
                <input type="text" name="servicesSearchTerm" placeholder="Search for a service">
                <button type="submit" name="servicesSearchButton">Search</button>
            </form>
        </section>
        <section>
            <form method="post">
                <?php while($row = $statement->fetch()): ?>
                    <button type="submit" name="selectedServiceId" value="<?= $row['id'] ?>"><?=$row['name']?></button>
                <?php endwhile ?>
            </form>   
        </section>
    </div>

    <div id="serviceProviders">
        <section class="searchBar">
            <h2>Service Providers</h2>
            <form method="post">
                <input type="text" name="serviceProvidersSearchTerm" placeholder="Search for a service provider">
                <button type="submit" name="serviceProvidersSearchButton">Search</button>
            </form>
        </section>
        <section>
            <?php while($serviceProviderRow = $serviceProvidersStatement->fetch()): ?>
                <p><a href="serviceProvider.php?id=<?=$serviceProviderRow['id']?>"><?=$serviceProviderRow['name']?></a></p>
            <?php endwhile ?>  
        </section>
    </div>
</div>
</div>
</body>
</html>