<?php

session_start();
//$_SESSION['user_id']=1;
require_once "pdo.php";
require_once "head.php";


if ( ! isset($_SESSION['name']) ) {
    die("ACCESS DENIED");
}

$failure = false;
$success = false;

if(isset($_POST['Add'])){
    if ( strlen(($_POST['first_name'])) < 1 || strlen(($_POST['last_name'])) < 1 ||strlen($_POST['email']) < 1 || strlen($_POST['headline']) < 1|| strlen($_POST['summary']) < 1 ) {
            $failure = "All fields are required";
            $_SESSION['error'] = $failure;
            header("Location: add.php");
            return;
    }

    else {
      $myvar =$_POST['email'];
      if(strpos($myvar, '@') == false) {
            $failure = "Email must have an at-sign (@)";
            $_SESSION['error'] = $failure;
            header("Location: add.php");
            return;
        }

      $msg=validatePos();
      if(is_string($msg)){
        $failure=$msg;
        $_SESSION['error'] = $failure;
        header("Location: add.php");
        return;
      }
        else{
          $msg=validateSchool();
          if(is_string($msg)){
            $failure=$msg;
            $_SESSION['error'] = $failure;
            header("Location: add.php");
            return;
           }

                if ( isset($_POST['first_name']) && isset($_POST['last_name']) && isset($_POST['email'])&& isset($_POST['headline'])&& isset($_POST['summary']))  {
                  $stmt = $pdo->prepare('INSERT INTO Profile
                        (user_id, first_name, last_name, email, headline, summary)
                        VALUES ( :uid, :fn, :ln, :em, :he, :su)');

                          $stmt->execute(array(
                                    ':uid' => $_SESSION['user_id'],
                                    ':fn' => checkDataInjection($_POST['first_name']),
                                    ':ln' => checkDataInjection($_POST['last_name']),
                                    ':em' => checkDataInjection($_POST['email']),
                                    ':he' => checkDataInjection($_POST['headline']),
                                    ':su' => checkDataInjection($_POST['summary'])));

                          $profile_id = $pdo->lastInsertId();

                          $rank=1;
                          for ($i=1;$i<=9;$i++){
                            if(!isset($_POST['year'.$i])) continue;
                            if(!isset($_POST['desc'.$i])) continue;

                            $year=$_POST['year'.$i];
                            $desc=$_POST['desc'.$i];

                            $stmt=$pdo->prepare('INSERT INTO Position (profile_id,rank,year,description) VALUES(:pid,:rank,:year,:desc)');
                            $stmt->execute(array(
                              ':pid'=>$profile_id,
                              ':rank'=>$rank,
                              ':year'=>checkDataInjection($year),
                              ':desc'=>checkDataInjection($desc))
                            );
                            $rank++;
                          }


                          $rank=1;
                          for ($i=1;$i<=9;$i++){
                            if(!isset($_POST['yearSchool'.$i])) continue;
                            if(!isset($_POST['edu_school'.$i])) continue;

                            $yearSchool=$_POST['yearSchool'.$i];
                            $edu_school=$_POST['edu_school'.$i];


                            $stmtIid=$pdo->prepare('SELECT institution_id,name FROM Institution where name = :name');
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

                              $stmtIid2=$pdo->prepare('SELECT institution_id,name FROM Institution where name = :name');
                              $stmtIid2->execute(array(
                                ':name'=>$edu_school));
                              $rowIid2 = $stmtIid2->fetchAll(PDO::FETCH_ASSOC);
                              foreach($rowIid2 as $r){
                                $institution_id=$r['institution_id'];
                              }

                            }

                            insertEducation($pdo,$profile_id,$institution_id,$rank,$yearSchool);
                            /*$stmtedu=$pdo->prepare('INSERT INTO Education (profile_id,institution_id,rank,year) VALUES(:pid,:iid,:rank,:year)');
                            $stmtedu->execute(array(
                              ':pid'=>$profile_id,
                              ':iid'=>$institution_id,
                              ':rank'=>$rank,
                              ':year'=>checkDataInjection($yearSchool)
                            ));
                            */
                            $rank++;
                          }



                    $success = 'Record added';
                    $_SESSION['success'] = $success;
                    header("Location: index.php");
                    return;

}
        }
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
<title>Yazmín 6e890e85 - Adding Profile</title>
<?php require_once "bootstrap.php"; ?>

<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">

<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap-theme.min.css" integrity="sha384-fLW2N01lMqjakBkx3l/M9EahuwpSfeNvV63J5ezn3uZzapT0u7EYsXMjQV+0En5r" crossorigin="anonymous">

<link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/ui-lightness/jquery-ui.css">

<script src="https://code.jquery.com/jquery-3.2.1.js" integrity="sha256-DZAnKJ/6XZ9si04Hgrsxu/8s717jcIzLy3oi35EouyE=" crossorigin="anonymous"></script>

<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js" integrity="sha256-T0Vest3yCU7pafRw9r+settMBX6JkKN06dqBnpQ8d30=" crossorigin="anonymous"></script>

</head>
<body>
<div class="container">
<h1>Yazmín 6e890e85 - Adding Profile for
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
<input type="text" name="first_name" size="40"></p>
<p>Last name:
<input type="text" name="last_name" size="40"></p>
<p>Email:
<input type="text" name="email" size="40"></p>
<p>Headline:<br/>
<input type="text" name="headline" size="80"/></p>
<p>Summary:<br/>
<textarea name="summary" rows="8" cols="80"></textarea>
</p>
<p>Education: <input type="submit" id="addSchool" value="+"></p>
<div id = "school_fields"> </div>
<p>Position:<input type="submit" id="addPos" value="+"></p>
<div id = "position_fields"> </div>


<input type="submit" name = "Add" value="Add">
<input type="submit" name="Logout" value="Cancel">
</form>
<script>
countPos =0;
countSchool=0;
$(document).ready(function(){
    //$('.school').autocomplete({ source: "school.php" });
    window.console && console.log('Document ready called');
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
    //    $('.school').autocomplete({ source: "school.php" });
      event.preventDefault();

      if(countSchool>=9){
        alert("Maximum of nine schools entries exceeded");
        return;
      }
      countSchool++;
      window.console && console.log("Adding school"+countSchool);

      $('#school_fields').append('<div id="school'+countSchool+'"> <p>Year:<input type="text" name ="yearSchool'+countSchool+'" value="" /> <input type = "button" value="-" onclick="$(\'#school'+countSchool+'\').remove();return false;"></p> <p>School: <input type="text" size="80" name="edu_school'+countSchool+'" class="school" value="" /></p> </div>');
     $('.school').autocomplete({ source: "school.php" });
    });

$('.school').autocomplete({ source: "school.php" });
  });
$('.school').autocomplete({ source: "school.php" });

</script>

</div>
</body>
</html>
