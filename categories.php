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

Class Category {
  public $id = "";
  public $groupName = "";
  public $category = "";
}
?>
<!DOCTYPE html>
<html lang="jp">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="stylesheet"
    href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/css/bootstrap.min.css">
  <title>KKB Categories</title>
  <script src="jquery-3.2.1.js"></script>
  <link rel="stylesheet" href="jquery-ui.min.css">
  <script src="jquery-ui.min.js"></script>
</head>
<body>
<h1>KKB Categories</h1>
<?php
  /* Connect to MySQL and select the database. */
  $connection = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD);
  if (mysqli_connect_errno()) echo "Failed to connect to MySQL: " . mysqli_connect_error();
  $database = mysqli_select_db($connection, DB_DATABASE);

  /* Ensure that the KKB_Entry table exists. */
  VerifyTable($connection, "kkb_entry", DB_DATABASE);

  /* If input fields are populated, add a row to the Employees table. */
  $mode = htmlentities($_POST['mode']);
  $groupName = htmlentities($_POST['GroupName']);
  $category = htmlentities($_POST['Category']);
  $id = htmlentities($_POST['id']);
  $btn = htmlentities($_POST['btn']);

  $c = new Category();
  //$e->init();
  $c->id = "";
  $c->groupName = "";
  $c->category = "";

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
    if ( strlen($groupName) && strlen($category)) {
      $c->id = $id;
      $c->groupName = $groupName;
      $c->category = $category;

      AddCategory($connection, $c);
      //$e->init();
      $c->id = "";
      $c->groupName = "";
      $c->category = "";
    }
  }

  if ($mode == "u" && $btn == "upd") {
      //$e->set($id, $item, $amount, $date);
      $c->id = $id;
      $c->groupName = $groupName;
      $c->category = $category;

      UpdateCategory($connection, $c);
      //$e->init();
      $c->id = "";
      $c->groupName = "";
      $c->category = "";
      $mode = "i";
  }

  if ($mode == "u" && $btn == "del") {
    DeleteCategory($connection, $id);
    $mode = "i";
  }

  if ($mode == "s" && strlen($id)) {
    $c = getCategory($connection, $id);
    $mode = "u";
  }

?>

<a href="<?=$_SERVER['SCRIPT_NAME']?>">Reload</a>
<!-- DEBUG -->
<!--
<?=$d_today->format('Y-m-d')?><BR>
-->

<!-- Input form -->
<form action="<?PHP echo $_SERVER['SCRIPT_NAME'] ?>" method="POST">
  <input type="hidden" name="mode" value="<?=$mode?>">
  <input type="hidden" name="id" value="<?=$id?>">
  <input type="hidden" name="uid" value="<?=$_SESSION['uid']?>">
  <div class="form-group">
    <label for="InputCategory">Group Name</label>
    <input type="text" class="form-control" id="InputGroupName" placeholder="Enter Group Name" name="GroupName"  value="<?=$c->groupName?>">
  </div>
  <div class="form-group">
    <label for="InputCategory">Category</label>
    <input type="text" class="form-control" id="InputCategory" placeholder="Enter Category" name="Category"  value="<?=$c->category?>">
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
<BR>
<p><a href="kkb.php">New Entry</a></p>
<BR>
<p><a href="view.php">More Entries</a></p>
<BR>

<!-- Display table data. -->
<p>
<table class="table table-striped">
  <thead>
  <tr>
    <th>ID</th>
    <th>Group Name</th>
    <th>Category</th>
  </tr>
  </thead>
  <tbody>
<?php

$result = mysqli_query($connection, "SELECT * FROM categories ORDER BY id DESC");

while($query_data = mysqli_fetch_row($result)) {
  echo "<tr>";
  echo "<th scope=\"row\"><a href=\"?m=s&id=", $query_data[0], "\">", $query_data[0], "</a></th>",
       "<td>", $query_data[1], "</td>",
       "<td>", $query_data[2], "</td>",
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

function getCategory($connection, $id) {
  $result = mysqli_query($connection, "SELECT * FROM categories WHERE id = {$id}");
  $c = new Category();
  while ($query_data = mysqli_fetch_row($result)) {
    $c->id = $query_data[0];
    $c->groupName = $query_data[1];
    $c->category = $query_data[2];
  }
  return $c;
}

/* Delete an entry in the table. */

function DeleteCategory($connection, $id) {
   $query = "DELETE FROM categories WHERE id={$id}";
   if(!mysqli_query($connection, $query)) echo("<p>Error deleting category data.</p>");
}

/* Update an entry in the table. */
function UpdateCategory($connection, $c) {
   $g = mysqli_real_escape_string($connection, $c->groupName);
   $a = mysqli_real_escape_string($connection, $c->category);
   $i = $c->id;

   $query = "UPDATE categories SET ";
   $query .= "groupName='{$g}', category='{$a}' ";
   $query .= "WHERE id={$i}";

   if(!mysqli_query($connection, $query)) echo("<p>Error updating category data.</p>");
   //echo $query;
}

/* Add an entry to the table. */
function AddCategory($connection, $c) {
   $g = mysqli_real_escape_string($connection, $c->groupName);
   $a = mysqli_real_escape_string($connection, $c->category);

   $query = "INSERT INTO `categories` (";
   $query .= "`groupName`, `category` ";
   $query .= ") VALUES (";
   $query .= "'$g', '$a' ";
   $query .= ");";

   if(!mysqli_query($connection, $query)) echo("<p>Error adding category data.</p>");
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
