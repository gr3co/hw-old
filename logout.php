<?php
session_start();
$id = $_SESSION['userid'];
$mongo = new MongoClient();
$coll = $mongo->helloworld->users;
$coll->update(array("userid"=>$id), array('$set'=>array('login.logged_in'=>false)));
session_destroy();
header("location:index.html");
?>