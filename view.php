<?php 
include "../../inc/kkb_dbinfo.inc";
session_start();

if ($_SESSION['loggedIn'] == 0) {
  header("Location: http://".$_SERVER['SERVER_NAME']."/kkb/login.php");
  die();
}

if ($_GET['m'] == 'lo') {
  $_SESSION['loggedIn'] = 0;
  header("Location: http://".$_SERVER['SERVER_NAME']."/kkb/login.php");
  die();
}
?>
<!DOCTYPE html>
<html lang="jp">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="stylesheet" 
    href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/css/bootstrap.min.css">
  <title>KKB View</title>
  <script src="jquery-3.2.1.js"></script>
  <link rel="stylesheet" href="jquery-ui.min.css">
  <script src="jquery-ui.min.js"></script>
</head>
<body>
<h1>KKB View</h1>
<?php
  /* Connect to MySQL and select the database. */
  $connection = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD);
  if (mysqli_connect_errno()) echo "Failed to connect to MySQL: " . mysqli_connect_error();
  $database = mysqli_select_db($connection, DB_DATABASE);

  /* Ensure that the KKB_Entry table exists. */
  VerifyTable($connection, "kkb_entry", DB_DATABASE); 
?>

<a href="<?=$_SERVER['SCRIPT_NAME']?>">Reload</a>
<BR>
<a href="kkb.php">Create New</a>
<!-- DEBUG -->
<!--
ID: <?=$e->id?>
<BR>
-->

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
    <th>Created By</th>
    <th>Created On</th>
    <th>Last Updated By</th>
    <th>Last Updated On</th>
  </tr>
  </thead>
  <tbody>
<?php

$result = mysqli_query($connection, 
"SELECT kkb_entry.*, u1.username, u2.username FROM kkb_entry, users u1, users u2 WHERE kkb_entry.created_by = u1.id AND kkb_entry.lastUpdated_by = u2.id ORDER BY id DESC LIMIT 100"); 

while($query_data = mysqli_fetch_row($result)) {
  echo "<tr>";
  echo "<th scope=\"row\"><a href=\"kkb.php?m=s&id=", $query_data[0], "\">", $query_data[0], "</a></th>",
       "<td>", $query_data[1], "</td>",
       "<td>", $query_data[3], "</td>",
       "<td>", $query_data[4], "</td>",
       "<td>", $query_data[2], "</td>",
       "<td>", $query_data[10], "</td>",  // Method
       "<td>", $query_data[7], "</td>",  // Other Party
       "<td>", $query_data[11], "</td>", // Created By (Username)
       "<td>", $query_data[6], "</td>",
       "<td>", $query_data[12], "</td>", // Last Updated By
       "<td>", $query_data[8], "</td>", // Last Update On
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

function VerifyTable($connection, $tableName, $dbName) {
}

function TableExists($tableName, $connection, $dbName) {
}

?>
