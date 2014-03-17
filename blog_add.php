<?php 
session_start(); 
$mongo = new MongoClient();
$coll = $mongo->helloworld->users;
$count = $mongo->helloworld->counters;

$blogtitle = $_POST['blogtitle'];
$blogbody = $_POST['blogbody'];

$blogtitle = filter_var($blogtitle,FILTER_SANITIZE_SPECIAL_CHARS);
$blogbody = filter_var($blogbody,FILTER_SANITIZE_SPECIAL_CHARS);

if (strlen($blogtitle) == 0){
	$blogtitle = "Untitled";
}

if (strlen($blogbody) == 0){
	die("Your post's body cannot be empty.");
}


$whichblog = $_SESSION['userid'];
$temp = $count->findOne(array('_id'=>'blogs'));
$id = $temp['users']["$whichblog"];
$count->update(array('_id'=>'blogs'), array('$inc'=>array("users.$whichblog"=>1)));
$my_array = array("title"=>$blogtitle, "body"=>$blogbody, "posted_by"=>$_SESSION['username'], "id"=>$id, "timestamp"=>strftime("%Y-%m-%d %H:%M:%S"));
$coll->update(array('userid'=>(int)$whichblog), array('$push'=> array('blog'=>$my_array)));

header("location:blog.html?user=".$_SESSION['username']);

?>