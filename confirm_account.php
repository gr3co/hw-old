<?php
$salt = $_GET['code'];
$user = $_GET['user'];
$mongo = new MongoClient();
$coll = $mongo->helloworld->users;

$item = $coll->findOne(array("login.username"=>$user, "login.salt"=>$salt));

if (is_null($item)){
	die("This username and confirmation code do not match.");
}

else{
$coll->update(array("login.username"=>$user), array('$set'=>array('login.confirmed'=>true)));
header("location:index.html");
}
?>