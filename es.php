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

    $servername = "127.0.0.1";
    $username = "root";
    $password = "root";
    $dbname = "borsa";
    
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
      echo "New record created successfully";
    } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
    }
    $conn->close();
    
}

function semboldenvericek(){
    
    $servername = "127.0.0.1";
    $username = "root";
    $password = "root";
    $dbname = "borsa";
    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);
    // Check connection
    $sql = "SELECT id, symbol, amount, cost FROM Semboller";
    $result = $conn->query($sql);
    if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
    }    

    if ($result->num_rows > 0) {
        // output data of each row
        while($row = $result->fetch_assoc()) {
          echo "id: " . $row["id"]. " - Symbol: " . $row["symbol"]. " -Amount" . $row["amount"]." -Cost:" . $row["cost"]. "<br>";
          $toplam = (apidenvericek($row["symbol"])['data'][0]['close']-$row["cost"])*$row["amount"];
        }
      } else {
        echo "0 results";
      }
      $conn->close();

      return $toplam;

}


//fonksiyonlar bunun altında çağırılacak

if (isset($_GET["symbol"])) {
    sembolekle();
}

$toplam = semboldenvericek();
echo ($toplam);

?>


<!DOCTYPE html>
<html>
<body>

<form method="GET">
  Symbol: <input type="text" name="symbol">
  <br>
  Amount: <input type="text" name="amount">
  <br>
  Cost: <input type="text" name="cost">
  <br>


  <input type="submit">
</form>

</body>