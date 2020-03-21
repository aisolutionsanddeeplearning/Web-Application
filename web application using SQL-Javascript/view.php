<?php
session_start();
require_once "pdo.php";
require_once "head.php";
if ( ! isset($_SESSION['name']) ) {
    die('Not logged in');
}
$query1 = "SELECT * FROM Profile WHERE profile_id =".$_REQUEST['profile_id'];
$stmt1 = $pdo->query($query1);
$rows1 = $stmt1->fetchAll(PDO::FETCH_ASSOC);

$query = "SELECT * FROM Position WHERE profile_id =".$_REQUEST['profile_id'];
$stmt = $pdo->query($query);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$query2 = "SELECT * FROM Education WHERE profile_id =".$_REQUEST['profile_id'];
$stmt2 = $pdo->query($query2);
$rows2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);

$institutions=array();
$query22 = "SELECT * FROM Education WHERE profile_id =".$_REQUEST['profile_id'];
$stmt22 = $pdo->query($query22);
$rows22 = $stmt22->fetchAll(PDO::FETCH_ASSOC);


foreach($rows22 as $row){
    $institutions[]=$row['institution_id'];
  }

$institutionsname=array();
foreach($institutions as $inst){
  $query3 = "SELECT name FROM Institution WHERE institution_id =".$inst;
  $stmt3 = $pdo->query($query3);
  $rows3 = $stmt3->fetchAll(PDO::FETCH_ASSOC);
  foreach($rows3 as $row){
    $institutionsname[]=$row['name'];
  }
}


// If the user requested logout go back to index.php
if ( isset($_POST['Logout']) ) {
    header('Location: index.php');
    return;
}
?>



<!DOCTYPE html>
<html>
<head>
<title>Yazmín 6e890e85 - Position Database</title>
<?php require_once "bootstrap.php"; ?>
</head>
<body>
<div class="container">
<h1>Yazmín 6e890e85 - Positions for
    <?php if ( isset($_SESSION['name']) )
    {
        echo htmlentities($_SESSION['name']);
}?>
</h1>
<?php
if (isset($_SESSION['success'])){
    // Look closely at the use of single and double quotes
    echo('<p style="color: green;">'.$_SESSION['success']."</p>\n");
}

?>
<h1>Profile</h1>
<?php
    foreach($rows1 as $row){
      echo("<ul> <li>"."<b>Profile_id: </b>".$row["profile_id"]."<li> <b>First name:</b> ".$row["first_name"]."<li> <b>Last Name:</b> ".$row['last_name']."<li><b>Email:</b> ".$row['email']."<li><b>Headline: </b>".$row['headline']."<li><b>Summary: </b>".$row['summary']."</ul>\n");
    }
?>

<h1>Positions</h1>
<?php
    foreach($rows as $row){
      echo("<ul> "."<b> <li> Year: </b> ".$row["year"]."<li> <b>Description:</b> ".$row['description']."</ul>\n");
    }
?>
<h1>Education</h1>
<?php
     $i=0;
    foreach($rows2 as $row){
      echo("<ul>"."<b><li> Year:</b> ".$row["year"]." <li><b>Institution:</b> ".$institutionsname[$i]."</ul>\n");
      $i++;
    }

?>
<p> <a href="add.php">Add New</a> | <a href="logout.php">Logout</a></p>
</div>
</body>
</html>
