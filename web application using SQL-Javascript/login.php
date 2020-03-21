<?php // Do not put any HTML above this line
session_start();
require_once "pdo.php";
require_once "head.php";
if ( isset($_POST['Logout'] ) ) {
    // Redirect the browser to game.php
    header("Location: index.php");
    return;
}

$salt = 'XyZzy12*_';
$stored_hash = '1a52e17fa899cf40fb04cfc42e6352f1';  // Pw is php123

$failure = false;  // If we have no POST data


if ( isset($_POST['email']) && isset($_POST['pass']) ) {
    unset($_SESSION['name']);
    $myvar = $_POST['email'];

    if ( strlen($_POST['email']) < 1 & strlen($_POST['pass']) < 1 ) {
        $failure = "User name and password are required";
        $_SESSION['error'] = "User name and password are required";
        header("Location: login.php");
        return;
    }
        else {
                if(!preg_match("[@]",$myvar)){
            $failure='Email must have an at-sign (@)';
            $_SESSION['error'] = "Email must have an at-sign (@)";
            header("Location: login.php");
            return;
          }
          else{
        $check = hash('md5', $salt.$_POST['pass']);
        $stmt = $pdo->prepare('SELECT user_id, name FROM users WHERE email = :em AND password = :pw');
        $stmt->execute(array( ':em' => $_POST['email'], ':pw' => $check));
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

      //  if ( $row ) {
            if ( $row !== false ) {
              $_SESSION['name'] = $row['name'];
              $_SESSION['user_id'] = $row['user_id'];
              // Redirect the browser to index.php
              header("Location: index.php");
              return;
        } else {
            $failure = "Incorrect password";
            $_SESSION['error'] = "Incorrect password";
            error_log("Login fail ".$_POST['email']." $check");
            header("Location: login.php");
            return;
        //}
}
}
}
if ($failure==false){error_log("Login success ".$_POST['email']);}

}
// Fall through into the View
?>



<!DOCTYPE html>
<html>
<head>
<?php require_once "bootstrap.php"; ?>
<title>Yazmín 6e890e85 Login Page</title>
</head>
<body>
<div class="container">
<h1>Yazmín 6e890e85- Please Log In</h1>
<?php
// Note triple not equals and think how badly double
// not equals would work here...

if ( isset($_SESSION['error']) ) {
    echo('<p style="color: red;">'.htmlentities($_SESSION['error'])."</p>\n");
    unset($_SESSION['error']);

}
?>


<form method="POST">
<label for="nam">User Name (email)</label>
<input type="text" name="email" id="nam"><br/>
<label for="id_1723">Password</label>
<input type="password" name="pass" id="id_1723"><br/>
<input type="submit" onclick="return doValidate();" value="Log In">
<input type="submit" name="Logout" value="Cancel">
</form>
<script>
function doValidate() {
          console.log("Validating...");
              try {
                    pw = document.getElementById("id_1723").value;
                    e = document.getElementById("nam").value;
                    console.log("Validating pw="+pw);
                          if (pw == null || pw == "") {
                                  alert("Both fields must be filled out");
                                  return false;
                                }
                    console.log("Validating e="+e);
                          if(!e.includes('@')){
                                    alert("Invalild email address");
                                    return false;
                          }
                          return true;
              } catch(e) {
                          return false;
                        }
              return false;
}
</script>


<p>
For a password hint, view source and find a password hint
in the HTML comments.
<!-- Hint: The password is the programming language that we are using in this app followed by 123.
 -->
</p>
</div>
</body>
</html>
