<?php 
include "../../inc/kkb_dbinfo.inc";

// Init
$id = "";
$item = "";
$amount = "";
$date = "";
$mode = "";

Class Entry {
  public $id = "";
  public $item = "";
  public $amount = "";
  public $date = "";
}
?>
<!DOCTYPE html>
<html lang="jp">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="stylesheet" 
    href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/css/bootstrap.min.css">
  <title>KKB</title>
  <script src="jquery-3.2.1.js"></script>
</head>
<body>
<h1>KKB</h1>
<?php
  /* Connect to MySQL and select the database. */
  $connection = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD);
  if (mysqli_connect_errno()) echo "Failed to connect to MySQL: " . mysqli_connect_error();
  $database = mysqli_select_db($connection, DB_DATABASE);

  /* Ensure that the KKB_Entry table exists. */
  VerifyTable($connection, "kkb_entry", DB_DATABASE); 

  /* If input fields are populated, add a row to the Employees table. */
  $mode = htmlentities($_POST['mode']);
  $item = htmlentities($_POST['Item']);
  $amount = htmlentities($_POST['Amount']);
  $date = htmlentities($_POST['Date']);
  $id = htmlentities($_GET['id']);
  $e = new Entry();

  if (strlen($mode) < 1) {
    $mode = htmlentities($_GET['m']);
  }
  if ($mode == "i" && (strlen($item) || strlen($amount))) {
    AddEntry($connection, $item, $amount, $date);
    $item = "";
    $amount = "";
    $date = "";
  }
  if ($mode == 'u' && strlen($id)) {
    $e = getEntry($connection, $id);
    $item = $e->item;
    
  }

?>

<a href="<?=$_SERVER['SCRIPT_NAME']?>">Reload</a>
<BR>
ID: <?=$id?>
<BR>
Item: <?=$item?>
<BR><BR>

<!-- Input form -->
<form action="<?PHP echo $_SERVER['SCRIPT_NAME'] ?>" method="POST">
  <input type="hidden" name="mode" value="i">
  <div class="form-group">
    <label for="InputItem">Item</label>
    <input type="text" class="form-control" id="InputItem" placeholder="Enter Item" name="Item" maxlength="250" size="10" value="<?=$item?>"/>
  </div>
  <div class="form-group">
    <label for="InputAmount">Amount</label>
    <input type="text" class="form-control" id="InputAmount" placeholder="in JPY" name="Amount" maxlength="15" size="10" />
  </div>
  <div class="form-group">
    <label for="InputDate">Date</label>
    <input type="date" class="form-control" id="InputDate" placeholder="YYYY-MM-DD" name="Date">
  </div>
  <button type="submit" class="btn btn-primary">Add Data</button>
</form>

<!-- Display table data. -->
<table class="table table-striped">
  <thead>
  <tr>
    <th>ID</th>
    <th>Item</th>
    <th>Amount</th>
    <th>Date </th>
  </tr>
  </thead>
  <tbody>
<?php

$result = mysqli_query($connection, "SELECT * FROM kkb_entry ORDER BY id DESC LIMIT 10"); 

while($query_data = mysqli_fetch_row($result)) {
  echo "<tr>";
  echo "<th scope=\"row\"><a href=\"?m=u&id=", $query_data[0], "\">", $query_data[0], "</a></th>",
       "<td>", $query_data[1], "</td>",
       "<td>", $query_data[2], "</td>",
       "<td>", $query_data[3], "</td>";
  echo "</tr>";
}
?>
</tbody>
</table>

<!-- Clean up. -->
<?php

  mysqli_free_result($result);
  mysqli_close($connection);

?>

</body>
</html>

<?php

/* Get an entry of the specific ID. */

function getEntry($connection, $id) {
  $result = mysqli_query($connection, "SELECT * FROM kkb_entry WHERE id = " + $id);
  $e = new Entry();
  while ($query_data = mysqli_fetch_row($result)) {
    $e->id = $query_data[0];
    $e->item = $query_data[1];
    $e->amount = $query_data[2];
    $e->date = $query_data[3];
  }
  return $e;
}

/* Add an entry to the table. */

function AddEntry($connection, $item, $amount, $date) {
   $n = mysqli_real_escape_string($connection, $item);
   $a = mysqli_real_escape_string($connection, $amount);
   $d = mysqli_real_escape_string($connection, $date);

   $query = "INSERT INTO `kkb_entry` (`Item`, `Amount`, `date`) VALUES ('$n', '$a', '$d');";

   if(!mysqli_query($connection, $query)) echo("<p>Error adding entry data.</p>");
}

/* Check whether the table exists and, if not, create it. */
function VerifyTable($connection, $tableName, $dbName) {
  if(!TableExists($tableName, $connection, $dbName)) 
  { 
     $query = "CREATE TABLE `KKB_Entry` (
         `ID` int NOT NULL AUTO_INCREMENT primary key,
         `Item` varchar(255) DEFAULT NULL,
         `Amount` double
       )";

     if(!mysqli_query($connection, $query)) echo("<p>Error creating table.</p>");
  }
}

/* Check for the existence of a table. */

function TableExists($tableName, $connection, $dbName) {
  $t = mysqli_real_escape_string($connection, $tableName);
  $d = mysqli_real_escape_string($connection, $dbName);

  $checktable = mysqli_query($connection, 
      "SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_NAME = '$t' AND TABLE_SCHEMA = '$d'");

  if(mysqli_num_rows($checktable) > 0) return true;

  return false;
}

?>
