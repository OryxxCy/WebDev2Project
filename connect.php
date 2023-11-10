 <?php
     define('DB_DSN','mysql:host=localhost;dbname=serverside;charset=utf8');
     define('DB_USER','serveruser');
     define('DB_PASS','gorgonzola7!');     

     try {
         $db = new PDO(DB_DSN, DB_USER, DB_PASS);
     } catch (PDOException $error) {
         print "Error: " . $error->getMessage();
         die();
     }
 ?>