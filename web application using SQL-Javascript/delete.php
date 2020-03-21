<?php
require_once "pdo.php";
require_once "head.php";
session_start();
if ( ! isset($_SESSION['name']) ) {
    die("ACCESS DENIED");
}

if ( isset($_POST['delete']) && isset($_POST['profile_id']) ) {
    $sql = "DELETE FROM Profile WHERE profile_id = :zip";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array(':zip' => $_POST['profile_id']));
    $_SESSION['success'] = 'Profile deleted';
    header( 'Location: index.php' ) ;
    return;
}

// Guardian: Make sure that autos_id is present
if ( ! isset($_GET['profile_id']) ) {
  $_SESSION['error'] = "Missing profile_id";
  header('Location: index.php');
  return;
}

$stmt = $pdo->prepare("SELECT * FROM Profile where profile_id = :xyz");
$stmt->execute(array(":xyz" => $_GET['profile_id']));
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if ( $row === false ) {
    $_SESSION['error'] = 'Bad value for profile_id';
    header( 'Location: index.php' ) ;
    return;
}

if ( isset($_POST['Logout'] ) ) {
    // Redirect the browser to game.php
    header("Location: index.php");
    return;
}

?>


<h1>Deleting Profile: </h1>
<p>First Name: <?= htmlentities($row['first_name']) ?></p>
<p>Last Name:  <?= htmlentities($row['last_name']) ?></p>
<form method="post">
<input type="hidden" name="profile_id" value="<?= $row['profile_id'] ?>">

<input type="submit" value="Delete" name="delete">
<input type="submit" value="Cancel" name="Logout">

</form>
