<?php
function insertEducation($pdo,$profile_id,$institution_id,$rank,$yearSchool){
  $stmtedu=$pdo->prepare('INSERT INTO Education (profile_id,institution_id,rank,year) VALUES(:pid,:iid,:rank,:year)');
  $stmtedu->execute(array(
    ':pid'=>$profile_id,
    ':iid'=>$institution_id,
    ':rank'=>$rank,
    ':year'=>checkDataInjection($yearSchool)
  ));
  return TRUE;
}
 ?>
