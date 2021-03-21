 <?php

$servername = "localhost";
$username = "abdelmoujib";
$password = "159357";

try {
  $bdd = new PDO("mysql:host=$servername;dbname=noQ", $username, $password);
  // set the PDO error mode to exception
  $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
  echo "Connection failed: " . $e->getMessage();
}

