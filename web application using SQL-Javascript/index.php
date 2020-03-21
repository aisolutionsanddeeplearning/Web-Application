<?php
  require_once "pdo.php";
  require_once "head.php";
  session_start();
 ?>
<!DOCTYPE html>
<html>
<head>
<title>Yazmín's Resume Registry--"Yazmín 6e890e85" </title>
<?php require_once "bootstrap.php"; ?>
</head>
<body>

<div class="container">
<h1>Welcome to Yazmín's Resume Registry "Yazmín 6e890e85"</h1>


  <?php
  if ( isset($_SESSION['error']) ) {
    echo ('<p style="color:red">'.$_SESSION['error']."</p>\n");
    unset($_SESSION['error']);
}
if ( isset($_SESSION['success']) ) {
    echo ('<p style="color:green">'.$_SESSION['success']."</p>\n");
    unset($_SESSION['success']);
}
  if ( isset($_SESSION['name']) ) {
    $stmtcheck = $pdo->query("SELECT profile_id, first_name, last_name, headline FROM Profile");
    $rowcheck = $stmtcheck->fetch(PDO::FETCH_ASSOC) ;
    if($rowcheck){
    echo('<table border="1">'."\n");
    echo "<tr><td> <b>Name</b>";
    echo("</td><td> <b>Headline</b>");
    echo("</td><td> <b>Action</b>");
    echo("</td><td>");
    echo ("<tr><td>");
    $stmt = $pdo->query("SELECT profile_id, first_name, last_name, headline, user_id FROM Profile");
    while ( $row = $stmt->fetch(PDO::FETCH_ASSOC) )  {
        echo "<tr><td>";
        echo(htmlentities($row['first_name'].' '.$row['last_name']));
        echo("</td><td>");
        echo(htmlentities($row['headline']));
        echo("</td><td>");

        if ($row['user_id'] == $_SESSION['user_id']){
          echo('<a href="edit.php?profile_id='.$row['profile_id'].'">Edit</a> / ');
          echo('<a href="delete.php?profile_id='.$row['profile_id'].'">Delete</a>');
        }
        echo("</td></tr>\n");
      }
  echo("</table>");
}
else{echo('<p>No rows found</p>');}
  echo('<p><a href="add.php">Add New Entry</a></p>'."\n");
  echo('<p><a href="logout.php">Logout</a></p>'."\n");
}

else{

echo('
<p>
<a href="login.php">Please log in</a>
</p>
');

$stmtcheck2 = $pdo->query("SELECT profile_id, first_name, last_name, headline FROM Profile");
$rowcheck2 = $stmtcheck2->fetch(PDO::FETCH_ASSOC) ;
if($rowcheck2){
echo('<table border="1">'."\n");
echo "<tr><td> <b>Name</b>";
echo("</td><td> <b>Headline</b>");
echo("</td><td>");
$stmt2 = $pdo->query("SELECT profile_id, first_name, last_name, headline FROM Profile");
while ( $row2 = $stmt2->fetch(PDO::FETCH_ASSOC) )  {
    echo "<tr><td>";
    echo(htmlentities($row2['first_name'].' '.$row2['last_name']));
    echo("</td><td>");
    echo(htmlentities($row2['headline']));
    echo("</td><td>");
  }
echo("</table>");
}


echo('
<p>
	<b>Note:</b> Your implementation should retain data across multiple logout/login sessions. This sample implementation clears all its data on logout - which you should not do in your implementation.
	</p>
</div>
');}//end else echo
?>

</body>
