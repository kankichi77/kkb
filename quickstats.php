<?php
// INCLUDE FILES
include "../../inc/kkb_dbinfo.inc";  // DB SETTINGS
include "proc.php";  // Processing functions

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
$date = "";
?>
<!DOCTYPE html>
<html lang="jp">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="stylesheet"
    href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/css/bootstrap.min.css">
  <title>KKB Stats</title>
  <script src="jquery-3.2.1.js"></script>
  <link rel="stylesheet" href="jquery-ui.min.css">
  <script src="jquery-ui.min.js"></script>

  <link rel="stylesheet" href="kkb.css">
</head>
<body>
<div class="container">

<a href="<?=$_SERVER['SCRIPT_NAME']?>"><h1>KKB Stats</h1></a>

<a href="index.php" class="btn btn-outline-secondary btn-sm" role="button">Create New</a>
<a href="categories.php" class="btn btn-outline-secondary btn-sm" role="button">Categories</a>
<a href="view.php" class="btn btn-outline-secondary btn-sm" role="button">More Entries</a>
<?php
  /* Connect to MySQL and select the database. */
  $connection = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD);
  if (mysqli_connect_errno()) echo "Failed to connect to MySQL: " . mysqli_connect_error();
  $database = mysqli_select_db($connection, DB_DATABASE);

  /* Ensure that the KKB_Entry table exists. */
  //VerifyTable($connection, "kkb_entry", DB_DATABASE);

  $mode = htmlentities($_POST['mode']);

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

  $y = htmlentities($_GET['y']);
  $m = htmlentities($_GET['mo']);
  if ($mode != "v" || $y == '' || $m == '') {
      $mode = "";
      $y = date('Y');
      $m = date('m');
  }
  try {
    $d_month_start = new DateTime($y . "-" . $m . "-" . '1');
    $d_next_month = new DateTime($y . "-" . $m . "-" . '1');
    $d_prev_month = new DateTime($y . "-" . $m . "-" . '1');

    $d_next_month->add(new DateInterval('P1M'));
    $d_prev_month->sub(new DateInterval('P1M'));
  } catch (Exception $er) {
      echo $er->getMessage();
      exit(1);
  }
?>
<!-- DEBUG -->
<!--
<?=$mode?><BR>
<?=$y?><BR>
<?=$m?>
-->

<!-- Input form -->
<form action="<?PHP echo $_SERVER['SCRIPT_NAME'] ?>" method="POST">
  <input type="hidden" name="mode" value="<?=$mode?>">
  <input type="hidden" name="id" value="<?=$id?>">
  <input type="hidden" name="uid" value="<?=$_SESSION['uid']?>">

<!-- Quick Stats -->
<div class="quickStats">
<p class="quickStats-title">
  <a href="quickstats.php?m=v&y=<?=$d_prev_month->format('Y')?>&mo=<?=$d_prev_month->format('m')?>"><-</a>
  <?=$y?>年<?=$m?>月データ
  <a href="quickstats.php?m=v&y=<?=$d_next_month->format('Y')?>&mo=<?=$d_next_month->format('m')?>">-></a>
</p>
<p>
<table class="table table-striped">
  <thead>
  <tr>
    <th>Group</th>
    <th>Amount</th>
  </tr>
  </thead>
  <tbody>
<?php
$query = "SELECT groupname, sum(amount) total FROM kkb_entry ";
$query .= "LEFT JOIN categories ON kkb_entry.category = categories.category ";
$query .= "WHERE kkb_entry.date >= '";
$query .= $d_month_start->format('Y-m-d');
$query .= "' AND kkb_entry.date < '";
$query .= $d_next_month->format('Y-m-d');
$query .= "' GROUP BY categories.groupname ORDER BY total DESC";

$result = mysqli_query($connection, $query);
$total = 0;

while($query_data = mysqli_fetch_row($result)) {
  $total += $query_data[1];
  if ($query_data[0] == "") $x = "(BLANK)";
    else $x = $query_data[0];
  echo "<tr>";
  echo "<th scope=\"row\"><a href=\"view.php?m=s&k=", $query_data[0], "\">", $x, "</a></th>",
       "<td>¥ ", number_format($query_data[1]), "</td>",
       "</tr>";
}
?>
</tbody>
</table>
Total: ¥ <?=number_format($total)?>
</p><?=$query?>
</div>
<!-- Stats End -->

<!-- Clean up. -->
<?php

  mysqli_free_result($result);
  mysqli_close($connection);

?>

<a href="<?=$_SERVER['SCRIPT_NAME']?>?m=lo">Log out</a>

</div>
</body>
</html>
