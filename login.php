<?php

session_start();

$mongo = new MongoClient() or die("Cannot connect to Mongo");
$coll = $mongo->helloworld->users;

$user = $_POST['username'];
$pass = $_POST['password'];


$login = array( "login.username" => $user, "login.password" => md5($pass));
$cursor = $coll->findOne($login);
$previous = $_SERVER['HTTP_REFERER'];
$userid = $cursor['userid'];

if (!is_null($cursor)){
$_SESSION['logged'] = true;
$_SESSION['userid'] = $userid;
$_SESSION['username'] = $user;
$coll->update(array('userid'=>$userid), array('$set'=>array('login.logged_in'=>true)));
header("location:$previous");
}
else {
echo "Login failed. Click <a href='$previous'>here</a> to go back.";
}

?>