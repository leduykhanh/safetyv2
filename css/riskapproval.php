<?php
session_start();
 include_once 'header.php';
 include_once 'config.php';
 if(isset($_SESSION['adminid'])=="")
 {

 ?><script type="text/javascript">window.location.assign('index.php');</script>
 <?php

 }

 //update the table


 if(isset($_POST['sendDocument']) && $_POST['sendDocument'] == 'Send Document' && isset($_GET['riskId']))
 {


    if($_POST['NotifySignatureAdded'] == 'on')
    {
      $NotifySignatureAdded = 1;
    }
    else
    {
      $NotifySignatureAdded = 0;
    }

    if($_POST['whenViewed'] == 'on')
    {
      $whenViewed = 1;
    }
    else
    {
      $whenViewed = 0;
    }


    if($_POST['whenSignatureAdded'] == 'on')
    {
      $whenSignatureAdded = 1;
    }
    else
    {
      $whenSignatureAdded = 0;
    }


    if($_POST['asTemplate'] == 'on')
    {
      $asTemplate = 1;
    }
    else
    {
      $asTemplate = 0;
    }

    if($_POST['sendAttachment'] == 'on')
    {
      $sendAttachment = 1;
    }
    else
    {
      $sendAttachment = 0;
    }

    if($_POST['signingReminders'] == 'on')
    {
      $signingReminders = 1;
    }
    else
    {
      $signingReminders = 0;
    }

     $update = "UPDATE `riskassessment` SET `NotifySignatureAdded` = '".$NotifySignatureAdded."', `whenViewed` = '".$whenViewed."', `whenSignatureAdded` = '".$whenSignatureAdded."', `asTemplate` = '".$asTemplate."', `sendAttachment` = '".$sendAttachment."', `signingReminders` = '".$signingReminders."', `sendReminder` = '".$_POST['sendReminder']."', `afterFirstReminder` = '".$_POST['afterFirstReminder']."', `ecpireReminder` = '".$_POST['ecpireReminder']."' , `status` = '0' WHERE `id` = ".$_GET['riskId']."";
  mysqli_query($con,$update);
  $j = 0;
  mysqli_query($con, "DELETE FROM `signing` WHERE `riskid` = ".$_GET['riskId']."");
  foreach ($_POST['staffName'] as $staffName)
  {
      $sqlStaffName = "INSERT INTO `signing` (`id`, `riskid`, `name`, `email`
        ) VALUES (NULL, '".$_GET['riskId']."', '".$staffName."', '".trim($_POST['staffEnail'][$j])."')";
      $insertStaffName=mysqli_query($con, $sqlStaffName);
      $signingId = mysqli_insert_id($con);

      //chking user in the staff table if not creaating one

      $sql = "SELECT * FROM staff_login WHERE email = '".trim($_POST['staffEnail'][$j])."'";
      $result = mysqli_query($con, $sql);
      $num_row= mysqli_num_rows($result);

      if($num_row > 0)
      {
        $userDetails = mysqli_fetch_assoc($result);
        $password = $userDetails['password'];
      }
      else
      {
        //create one with the user name and email
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_-=+;:,.?";
        $password = substr( str_shuffle( $chars ), 0, 4 );

        $insertUser = "INSERT INTO `staff_login` (`id`, `email`, `password`, `name`, `designation`, `age`, `sex`) VALUES (NULL, '".trim($_POST['staffEnail'][$j])."', '".$password."', '".$staffName."', '', '', '')";
        mysqli_query($con,$insertUser);
      }





      //define the receiver of the email
      $to = $_POST['staffEnail'][$j];
      //define the subject of the email
      $subject = 'new approval request for you';
      //create a boundary string. It must be unique
      //so we use the MD5 algorithm to generate a random hash
      $random_hash = md5(time());
      //define the headers we want passed. Note that they are separated with \r\n
      $headers = "From: webmaster@mrphpguru.com\r\nReply-To: webmaster@mrphpguru.com";
      //add boundary string and mime type specification
      $headers .= "\r\nContent-Type: multipart/alternative; boundary=\"PHP-alt-".$random_hash."\"";
      //define the body of the message.
      ob_start(); //Turn on output buffering


      ?>



      Hello <?php echo $staffName;?>! Please review risk assessment and approve it

      To approve risk assessment please use following credentials.

      Site link : http://demo.mrphpguru.com/riskmanagement

      User name : <?php echo $_POST['staffEnail'][$j];?>

      password: <?php echo $password;?>

      Check the link below to see particular riskassessment

      http://demo.mrphpguru.com/riskmanagement?useremail=<?php echo $_POST['staffEnail'][$j];?>


      Thanks
      Admin




      <?php
      //copy current buffer contents into $message variable and delete current output buffer
      $message = ob_get_clean();
      //send the email
      $mail_sent = @mail( $to, $subject, $message, $headers );
      //if the message is sent successfully print "Mail sent". Otherwise print "Mail failed"
     // echo $mail_sent ? "Mail sent" : "Mail failed";

      $j++;
  }

   ?>
    <script type="text/javascript">window.location.assign("listwork_activity.php?id=<?php echo $_GET['riskId'];?>&status=0&message=document sents successfully")</script>
    <?php


 }





 if(isset($_GET['riskId']) && $_GET['riskId'] != '')
    {
      $getRiskAssesmentSQl = "SELECT * FROM  `riskassessment` WHERE id = ".$_GET['riskId']."";
      $resultAllRiskAssesment=mysqli_query($con, $getRiskAssesmentSQl);
      $valueRisk = mysqli_fetch_assoc($resultAllRiskAssesment);

      //print_r($valueRisk);

      //get user details

      $getUserSQl = "SELECT * FROM  `staff_login` WHERE id = ".$valueRisk['createdBy']."";
      $resultAllUser=mysqli_query($con, $getUserSQl);
      $valueUser = mysqli_fetch_assoc($resultAllUser);
    }
 ?>
    <style type="text/css">
        .margin
        {
            margin: 10px;
        }
        .clonedInput { padding: 10px; border-radius: 5px; background-color: #def;}


    </style>


<script type='text/javascript'>//<![CDATA[
$(window).load(function(){
// Add a new repeating section
var attrs = ['for', 'id', 'value'];
function resetAttributeNames(section) {
    var tags = section.find('input, label'), idx = section.index();
    tags.each(function() {
      var $this = $(this);
      $.each(attrs, function(i, attr) {
        var attr_val = $this.attr(attr);
        if (attr_val) {
            $this.attr(attr, attr_val.replace(/_\d+$/, '_'+(idx + 1)));



        }

      })


    })
}

$('.addWorkActivity').click(function(e){
        e.preventDefault();
        var toRepeatingGroup = $('.repeatingSection').last();
        var lastRepeatingGroup = $('.repeatingSection').last();
        var cloned = toRepeatingGroup.clone(true);
        cloned.insertAfter(lastRepeatingGroup);
        resetAttributeNames(cloned);
    });



// Delete a repeating section
$('.deleteWorkActivity').click(function(e){
        e.preventDefault();
        var current_fight = $(this).parent('div');
        var other_fights = current_fight.siblings('.repeatingSection');
        if (other_fights.length === 0) {
            alert("You should atleast have one staff to sign");
            return;
        }
        current_fight.slideUp('slow', function() {
            current_fight.remove();

            other_fights.each(function() {
            resetAttributeNames($(this));
            })

        })


    });

});//]]>

</script>
<form action="riskapproval.php?riskId=<?php echo $_GET['riskId'];?>" method="post">




     <div class="container" style="border:2px solid black;">

            <div class="col-sm-12 form_pad">
                        <h3>Approval of Risk Assessment</h3>
                        <hr class="add_risk" />

                        <div class="row form-row">
                            <div class="col-sm-2"><label>RA Leader:</label></div>
                            <div class="col-sm-4">
                                <label><?php echo $valueUser['name']; ?></label>
                            </div>
                        </div>

                        <div class="row form-row">
                            <div class="col-sm-2"><label>Company:</label></div>
                            <div class="col-sm-4">
                                <label>Reno Box  Pte Ltd</label>
                            </div>

                            <div class="col-sm-2"><label>Reference No:</label></div>
                            <div class="col-sm-4">
                                    <label>00<?php echo $valueRisk['id']; ?></label>
                            </div>
                        </div>


                        <div class="row form-row">
                            <div class="col-sm-2"><label>Risk Location:</label></div>
                            <div class="col-sm-4">
                                <label><?php echo $valueRisk['location']; ?></label>
                            </div>

                            <div class="col-sm-2"><label>Creation Date:</label></div>
                            <div class="col-sm-4">
                                 <label><?php echo $date = date('d-m-Y', strtotime($valueRisk['createdDate']));?></label>
                            </div>
                        </div>

                      <div class="row form-below">
                        <div class="col-sm-2"><label>Risk Process:</label></div>
                        <div class="col-sm-4">
                            <label><?php echo $valueRisk['process']; ?></label>
                        </div>
                    </div>
            </div>



            <div class="col-sm-12 form_pad">
                <h3>Who needs to sign the document?</h3>
                <hr class="add_risk">
               <?php
                  $getSigningSQl = "SELECT * FROM  `signing` WHERE riskid = ".$_GET['riskId']."";
                  $signing = mysqli_query($con, $getSigningSQl);

                 $getSigning = mysqli_fetch_assoc($signing);

               ?>

                <div class="col-sm-12 form-row">
                  <label class="col-sm-3">Approving Manager:</label>
                  <input class="col-sm-4" type="text" id="staffname_a_id_1" placeholder="" name="staffName[]" required value="<?php echo $getSigning['name'];?>">
                  <label class="col-sm-2">Email:</label>
                  <input class="col-sm-3" type="email" id="staffemail_a_id_1" placeholder="" name="staffEnail[]" required value="<?php echo $getSigning['email'];?>">
                </div>
            </div>

<!--Footer button-->

            <div class="col-sm-12 form_pad" style="display:none">
                <div class="row col-sm-12">
                    <h3>Declaration of Risk Assessment</h3>
                    <hr class="add_risk" />
                </div>



                    <div class="row col-sm-12 margin">
                        <span class="glyphicon glyphicon-question-sign question_icon_spaccing"></span>



                        <input type="checkbox" name="NotifySignatureAdded" <?php if($valueRisk['NotifySignatureAdded'] == 1){echo 'checked';} ?>> Notify me when a signature is added

                    </div>

                    <div class="row col-sm-12 margin">
                        <span class="notify_checkbox"></span>
                        <input type="checkbox" name="whenViewed" <?php if($valueRisk['whenViewed'] == 1){echo 'checked';} ?>>Before the document is signed continue emailing me when ever it is viewed

                    </div>


                    <div class="row col-sm-12 margin">
                     <span class="glyphicon glyphicon-question-sign question_icon_spaccing"></span>
                     <input type="checkbox" name="whenSignatureAdded" <?php if($valueRisk['whenSignatureAdded'] == 1){echo 'checked';} ?>> Notify me when a signature is added
                    </div>

                    <div class="row col-sm-12 margin">
                     <span class="glyphicon glyphicon-question-sign question_icon_spaccing"></span>
                     <input type="checkbox" name="asTemplate" <?php if($valueRisk['asTemplate'] == 1){echo 'checked';} ?>> Save documents as template
                    </div>


                    <div class="row col-sm-12 margin">
                     <span class="glyphicon glyphicon-question-sign question_icon_spaccing"></span>
                     <input type="checkbox" name="sendAttachment" <?php if($valueRisk['sendAttachment'] == 1){echo 'checked';} ?>> Send the PDF of this aggrement as an email attachment

                    </div>

                    <div class="row col-sm-12 margin">
                     <span class="glyphicon glyphicon-question-sign question_icon_spaccing"></span>
                     <input type="checkbox" name="signingReminders" <?php if($valueRisk['signingReminders'] == 1){echo 'checked';} ?>> Enable signing reminders
                    </div>

                    <div class="row col-sm-12 margin">
                       <span class="simple_text">Send the reminder eamil to the signer in</span>
                       <input type="text" class="days_input" name="sendReminder" value="<?php echo $valueRisk['sendReminder'];?>" > days

                    </div>

                    <div class="row col-sm-12 margin">
                       <span class="simple_text">After the first remeinder send reminder everday </span>
                       <input type="text" class="days_input" name="afterFirstReminder" value="<?php echo $valueRisk['afterFirstReminder'];?>"> days
                    </div>

                    <div class="row col-sm-12 margin">
                       <span class="simple_text">Expire reminders in</span>
                       <input type="text" class="days_input" name="ecpireReminder" value="<?php echo $valueRisk['ecpireReminder'];?>"> days
                    </div>

                    <div class="row">
                        <div class="col-sm-10 margin">
                            <label>
                                <a class="advance-setting" id="advance_setting">(+)Advance document setting</a>
                            </label>
                        </div>
                    </div>
          </div>


       <div class="row">


                   <div class="col-sm-8">


                        <a class="btn btn-success col-sm-2" target="_blank" href="companyreport.php?riskid=<?php echo $_GET['riskId'];?>">Preview</a>



                    </div>
                    <div class="col-sm-4">

                        <input type="submit" value="Send Document" name="sendDocument" class="btn btn-success pull-right">




                    </div>


        </div>
      <div class="row">&nbsp; </div>
    <!--Footer Botton-->
    </div>

</form>

<div class="form-row col-sm-12"></div>

 <?php include_once 'footer.php';?>
