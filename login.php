<?php
include "../../inc/kkb_dbinfo.inc";
session_start();
if (!isset($_SESSION['loggedIn'])) {
  $_SESSION['loggedIn'] = 0;
}

if ($_SESSION['loggedIn'] == 1) {
  header("Location: http://" . $_SERVER['SERVER_NAME']."/kkb/index.php");
  //echo "Logged in ";
  //echo $_SESSION['loggedIn'];
  die();
}

  /* Connect to MySQL and select the database. */
  $connection = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD);
  if (mysqli_connect_errno()) echo "Failed to connect to MySQL: " . mysqli_connect_error();
  $database = mysqli_select_db($connection, DB_DATABASE);

  /* Ensure that the KKB_Entry table exists. */
  VerifyTable($connection, "users", DB_DATABASE);

  /* If input fields are populated, add a row to the Employees table. */
  $mode = htmlentities($_POST['mode']);
  $username = htmlentities($_POST['Username']);
  $password = htmlentities($_POST['Password']);
  $error = false;
  $msg = "";

  if ($mode == "login" && strlen($username) && strlen($password)) {
    //AddEntry($connection, $item, $amount, $date);
    $uid = VerifyLogin($connection, $username, $password);
    if ($uid > 0) {
      $_SESSION['loggedIn'] = 1;
      $_SESSION['uid'] = $uid;
      header("Location: http://".$_SERVER['SERVER_NAME']."/kkb/index.php");
      die();
    } else {
      $_SESSION['loggedIn'] = 0;
      $_SESSION['uid'] = 0;
      $error = true;
      $msg = "Invalid Login";
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
  <title>KKB Login</title>
  <script src="jquery-3.2.1.js"></script>
</head>
<body>
<h1>KKB Login</h1>
<!-- DEBUG -->
<!--
<?=$_SESSION['loggedIn']?><BR>
<?=$username?><BR>
<?=$password?><BR>
<a href="<?=$_SERVER['SCRIPT_NAME']?>">Reload</a>
-->

<?php if ($error) {?>
  <p><font color="red"><?=$msg?></font></p>
<?php }?>

<!-- Input form -->
<form action="<?PHP echo $_SERVER['SCRIPT_NAME'] ?>" method="POST">
  <input type="hidden" name="mode" value="login">
  <div class="form-group">
    <label for="InputUsername">Username</label>
    <input type="text" class="form-control" id="InputUsername" placeholder="Enter Username" name="Username" maxlength="20" size="10" value=""/>
  </div>
  <div class="form-group">
    <label for="InputPassword">Password</label>
    <input type="password" class="form-control" id="InputPassword" placeholder="Password" name="Password" maxlength="10" size="10" value=""/>
  </div>
    <button type="submit" class="btn btn-primary" name="btn" value="login">Login</button>
</form>

<!-- Clean up. -->
<?php

  mysqli_free_result($result);
  mysqli_close($connection);

?>
</body>
</html>

<?php
/* Check whether username and password match */
function VerifyLogin($connection, $username, $password) {
  $result = mysqli_query($connection, "SELECT * FROM users WHERE username = '{$username}'");
  while ($query_data = mysqli_fetch_row($result)) {
    if ($password == $query_data[2]) {
      return $query_data[0];
    }
  }
  return 0;
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
