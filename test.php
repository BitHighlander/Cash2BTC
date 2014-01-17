<?php
//*******
//  SEND
//*******

//mysqli

$sqlicon = new mysqli('', '', '','BETA00016');
if (mysqli_connect_errno()) {
printf("Connect failed: %s\n", mysqli_connect_error());
exit();
}

//pdo

$host = "";
$db_name = "";
$username = "";
$password = "";
try {
    $con = new PDO("mysql:host={$host};dbname={$db_name}", $username, $password);
}catch(PDOException $exception){ //TO handle connection error
    echo "Connection error: " . $exception->getMessage();
}



//pubnub

include ('php/Pubnub.php');

//EDIT ME

$pubnub = new Pubnub(
    "",  ## PUBLISH_KEY
    "",  ## SUBSCRIBE_KEY
    "",      ## SECRET_KEY
    false    ## SSL_ON?
);




//****************
//      atm
//****************

$arr = array("Server"=>"Server1 has come ONLINE");
//send to channel
$info = $pubnub->publish(array(
    'channel' => 'BETA00016', ## REQUIRED Channel to Send
    'message' => $arr   ## REQUIRED Message String/Array
));

//start timer
while(true) {




//****************
//   Price Server
//****************

//GOX
        

    $c = curl_init();
    curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($c, CURLOPT_HTTPHEADER, array('Accept: application/json', 'Content-Type: application/json'));
    curl_setopt($c, CURLOPT_URL, 'http://data.mtgox.com/api/2/BTCUSD/money/ticker');
    $data = curl_exec($c);
    curl_close($c);
    $obj = json_decode($data);
    $last = print_r($obj->{'data'}->{'last'}->{'display_short'}."\n", true);
    $entry = filter_var($last, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);


//bitstamp

//bitcoinaverage

$arr = array("btcPrice"=>"$entry");
//send to channel
$info = $pubnub->publish(array(
    'channel' => 'BETA00016', ## REQUIRED Channel to Send
    'message' => $arr   ## REQUIRED Message String/Array
));





// how many bills are in the atm?


$sql = $query = "SELECT SUM(b1) FROM bills"; $result = $sqlicon->query($sql);if ($result->num_rows > 0) {while($row = $result->fetch_assoc()) {$sum1 = $row['SUM(b1)'];}}
$sql = $query = "SELECT SUM(b5) FROM bills"; $result = $sqlicon->query($sql);if ($result->num_rows > 0) {while($row = $result->fetch_assoc()) {$sum5 = $row['SUM(b5)'];}}
$sql = $query = "SELECT SUM(b10) FROM bills"; $result = $sqlicon->query($sql);if ($result->num_rows > 0) {while($row = $result->fetch_assoc()) {$sum10 = $row['SUM(b10)'];}}
$sql = $query = "SELECT SUM(b20) FROM bills"; $result = $sqlicon->query($sql);if ($result->num_rows > 0) {while($row = $result->fetch_assoc()) {$sum20 = $row['SUM(b20)'];}}
$sql = $query = "SELECT SUM(b50) FROM bills"; $result = $sqlicon->query($sql);if ($result->num_rows > 0) {while($row = $result->fetch_assoc()) {$sum50 = $row['SUM(b50)'];}}
$sql = $query = "SELECT SUM(b100) FROM bills"; $result = $sqlicon->query($sql);if ($result->num_rows > 0) {while($row = $result->fetch_assoc()) {$sum100 = $row['SUM(b100)'];}}
$total = ($sum1 *1) + ($sum5 * 5) + ($sum10 * 10) + ($sum20 * 20) + ($sum50 *50) + ($sum100 * 100);
$sql = $query = "SELECT SUM(usd) FROM btcout";$result = $sqlicon->query($sql); if ($result->num_rows > 0) { while($row = $result->fetch_assoc()) {$BTCpaid = $row['SUM(usd)'];}}
$sestotal = $total - $BTCpaid;


if ($sestotal == 0) { echo "     ***   nothing to do   ***     "; } 

else { 

//****************
//      LIVE SESSION
//****************

echo "     ***   Payment Pending   ***     "; 


$arr = array("Session"=>"LIVE");
//send to channel
$info = $pubnub->publish(array(
    'channel' => 'BETA00016', ## REQUIRED Channel to Send
    'message' => $arr   ## REQUIRED Message String/Array
));

//Deturmin the age the last bill

//get timestamp of last bill
$row = $con->query("SELECT * FROM bills ORDER BY id DESC LIMIT 1")->fetch(); $lastbill = $row['time']; 
//objectify
$billdatetime = new DateTime($lastbill);
//get the current timestamp
$now = new DateTime();
//get the differance
$seconds = $now->getTimestamp() - $billdatetime->getTimestamp();

//echo $seconds;
//echo "     ***   The current age is ".$seconds."   ***     "; 

$timer = 10;
$phaseshift = 3600 + $timer;

if ($seconds > $phaseshift) {


//****************
//     MAKE PAYMENT
//****************

//GET cient addresss

// END session and send bitcoin
$sql = "SELECT * FROM address ORDER BY id DESC LIMIT 1"; $result = $sqlicon->query($sql);if ($result->num_rows > 0) {while($row = $result->fetch_assoc()) {$CLIENTaddress = $row['address'];}}


if (empty($CLIENTaddress)) {
$address = 'EMPTY ERROR';

//what to do if no address given

//
echo "YOU MUST SCAN AN ADDRESS TO RECIEVE BITCOIN";
$arr = array("ERROR"=>"Session is HOT but no recieing address defined");
//send to channel
$info = $pubnub->publish(array(
    'channel' => 'BETA00012', ## REQUIRED Channel to Send
    'message' => $arr   ## REQUIRED Message String/Array
));


} else {


//GET cash2btc addresss
$SERVICEaddress = "1BrBydEKrAwJxMauGEw79P6WP1aAoRLv16";

//Get owner address
$sql = "SELECT * FROM config ORDER BY id DESC LIMIT 1"; $result = $sqlicon->query($sql);if ($result->num_rows > 0) {while($row = $result->fetch_assoc()) {$HOSTaddress = $row['address'];}}



//Get owner percentage
$sql = "SELECT * FROM config ORDER BY id DESC LIMIT 1"; $result = $sqlicon->query($sql);if ($result->num_rows > 0) {while($row = $result->fetch_assoc()) {$hrate = $row['percentage'];}}





//****************
//   calculate send amounts
//****************

$txout = $sestotal / $entry;
$satoshi = $txout * 100000000;
$amount = round($satoshi);

//echo $amount;
$amountSERVICE = $amount * (1/100);
$amountSERVICE = round($amountSERVICE);

$namount = $amount - $amountSERVICE;
$namount = round($namount);

$amountHOST = $namount * $hrate;
$amountHOST = round($amountHOST);

$amountCLIENT = ($amountSERVICE + $amountHOST) - $amount;
$amountCLIENT = round($amountCLIENT);


//****************
//   send the bitcoin
//****************



$guid="";
$firstpassword="";
$secondpassword="";

$recipients = urlencode('{
                  "'.$SERVICEaddress.'": '.$amountSERVICE.',
                  "'.$HOSTaddress.'": '.$amountHOST.',
                  "'.$CLIENTaddress.'": '.$amountCLIENT.'
               }');

$c = curl_init ("http://blockchain.info/merchant/$guid/sendmany?password=$firstpassword&second_password=$secondpassword&recipients=$recipients");
curl_setopt ($c, CURLOPT_RETURNTRANSFER, true);
$nrecipt = curl_exec($c);

//var_dump($nrecipt);

// PAID OUT
$query = "INSERT INTO btcout SET usd = ?"; $stmt = $con->prepare($query); $stmt->bindParam(1, $sestotal);$stmt->execute();


/*
//publish
echo "     ***   Payment SENT ".$nrecipt."   ***     "; 
$arr = array("paid"=>"$sestotal");
//send to channel
$info = $pubnub->publish(array(
    'channel' => 'BETA00016', ## REQUIRED Channel to Send
    'message' => $arr   ## REQUIRED Message String/Array
));
$arr = array("recipt"=>"$nrecipt");
//send to channel
$info = $pubnub->publish(array(
    'channel' => 'BETA00016', ## REQUIRED Channel to Send
    'message' => $arr   ## REQUIRED Message String/Array
));

*/



?>
