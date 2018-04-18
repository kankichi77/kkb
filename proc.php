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
   $last_id = mysqli_insert_id($connection);
   return $last_id;
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
