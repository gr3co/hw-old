<?php

session_start(); 
$mongo = new MongoClient();
$coll = $mongo->helloworld->users;

$email = $_POST['email'];
$first = $_POST['first'];
$middle = $_POST['middle'];
$last = $_POST['last'];
$age = $_POST['age'];
$gender = $_POST['gender'];
$location = $_POST['location'];
$quote = $_POST['quote'];


$email = filter_var($email,FILTER_SANITIZE_EMAIL);
$quote = filter_var($quote,FILTER_SANITIZE_SPECIAL_CHARS);
$age = filter_var($age,FILTER_SANITIZE_NUMBER_INT);
$email = strtolower($email);


$doc = array('login.email'=>$email, 'profile.first'=>$first, 'profile.middle'=>$middle, 'profile.last'=>$last, 
  'profile.age'=>$age, 'profile.gender'=>$gender, 'profile.location'=>$location, 'profile.quote'=>$quote);

$coll->update(array('userid'=>$_SESSION['userid']), array('$set'=>$doc));



if (!check_email_address($email)){
	die('Please enter a valid e-mail address.');
}


header("location:http://sgre.co/helloworld/profile?user=".$_SESSION['username']);

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