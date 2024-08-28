<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');

require __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

function apidenvericek($symbol){

    $key = $_ENV["KEY"];

    $curl = curl_init();
    curl_setopt_array($curl, array(
      CURLOPT_URL => 'http://api.marketstack.com/v1/eod?access_key='.$key.'&symbols='.$symbol,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'GET',));
    
    $response = curl_exec($curl);
    curl_close($curl);
    $value = json_decode($response, true);
    $close = $value['data'][0]['close'];

    return $value;    

}
function sembolekle() {

    $close = apidenvericek($_GET["symbol"])['data'][0]['close'];

    $servername = $_ENV["servername"];
    $username = $_ENV["username"];
    $password = $_ENV["password"];
    $dbname = $_ENV["dbname"];
    
    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);
    // Check connection
    if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
    }
        $symbol = $_GET["symbol"];
        $amount = $_GET["amount"];
        $cost = $_GET["cost"];
    
    $sql = "INSERT INTO Semboller (symbol, amount, cost, price)
    VALUES ('".$symbol."', '".$amount."', '".$cost."', '".$close."');";

    if ($conn->query($sql) === TRUE) {
    } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
    }
    $conn->close();
    
}

function semboldenvericek(){

  $servername = $_ENV["servername"];
  $username = $_ENV["username"];
  $password = $_ENV["password"];
  $dbname = $_ENV["dbname"];
    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);
    // Check connection
    $sql = "SELECT id, symbol, amount, cost FROM Semboller";
    $result = $conn->query($sql);
    if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
    }    
//$rows = $result->fetch_all(MYSQLI_ASSOC);
  $rows = [];
    if ($result->num_rows > 0) {
        // output data of each row
        while($row = $result->fetch_assoc()) {
         "id: " . $row["id"]. " - Symbol: " . $row["symbol"]. " -Amount" . $row["amount"]." -Cost:" . $row["cost"]. "<br>";
          $close = apidenvericek($row["symbol"]);
          $sonveri = $close['data'][0]['close'];
          $row["yuzde"] = (($sonveri-$row["cost"])*100)/$row["cost"];
          $row["change"] = $sonveri-$row["cost"];
          $row["price"] = $sonveri;   
          $row["total"] = $row["amount"]*$row["price"];
          $row["profit"] = ($row["cost"]*$row["yuzde"]/10);     
          array_push($rows, $row);
        }
      } else {
        echo "0 results";
      }
      $conn->close();
      return $rows;

}


//fonksiyonlar bunun altında çağırılacak

if (isset($_GET["symbol"])) {
    sembolekle();
}

$final = semboldenvericek();


?>

<!DOCTYPE html>
<html lang="en" >
<head>
  <meta charset="UTF-8">
  <title>CodePen - Stock Market</title>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/modernizr/2.8.3/modernizr.min.js" type="text/javascript"></script>

<link rel='stylesheet' href='//ajax.googleapis.com/ajax/libs/jqueryui/1.11.2/themes/smoothness/jquery-ui.css'><link rel="stylesheet" href="./style.css">

</head>
<body>
<!-- partial:index.partial.html -->
<table class="tablesorter">
  <thead>
    <th>Stock</th>
    <th>Price</th>
    <th>Change</th>
    <th>%</th>
    <th>Amount</th>
    <th>Total Profit</th>
    <th>Total Worth</th>

  </thead>
  <tbody>
  <?php
  foreach ($final as $row) {

      $row["yuzde"] = number_format((float)$row["yuzde"], 2, '.', '');
      $row["change"] = number_format((float)$row["change"], 2, '.', '');
      $row["profit"] = number_format((float)$row["profit"], 2, '.', '');

      echo "<tr class='stock increase'><td class='name'>".$row["symbol"]."</td><td class='value'>".$row["price"]."</td><td class='change'>".$row["change"]."</td><td class='percentage'>".$row["yuzde"]."</td><td class='amount'>".$row["amount"]."</td><td class='change'>".$row["profit"]."$</td><td class='change'>".$row["total"]."$</td></tr>";

  }
  ?>

<form method="GET">
  Symbol: <input type="text" name="symbol">
  <br>
  Amount: <input type="text" name="amount">
  <br>
  Cost: <input type="text" name="cost">
  <br>


  <input type="submit">

  </tbody>
</table>
<!-- partial -->
  <script src='//cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js'></script>
<script src='//ajax.googleapis.com/ajax/libs/jqueryui/1.11.2/jquery-ui.min.js'></script>
<script src='https://rawgithub.com/joequery/Stupid-Table-Plugin/master/stupidtable.min.js'></script><script  src="./script.js"></script>
 
</body>
</html>
