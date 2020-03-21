<?php
session_start();
require_once "pdo.php";
require_once "head.php";
if ( ! isset($_SESSION['name']) ) {
    die("ACCESS DENIED");
}

$failure = false;

$stmt = $pdo->prepare("SELECT * FROM Profile where profile_id = :xyz");
$stmt->execute(array(":xyz" => $_GET['profile_id']));
$row = $stmt->fetch(PDO::FETCH_ASSOC);

$stmt2 = $pdo->prepare("SELECT * FROM Position where profile_id = :xyz");
$stmt2->execute(array(":xyz" => $_GET['profile_id']));
$row2 = $stmt2->fetch(PDO::FETCH_ASSOC);
$total = $stmt2->rowcount();

$stmt3 = $pdo->prepare("SELECT * FROM Education where profile_id = :xyz");
$stmt3->execute(array(":xyz" => $_GET['profile_id']));
$totalSchool = $stmt3->rowcount();


//echo ($total);


if ( $row === false ) {
    $_SESSION['error'] = 'Bad value for profile_id';
    header( 'Location: index.php' ) ;
    return;
}
else{

if ($row['user_id'] == $_SESSION['user_id']){

if(isset($_POST['Save'])){
  if ( strlen($_POST['first_name']) < 1 || strlen($_POST['last_name']) < 1 ||strlen($_POST['email']) < 1 ||
       strlen($_POST['headline']) < 1|| strlen($_POST['summary']) < 1 ) {
            $failure = "All fields are required";
            $_SESSION['error'] = $failure;
            header("Location: edit.php?profile_id=".$_REQUEST['profile_id']);
            return;
    }

    else {
      $myvar =$_POST['email'];
      if(!preg_match("[@]",$myvar)) {
            $failure = "Email must have an at-sign (@)";
            $_SESSION['error'] = $failure;
            header("Location: edit.php?profile_id=".$_REQUEST['profile_id']);
            return;
        }
        else{

          $msg=validatePos();
          if(is_string($msg)){
            $failure=$msg;
            $_SESSION['error'] = $failure;
            header("Location: edit.php?profile_id=".$_REQUEST['profile_id']);
            return;
          }
            else{
              $msg=validateSchool();
              if(is_string($msg)){
                $failure=$msg;
                $_SESSION['error'] = $failure;
                header("Location: edit.php?profile_id=".$_REQUEST['profile_id']);
                return;
               }


          if ( isset($_POST['first_name']) && isset($_POST['last_name']) && isset($_POST['email'])&& isset($_POST['headline'])&& isset($_POST['summary']))  {
                                   $sql = "UPDATE Profile SET first_name= :first_name,
                                     last_name = :last_name, email = :email, headline = :headline, summary = :summary
                                   WHERE profile_id = :profile_id";
                                   echo("<pre>\n".$sql."\n</pre>\n");
                                   $stmt = $pdo->prepare($sql);
                                   $stmt->execute(array(
                                                ':first_name' =>  checkDataInjection($_POST['first_name']),
                                                ':last_name' =>  checkDataInjection($_POST['last_name']),
                                                 ':email' => checkDataInjection($_POST['email']),
                                                 ':headline' => checkDataInjection($_POST['headline']),
                                                 ':summary' => checkDataInjection($_POST['summary']),
                                                 ':profile_id' => $_POST['profile_id']));


                                  // Clear out the old position entries
                                  $stmt = $pdo->prepare('DELETE FROM Position WHERE profile_id=:pid');
                                  $stmt->execute(array( ':pid' => $_REQUEST['profile_id']));

                                  $stmt2 = $pdo->prepare('DELETE FROM Education WHERE profile_id=:pid');
                                  $stmt2->execute(array( ':pid' => $_REQUEST['profile_id']));

                                  // Insert the position entries

                                  $rank = 1;
                                  for($i=1; $i<=9; $i++) {
                                    if ( ! isset($_POST['year'.$i]) ) continue;
                                    if ( ! isset($_POST['desc'.$i]) ) continue;
                                        $year = $_POST['year'.$i];
                                        $desc = $_POST['desc'.$i];
                                        $stmt = $pdo->prepare('INSERT INTO Position
                                          (profile_id, rank, year, description)
                                          VALUES ( :pid, :rank, :year, :desc)');
                                          $stmt->execute(array(
                                            ':pid' => $_REQUEST['profile_id'],
                                            ':rank' => $rank,
                                            ':year' => $year,
                                            ':desc' => $desc)
                                          );
                                          $rank++;
                                        }
                                        // Insert the education entries
                                        $rank=1;
                                        for ($i=1;$i<=9;$i++){
                                          if(!isset($_POST['yearSchool'.$i])) continue;
                                          if(!isset($_POST['edu_school'.$i])) continue;

                                          $yearSchool=$_POST['yearSchool'.$i];
                                          $edu_school=$_POST['edu_school'.$i];


                                          $stmtIid=$pdo->prepare('SELECT * FROM Institution where name = :name');
                                          $stmtIid->execute(array(
                                            ':name'=>$edu_school));

                                          $rowIid = $stmtIid->fetchAll(PDO::FETCH_ASSOC);
                                          foreach($rowIid as $r){
                                            $institution_id = $r['institution_id'];

                                            }
                                        if ($stmtIid->rowcount() == 0) {
                                          $stmt=$pdo->prepare('INSERT INTO Institution (name) VALUES(:name)');
                                          $stmt->execute(array(
                                            ':name'=>$edu_school));

                                            $stmtIid2=$pdo->prepare('SELECT * FROM Institution where name = :name');
                                            $stmtIid2->execute(array(
                                              ':name'=>$edu_school));
                                            $rowIid2 = $stmtIid2->fetchAll(PDO::FETCH_ASSOC);
                                            foreach($rowIid2 as $r){
                                              $institution_id=$r['institution_id'];
                                            }
                                          }
                                          $stmtedu=$pdo->prepare('INSERT INTO Education (profile_id,institution_id,rank,year) VALUES(:pid,:iid,:rank,:year)');
                                          $stmtedu->execute(array(
                                            ':pid'=>$_REQUEST['profile_id'],
                                            ':iid'=>$institution_id,
                                            ':rank'=>$rank,
                                            ':year'=>$yearSchool
                                          ));
                                          $rank++;
                                        }


                                   $_SESSION['success'] = 'Profile updated';
                                   header( 'Location: index.php' ) ;
                                  return;
}
        }
    }
}
}
}
}

// Flash pattern
if ( isset($_SESSION['error']) ) {
    echo '<p style="color:red">'.$_SESSION['error']."</p>\n";
    unset($_SESSION['error']);
}

$first_name = htmlentities($row['first_name']);
$last_name = htmlentities($row['last_name']);
$email = htmlentities($row['email']);
$headline = htmlentities($row['headline']);
$summary = htmlentities($row['summary']);
$profile_id =  htmlentities($row['profile_id']);

$year=array();
$desc=array();

$stmt3 = $pdo->prepare("SELECT * FROM Position where profile_id = :xyz");
$stmt3->execute(array(":xyz" => $_GET['profile_id']));
while($row=$stmt3->fetch(PDO::FETCH_ASSOC)) {
    $year[]= $row['year'];
    $desc[]= $row['description'];
  //  echo('year'.$row['year']);
  //  echo('des'.$row['description']);
}

$stmt4 = $pdo->prepare("SELECT * FROM Education where profile_id = :xyz");
$stmt4->execute(array(":xyz" => $_GET['profile_id']));
while($row=$stmt4->fetch(PDO::FETCH_ASSOC)) {
    $yearSchool[]= $row['year'];
    $iid[]= $row['institution_id'];
  //  echo('year'.$row['year']);
  //  echo('des'.$row['description']);
}
$nameSchool=array();
foreach($iid as $inst_id){
  $stmt5 = $pdo->prepare("SELECT * FROM Institution where institution_id = :xyz");
  $stmt5->execute(array(":xyz" => $inst_id));
  while($row=$stmt5->fetch(PDO::FETCH_ASSOC)) {
    $nameSchool[]= $row['name'];
  }
}


// If the user requested logout go back to index.php
if ( isset($_POST['Logout']) ) {
    header('Location: index.php');
    return;
}

function checkDataInjection($data){
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }
    function validatePos() {
      for($i=1; $i<=9; $i++) {
        if ( ! isset($_POST['year'.$i]) ) continue;
        if ( ! isset($_POST['desc'.$i]) ) continue;

        $year = $_POST['year'.$i];
        $desc = $_POST['desc'.$i];

        if ( strlen($year) == 0 || strlen($desc) == 0  ) {
          return "All fields are required";
        }

        if  (! is_numeric($year))   {
          return "Year must be numeric";
        }
      }
      return true;
    }
    function validateSchool() {
      for($i=1; $i<=9; $i++) {

        if ( ! isset($_POST['yearSchool'.$i]) ) continue;
        if ( ! isset($_POST['edu_school'.$i]) ) continue;


        $yearSchool = $_POST['yearSchool'.$i];
        $edu_school = $_POST['edu_school'.$i];

        if (strlen($yearSchool) == 0 || strlen($edu_school) == 0 ) {
          return "All fields are required";
        }

        if  (! is_numeric($yearSchool))  {
          return "Year must be numeric";
        }
      }
      return true;
    }
?>



<!DOCTYPE html>
<html>
<head>
<title>Editing Profile</title>
<?php require_once "bootstrap.php"; ?>
</head>
<body>
<div class="container">
<h1>Editing Profile for
    <?php if ( isset($_REQUEST['name']) )
    {
        echo htmlentities($_REQUEST['name']);
}?>
</h1>

<?php

if (isset($_SESSION['error'])){
    // Look closely at the use of single and double quotes
    echo('<p style="color: red;">'.htmlentities($_SESSION['error'])."</p>\n");
    unset($_SESSION['error']);
}


?>


<form method="post">
<p>First name:
<input type="text" name="first_name" size="40" value="<?= $first_name ?>"></p>
<p>Last name:
<input type="text" name="last_name" size="40" value="<?= $last_name ?>"></p>
<p>Email:
<input type="text" name="email" size="40" value="<?= $email ?>"></p>
<p>Headline:<br/>
<input type="text" name="headline" value="<?= $headline ?>" size="80"/></p>
<p>Summary:<br/>
<input type="text " name="summary" value="<?= $summary ?>" size="80"/></p>
</p>
<p>Education: <input type="submit" id="addSchool" value="+"></p>
<div id = "school_fields"> </div>
<p>Position:<input type="submit" id="addPos" value="+"></p>
<div id = "position_fields"> </div>

<input type="hidden" name="profile_id" value="<?= $profile_id ?>">

<input type="submit" name = "Save" value="Save">
<input type="submit" name="Logout" value="Cancel">
</form>

<script>
countPos =0;
countSchool=0;
$(document).ready(function(){
    window.console && console.log('Document ready called');
    var totalPos = <?php echo $total ?>;
    var js_year = <?php echo json_encode($year );?>;
    var js_desc = <?php echo json_encode($desc );?>;

    var totalSchool = <?php echo $totalSchool ?>;
    var js_yearSchool = <?php echo json_encode($yearSchool );?>;
    var js_nameSchool = <?php echo json_encode($nameSchool );?>;

    for(var i=0;i<totalPos;i++) {
        countPos++;
        window.console && console.log("Adding position"+countPos);
          $('#position_fields').append('<div id="position'+countPos+'"> <p>Year:<input type="text" name ="year'+countPos+'" value=" '+js_year[i]+'" /> <input type = "button" value="-" onclick="$(\'#position'+countPos+'\').remove();return false;"></p> <textarea name ="desc'+countPos+'" rows="8" cols="80">  '+js_desc[i]+'</textarea> </div>');
          if (countPos>=9){return;}
    }

    for(var i=0;i<totalSchool;i++) {
        countSchool++;
        window.console && console.log("Adding school"+countSchool);

        $('#school_fields').append('<div id="school'+countSchool+'"> <p>Year:<input type="text" name ="yearSchool'+countSchool+'" value=" '+js_yearSchool[i]+'"  /> <input type = "button" value="-" onclick="$(\'#school'+countSchool+'\').remove();return false;"></p> <p>School: <input type="text" size="80" name="edu_school'+countSchool+'" class="school" value=" '+js_nameSchool[i]+'"  /></p> </div>');
      //  $('.school').autocomplete({ source: "school.php" });
       if (countSchool>=9){return;}
    }

    $('#addPos').click(function(event){
      event.preventDefault();
      if(countPos>=9){
        alert("Maximum of nine positions entries exceeded");
        return;
      }
      countPos++;
      window.console && console.log("Adding position"+countPos);
      $('#position_fields').append('<div id="position'+countPos+'"> <p>Year:<input type="text" name ="year'+countPos+'" value="" /> <input type = "button" value="-" onclick="$(\'#position'+countPos+'\').remove();return false;"></p> <textarea name ="desc'+countPos+'" rows="8" cols="80"></textarea> </div>');
    });
    $('#addSchool').click(function(event){
      event.preventDefault();

      if(countSchool>=9){
        alert("Maximum of nine schools entries exceeded");
        return;
      }
      countSchool++;
      window.console && console.log("Adding school"+countSchool);

      $('#school_fields').append('<div id="school'+countSchool+'"> <p>Year:<input type="text" name ="yearSchool'+countSchool+'" value="" /> <input type = "button" value="-" onclick="$(\'#school'+countSchool+'\').remove();return false;"></p> <p>School: <input type="text" size="80" name="edu_school'+countSchool+'" class="school" value="" /></p> </div>');
    //  $('.school').autocomplete({ source: "school.php" });
    });
//$('.school').autocomplete({ source: "school.php" });

  });



</script>

</div>
</body>
</html>
