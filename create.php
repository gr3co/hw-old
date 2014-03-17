<?php

require_once "Mail.php";
require_once 'Mail/mime.php';

session_start(); 
$mongo = new MongoClient();
$coll = $mongo->helloworld->users;
$count = $mongo->helloworld->counters;

$user = $_POST['username'];
$pass = $_POST['password'];
$email = $_POST['email'];
$first = $_POST['first'];
$middle = $_POST['middle'];
$last = $_POST['last'];
$age = $_POST['age'];
$gender = $_POST['gender'];
$location = $_POST['location'];
$quote = $_POST['quote'];
$pic = $_POST['pic'];
$pic = filter_var($pic,FILTER_SANITIZE_STRING);
$user = filter_var($user,FILTER_SANITIZE_STRING);
$email = filter_var($email,FILTER_SANITIZE_EMAIL);
$quote = filter_var($quote,FILTER_SANITIZE_SPECIAL_CHARS);
$age = filter_var($age,FILTER_SANITIZE_NUMBER_INT);
$user = strtolower($user);
$email = strtolower($email);

list($width, $height, $type, $attr) = getimagesize($pic);
if (!(empty($pic) or (isset($type) && in_array($type, array(
  IMAGETYPE_PNG, IMAGETYPE_JPEG, IMAGETYPE_GIF))))) {
  die('Please submit a valid image URL.');
}

if (strlen($user) == 0){
	die('Please enter a username.');
}
if (strlen($pass) < 6){
	die('Passwords must be at least 6 characters long.');
}
if (!check_email_address($email)){
	die('Please enter a valid e-mail address.');
}

$item = $coll->findOne(array('login.username'=>$user));

if (!is_null($item)){
  die("Username already exists.");
}
else {

  echo "Please wait while your profile is generated....<br>
  You will be redirected shortly.<br><br>";

  echo "generating photo database...<br>";

  if (!empty($pic)){
    $picarray = array('date'=>strftime("%Y-%m-%d %H:%M:%S"), 'source'=>$pic, 'profile_pic'=>true);
    $picset = true;
  }
  else{
    $pic = "images/default.png";
    $picset = false;
  }

  echo "generating login credentials...<br>"; 
  $temp = $count->findOne(array('_id'=>'userid'));
  $userid = $temp['seq'];
  $salt = md5(uniqid(rand(), true));
  $count->update(array('_id'=>'userid'), array('$inc'=>array('seq'=>1)));
  $loginarray = array('username'=>$user, 'password'=>md5($pass), 'salt'=> $salt, 'confirmed'=>false,
    'is_admin'=>false, 'email'=>$email, 'logged_in'=>false, 'date_created'=>strftime("%Y-%m-%d"));

  echo "generating profile credentials...<br>";
  $profilearray = array('first'=>$first, 'middle'=>$middle, 'last'=>$last, 'age'=>$age, 
    'gender'=>$gender, 'location'=>$location, 'quote'=>$quote, 'photo'=>$pic);

  echo "saving to database...<br>";
  $doc = array('userid'=>$userid, 'login'=>$loginarray, 'profile'=>$profilearray);
  $coll->insert($doc);

  if ($picset){
    $coll->update(array('userid'=>$userid), array('$push'=>array('photos'=>$picarray)));
  }

  echo "sending confirmation email...<br>";
  $pearmail = Mail::factory("smtp", array("host"=>"localhost", "auth"=>false));

  $textonly = "Hello $first! \r\n \r\n Thank you for joining HELLOworld. Please provide any feedback! \r\n \r\n Sincerely,\r\n Stephen Greco\r\n Creator";
  $htmlversion = "Hello $first!<br><br> <p>Thank you for joining HELLOworld! I hope you like the site.<br>Please provide any feedback to 
  <a href='mailto:sgreco@sgre.co'>sgreco@sgre.co</a>.</p> <p>Please click this link to confirm your account: 
  <a href='http://sgre.co/helloworld/confirm_account.php?user=$user&code=$salt'>http://sgre.co/helloworld/confirm_account.php?user=$user&code=$salt</a></p><br>Sincerely,<br><br> Stephen Greco<br> Creator";
  $crlf = "\n";

  $mime = new Mail_mime($crlf);
  $mime->setTXTBody($textonly);
  $mime->setHTMLBody($htmlversion);

  $headers = $mime->headers(array("From" => "HELLOworld! <hwadmin@sgre.co>", "Subject" => "Welcome to HELLOworld!", "To"=>$email));
  $bodyemail = $mime->get();
  $pearmail->send($email, $headers, $bodyemail) or die("ERROR: cannot send confirmation email.");

  echo "redirecting...<br><br><br>";
  echo "<a href=http://sgre.co/helloworld/profile?user=$user>click here</a> if redirect fails.";
  header("location:http://sgre.co/helloworld/profile?user=$user");
}


function check_email_address($email) {
  if (!ereg("^[^@]{1,64}@[^@]{1,255}$", $email)) {
    return false;
  }
  $email_array = explode("@", $email);
  $local_array = explode(".", $email_array[0]);
  for ($i = 0; $i < sizeof($local_array); $i++) {
    if
      (!ereg("^(([A-Za-z0-9!#$%&'*+/=?^_`{|}~-][A-Za-z0-9!#$%&
        ↪'*+/=?^_`{|}~\.-]{0,63})|(\"[^(\\|\")]{0,62}\"))$",
    $local_array[$i])) {
        return false;
      }
    }
    if (!ereg("^\[?[0-9\.]+\]?$", $email_array[1])) {
      $domain_array = explode(".", $email_array[1]);
      if (sizeof($domain_array) < 2) {
        return false;
      }
      for ($i = 0; $i < sizeof($domain_array); $i++) {
        if
          (!ereg("^(([A-Za-z0-9][A-Za-z0-9-]{0,61}[A-Za-z0-9])|
            ↪([A-Za-z0-9]+))$",
        $domain_array[$i])) {
            return false;
          }
        }
      }
      return true;
    }


    ?>