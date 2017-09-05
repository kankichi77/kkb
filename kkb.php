<?php
// COMMENTS

// DB SETTINGS
include "../../inc/kkb_dbinfo.inc";
session_start();

// SET Default time zone
date_default_timezone_set('Asia/Tokyo');

if ($_SESSION['loggedIn'] == 0) {
  header("Location: http://".$_SERVER['SERVER_NAME']."/kkb/login.php");
  die();
}

if ($_GET['m'] == 'lo') {
  $_SESSION['loggedIn'] = 0;
  header("Location: http://".$_SERVER['SERVER_NAME']."/kkb/login.php");
  die();
}

// Init
$id = "";
$item = "";
$amount = "";
$date = "";
$mode = "";
$category = "";
$method = "";
$op = "";
try {
  $d_today = new DateTime();
  $d_month_start = new DateTime(date('Y')."-".date('m')."-".'1');
  $d_month_end = new DateTime(date('Y')."-".date('m')."-".'1');
  $d_month_end->add(new DateInterval('P1M'));
} catch (Exception $er) {
    echo $er->getMessage();
    exit(1);
}

Class Entry {
  public $id = "";
  public $item = "";
  public $amount = "";
  public $date = "";
  public $category = "";
  public $method = "";
  public $op = "";

  public function init() {
    $id = "";
    $item = "";
    $amount = "";
    $date = "";
    $category = "";
    $method = "";
    $op = "";
  }

  public function set($i, $t, $a, $d, $c, $m, $o) {
    $id = $i;
    $item = $t;
    $amount = $a;
    $date = $d;
    $category = $c;
    $method = $m;
    $op = $o;
  }
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
  <link rel="stylesheet" href="jquery-ui.min.css">
  <script src="jquery-ui.min.js"></script>
  <script type="text/javascript">
$(document).ready( function() {
$( "#InputCategory" ).autocomplete({
		source: [
			'HPI', 'Kyosho', 'Losi',
			'Tamiya', 'Team Associated',
			'Team Durango', 'Traxxas', 'Yokomo'
		]
	}
);
</script>
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
  $id = htmlentities($_POST['id']);
  $btn = htmlentities($_POST['btn']);
  $category = htmlentities($_POST['Category']);
  $method = htmlentities($_POST['Method']);
  $op = htmlentities($_POST['OtherParty']);

  $e = new Entry();
  //$e->init();
  $e->id = "";
  $e->item = "";
  $e->amount = "";
  $e->date = date('Y-m-d');
  $e->category = "";
  $e->method = "";
  $e->op = "";

  if (strlen($mode) < 1) {
    $mode = htmlentities($_GET['m']);
    if (strlen($mode) < 1) {
      $mode = 'i';
    }
  }
  if (strlen($id) < 1) {
    $id = htmlentities($_GET['id']);
    if (strlen($id) < 1) {
      $id = "";
    }
  }

  if ($mode == "i") {
    if ( strlen($item) && strlen($amount)) {
      $e->id = $id;
      $e->item = $item;
      $e->amount = $amount;
      $e->date = $date;
      $e->category = $category;
      $e->method = $method;
      $e->op = $op;

      AddEntry($connection, $e);
      //$e->init();
      $e->id = "";
      $e->item = "";
      $e->amount = "";
      $e->date = date('Y-m-d');
      $e->category = "";
      $e->method = "";
      $e->op = "";
    }
  }
  if ($mode == "u" && $btn == "upd") {
      //$e->set($id, $item, $amount, $date);
      $e->id = $id;
      $e->item = $item;
      $e->amount = $amount;
      $e->date = $date;
      $e->category = $category;
      $e->method = $method;
      $e->op = $op;

      UpdateEntry($connection, $e);
      //$e->init();
      $e->id = "";
      $e->item = "";
      $e->amount = "";
      $e->date = date('Y-m-d');
      $e->category = "";
      $e->method = "";
      $e->op = "";
      $mode = "i";
  }
  if ($mode == "u" && $btn == "del") {
    DeleteEntry($connection, $id);
    $mode = "i";
  }
  if ($mode == "s" && strlen($id)) {
    $e = getEntry($connection, $id);
    $mode = "u";
  }
?>

<a href="<?=$_SERVER['SCRIPT_NAME']?>">Reload</a>
<!-- DEBUG -->
<!--
<?=$d_today->format('Y-m-d')?><BR>
<?=$d_month_start->format('Y-m-d')?><BR>
<?=$d_month_end->format('Y-m-d')?>
-->

<!-- Input form -->
<form action="<?PHP echo $_SERVER['SCRIPT_NAME'] ?>" method="POST">
  <input type="hidden" name="mode" value="<?=$mode?>">
  <input type="hidden" name="id" value="<?=$id?>">
  <input type="hidden" name="uid" value="<?=$_SESSION['uid']?>">
  <div class="form-group">
    <label for="InputCategory">Category</label>
    <input type="text" class="form-control" id="InputCategory" placeholder="Enter Category" name="Category"  value="<?=$e->category?>">
  </div>
  <div class="form-group">
    <label for="InputItem">Item</label>
    <input type="text" class="form-control" id="InputItem" placeholder="Enter Item" name="Item" maxlength="250" size="10" value="<?=$e->item?>"/>
  </div>
  <div class="form-group">
    <label for="InputAmount">Amount</label>
    <input type="text" class="form-control" id="InputAmount" placeholder="in JPY" name="Amount" maxlength="15" size="10" value="<?=$e->amount?>"/>
  </div>
  <div class="form-group">
    <label for="InputDate">Date</label>
    <input type="date" class="form-control" id="InputDate" placeholder="YYYY-MM-DD" name="Date" value="<?=$e->date?>" >
  </div>
  <div class="form-group">
    <label for="InputMethod">Method</label>
    <input type="text" class="form-control" id="InputMethod" placeholder="Enter Method" name="Method" value="<?=$e->method?>" >
  </div>
  <div class="form-group">
    <label for="InputOtherParty">Other Party / 相手先</label>
    <input type="text" class="form-control" id="InputOtherParty" placeholder="Enter Other Party" name="OtherParty" value="<?=$e->op?>" >
  </div>
  <?php
  if ($mode == "i") { ?>
    <button type="submit" class="btn btn-primary" name="btn" value="add">Add Data</button>
  <?php
  }
  if ($mode == "u") { ?>
    <button type="submit" class="btn btn-primary" name="btn" value="upd">Update Data</button>
    <button type="submit" class="btn btn-primary" name="btn" value="del">Delete Data</button>
  <?php
  } ?>
</form>
<!-- End Form -->

<p><a href="view.php">More Entries</a></p>

<!-- Quick Stats -->
<p><u><?=date('Y')?>年<?=date('n')?>月データ</u></p>
<?php
$query = "SELECT category, sum(amount) total FROM kkb_entry WHERE date > '";
$query .= $d_month_start->format('Y-m-d');
$query .= "' AND date < '";
$query .= $d_month_end->format('Y-m-d');
$query .= "' GROUP BY category ORDER BY total DESC";
$result = mysqli_query($connection, $query);
$total = 0;

while($query_data = mysqli_fetch_row($result)) {
  $total += $query_data[1];
  if ($query_data[0] == "") $x = "(BLANK)";
    else $x = $query_data[0];
  echo $x . ": " . number_format($query_data[1]);
  echo "<BR>";
}
echo "Total: " . number_format($total);
?>
<!-- Stats End -->

<!-- Display table data. -->
<p>
<table class="table table-striped">
  <thead>
  <tr>
    <th>ID</th>
    <th>Item</th>
    <th>Amount</th>
    <th>Date</th>
    <th>Category</th>
    <th>Method</th>
    <th>Other Party</th>
  </tr>
  </thead>
  <tbody>
<?php

$result = mysqli_query($connection, "SELECT * FROM kkb_entry ORDER BY id DESC LIMIT 10");

while($query_data = mysqli_fetch_row($result)) {
  echo "<tr>";
  echo "<th scope=\"row\"><a href=\"?m=s&id=", $query_data[0], "\">", $query_data[0], "</a></th>",
       "<td>", $query_data[1], "</td>",
       "<td>", number_format($query_data[3]), "</td>",
       "<td>", $query_data[4], "</td>",
       "<td>", $query_data[2], "</td>",
       "<td>", $query_data[10], "</td>",  // Method
       "<td>", $query_data[7], "</td>",  // Other Party
       "</tr>";
}
?>
</tbody>
</table>
</p>

<!-- Clean up. -->
<?php

  mysqli_free_result($result);
  mysqli_close($connection);

?>

<a href="<?=$_SERVER['SCRIPT_NAME']?>?m=lo">Log out</a>
</body>
</html>

<?php

/* Get an entry of the specific ID. */

function getEntry($connection, $id) {
  $result = mysqli_query($connection, "SELECT * FROM kkb_entry WHERE id = {$id}");
  $e = new Entry();
  while ($query_data = mysqli_fetch_row($result)) {
    $e->id = $query_data[0];
    $e->item = $query_data[1];
    $e->amount = $query_data[3];
    $e->date = $query_data[4];
    $e->category = $query_data[2];
    $e->method = $query_data[10];
    $e->op = $query_data[7];
  }
  return $e;
}

/* Delete an entry in the table. */

function DeleteEntry($connection, $id) {
   $query = "DELETE FROM kkb_entry WHERE id={$id}";
   if(!mysqli_query($connection, $query)) echo("<p>Error deleting entry data.</p>");
}

/* Update an entry in the table. */
function UpdateEntry($connection, $e) {
   $n = mysqli_real_escape_string($connection, $e->item);
   $a = mysqli_real_escape_string($connection, $e->amount);
   $d = mysqli_real_escape_string($connection, $e->date);
   $c = mysqli_real_escape_string($connection, $e->category);
   $m = mysqli_real_escape_string($connection, $e->method);
   $o = mysqli_real_escape_string($connection, $e->op);
   $i = $e->id;

   $query = "UPDATE kkb_entry SET ";
   $query .= "item='{$n}', amount={$a}, date='{$d}', category='{$c}', method='{$m}', ";
   $query .= "otherParty='{$o}', lastUpdated_by='{$_SESSION['uid']}', ";
   $query .= "lastUpdated_date='" . date('Y-m-d G:i:s') . "' ";
   $query .= "WHERE id={$i}";

   if(!mysqli_query($connection, $query)) echo("<p>Error updating entry data.</p>");
}

/* Add an entry to the table. */
function AddEntry($connection, $e) {
   $n = mysqli_real_escape_string($connection, $e->item);
   $a = mysqli_real_escape_string($connection, $e->amount);
   $d = mysqli_real_escape_string($connection, $e->date);
   $c = mysqli_real_escape_string($connection, $e->category);
   $m = mysqli_real_escape_string($connection, $e->method);
   $o = mysqli_real_escape_string($connection, $e->op);

   $query = "INSERT INTO `kkb_entry` (";
   $query .= "`Item`, `Amount`, `date`, `category`, `method`, `otherParty`, ";
   $query .= "`created_by`, created_date, `lastUpdated_by`, lastUpdated_date";
   $query .= ") VALUES (";
   $query .= "'$n', '$a', '$d', '$c', '$m', '$o', '";
   $query .= $_SESSION['uid'] . "', '" . date('Y-m-d G:i:s') . "', '";
   $query .= $_SESSION['uid'] . "', '" . date('Y-m-d G:i:s') . "');";

   if(!mysqli_query($connection, $query)) echo("<p>Error adding entry data.</p>");
   //echo $query;
}

/* Check whether the table exists and, if not, create it. */
function VerifyTable($connection, $tableName, $dbName) {
/*
  if(!TableExists($tableName, $connection, $dbName))
  {
     $query = "CREATE TABLE `KKB_Entry` (
         `ID` int NOT NULL AUTO_INCREMENT primary key,
         `Item` varchar(255) DEFAULT NULL,
         `Amount` double
       )";

     if(!mysqli_query($connection, $query)) echo("<p>Error creating table.</p>");
  }
*/
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
