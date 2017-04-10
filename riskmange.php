<?php
include_once 'config.php';
session_start();

if(!$_SESSION['adminid'])
{
    ?><script type="text/javascript">window.location.assign("index.php")</script>
    <?php
}
/*echo "<pre>";
print_r($_POST);
exit;*/
define('NON_ACTIVE', 0);
  $i = 0;
  $k = 1; // this is for keep track of j and loop from next level
  $l = 2; //for action officer
  $m = 0; // For other injuries
  $revisionDate = null;
  $creationDates = new DateTime($_POST['creationDate']);
  $creationDate = date_format($creationDates, 'Y-m-d H:i:s'); // 2011-03-03 00:00:00
  if (isset($_POST["revisionDate"]))
      $revisionDate = date_format(new DateTime($_POST['revisionDate']), 'Y-m-d H:i:s');

  $today = date('Y-m-d H:i:s');

  if($_POST['saveAsDraft'] == 'Save as Draft')
  {
    $status = 1;
  }
  else if($_POST['saveAsDraft'] == 'Next')
  {
      $status = $revisionDate?2:0;
  }
  else
  {
    $status = 0;
  }

 if(isset($_GET['riskid']) && $_GET['riskid'] != '')

  {
      $set_clause = "";
      if($revisionDate){
        $set_clause = $set_clause."`revisionDate` =  '".$revisionDate."' ,`approveDate` =  '".$revisionDate."' ,";
      }
      $riskassessment = "UPDATE  `riskassessment` SET  " .$set_clause. "`createdDate` =  '".$creationDate."' ,
      `location` =  '".$_POST['location']."',`process` =  '".$_POST['process']."',
      `expiry_date` =  '".$_POST['expiry_date']."',
      `status` = ".$status."
      WHERE `id` =".$_GET['riskid']."";
      $update_riskassessment=mysqli_query($con, $riskassessment);

      //delete all the RA members

      mysqli_query($con, "DELETE FROM `risk_ramemeber` WHERE `riskid` = ".$_GET['riskid']."");

      //insert new one

      //insert all the ra members
       foreach ($_POST['RA_Member'] as $RA_Member)
        {
          $raMemberSql = "INSERT INTO `risk_ramemeber` (`id`, `riskid`, `ramemberId`) VALUES (NULL, '".$_GET['riskid']."', '".$RA_Member."')";
          mysqli_query($con, $raMemberSql);
        }

      //delete all the work activity and hazards
      $getAllWorkSql = "SELECT * FROM `workactivity` WHERE `riskId` = ".$_GET['riskid']."";
      $resultAllWork=mysqli_query($con, $getAllWorkSql);


      while($valueAllWork = mysqli_fetch_assoc($resultAllWork))
      {
        mysqli_query($con, "DELETE FROM `hazard` WHERE `work_id` = ".$valueAllWork['work_id']."");
      }
      mysqli_query($con, "DELETE FROM `workactivity` WHERE `riskId` = ".$_GET['riskid']."");

      $riskassessmentId = $_GET['riskid'];
 }
 else
  {

     $riskassessment = "INSERT INTO `riskassessment` (`id`, `createdBy`, `location`, `process`, `createdDate`, `approveDate`, `revisionDate`, `approveBy`, `status`,`expiry_date`) VALUES (NULL, '".$_SESSION['adminid']."', '".$_POST['location']."', '".$_POST['process']."', '".$creationDate."', NULL, '".$creationDate."', NULL, '".$status."',".$_POST["expiry_date"].");";
      $insert_riskassessment=mysqli_query($con, $riskassessment);
      $riskassessmentId = mysqli_insert_id($con);
      // echo $riskassessment;
      //insert all the ra members
       foreach ($_POST['RA_Member'] as $RA_Member)
        {
          $raMemberSql = "INSERT INTO `risk_ramemeber` (`id`, `riskid`, `ramemberId`) VALUES (NULL, '".$riskassessmentId."', '".$RA_Member."')";

          mysqli_query($con, $raMemberSql);
        }
  }

  foreach ($_POST['work_activity'] as $workactivities)
  {
    if($i > 0)
    {

      $sqlWorkActivity = "INSERT INTO `workactivity` (`work_id`, `riskId`, `name`, `created_by`, `created_on`, `status`) VALUES (NULL, '".$riskassessmentId."', '".$workactivities."', '".$_SESSION['name']."', '".$today."', '0');";
        $insertWorkActivity=mysqli_query($con, $sqlWorkActivity);
        $workActivityId = mysqli_insert_id($con);

        //now we have to chk how many hazards we have
        if($_POST['hazardsCount'][$i] > 0)
        {
            //we have to loop for hazarads
            for($j=1; $j <= $_POST['hazardsCount'][$i]; $j++)
            {

              $actionDate = $_POST['actionMonth'][$k].'/'.$_POST['actionDate'][$k].'/'.$_POST['actionYear'][$k];

              $actonDateToInsert = new DateTime($actionDate);
              $actonDateNow = date_format($actonDateToInsert, 'Y-m-d H:i:s'); // 2011-03-03 00:00:00

			        $ExistingRiskControl = $_POST['Hazard'][$k]!== "other"? serialize($_POST['ExistingRiskControl'][$i][$j]):$_POST['ExistingRiskControl'][$i][$j];


            $sqlHazards = "INSERT INTO `hazard` (`hazard_id`, `work_id`, `name`, `security`, `securitysecond`, `accident`, `likehood`, `likehoodsecond`, `risk_control`, `risk_label`, `risk_additional`, `action_officer`, `action_date`, `status`,`name_other`)
            VALUES (NULL, '".$workActivityId."', '".$_POST['Hazard'][$k]."', '".$_POST['severity'][$k]."', '".$_POST['severitySecond'][$k]."', '".$_POST['InjuryAccident'][$k]."', '".$_POST['likelihood'][$k]."',
             '".$_POST['likelihoodSecond'][$k]."', '".$ExistingRiskControl."', '".$_POST['riskLevel'][$k]."', '".$_POST['additionalRiskContro'][$k]."', '', '".$actonDateNow."', '0','".$_POST['HazardOther'][$k]."');";


             $insertHazards=mysqli_query($con, $sqlHazards);
             $insertHazardsId = mysqli_insert_id($con);


             //insert hazards action officer of this hazards
             $numOfActionOfficer = $_POST['hazardsActionOfficerCount'][$k];
              // print_r($_POST['actionOfficer']);
              // echo $numOfActionOfficer;
                 for($numOfAction = 1; $numOfAction <= $numOfActionOfficer; $numOfAction++)
                  {
                  // echo $l;
                  // echo $_POST['actionOfficer'][$l];

                   $sqlHazardsActionOfficer = "INSERT INTO `hazard_actionofficer` (`id`, `hazardid`, `ramemberId`) VALUES (NULL, '".$insertHazardsId."', '".$_POST['actionOfficer'][$l]."')";
                   mysqli_query($con, $sqlHazardsActionOfficer);

                   $l++;

                  }
              //insert other injuries
              $numOfOtherInjuries = $_POST['hazardsOthersInjuryCount'][$k-1];

                  for($numOfInjury = 1; $numOfInjury <= $numOfOtherInjuries; $numOfInjury++)
                   {


                    $sqlHazardsActionOfficer = "INSERT INTO `injury_hazard` (`id`, `hazard_id`, `injury`) VALUES (NULL, '".$insertHazardsId."', '".$_POST['InjuryAccidentOthers'][$m]."')";
                    mysqli_query($con, $sqlHazardsActionOfficer);

                    $m++;

                   }

                $k++;

            } //main for of hazards

        }
        $i++;

    }
    else
    {
      $i++;
    }



  }

if(isset($insertHazardsId))
{


  if($_POST['saveAsDraft'] == 'Next')
  {
  echo "<script>window.open('riskapproval.php?riskId=".$riskassessmentId."','_self')</script>";
  }
  else
  {
    echo "<script>window.open('listwork_activity.php?message=data','_self')</script>";
  }
}
else
{
  echo "<script>window.open('listwork_activity.php?unsuccess=data','_self')</script>";
}



?>


      <?php include_once 'footer.php';?>
