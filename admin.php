<?php 
session_start(); 

require_once "Mail.php";
require_once 'Mail/mime.php';

$mongo = new MongoClient();
$coll = $mongo->helloworld->users;
$count = $mongo->helloworld->counters;
?>
<html>
<head>
	<title>Admin Tools</title>
</head>
<body>
	<?php
	if (isset($_SESSION['logged']) and $_SESSION['logged'] == true){
		echo "Logged in as <i>" . $_SESSION['username'] . "</i> | <a href='logout.php'>Logout</a><br><br>";
		$item = $coll->findOne(array('userid'=>$_SESSION['userid']));
		if ($item['login']['is_admin']){
			echo "Welcome to the HELLOworld administrative tools.<br><br>";
			if ($_SERVER['REQUEST_METHOD'] == 'GET') {
				echo "<form action='" . $_SERVER['PHP_SELF'] . "' method='POST'>
				Post to a blog:<br />
				Title: <input type='text' name='blogtitle' size='20'/><br />
				<textarea cols='40' rows='5' name ='blogbody' /></textarea><br />
				<select name='whichblog'>
				<option value='0'>Official</option>
				<option value='".$_SESSION['userid']."'>Personal</option>
				</select><input type='submit' value='Post' />
				</form><br />";
				echo "<form action='" . $_SERVER['PHP_SELF'] . "' method='POST'>
				Delete user from all databases 
				(please fill in <b>one</b> of the following):<br />
				Username: <input type='text' name='deluser' />
				<input type='submit' value='Delete' /><br />
				UserID: <input type='text' name='delid' />
				<input type='submit' value='Delete' /><br />
				*Admins cannot be deleted with this form.</form><br />";
				echo "<form action='" . $_SERVER['PHP_SELF'] . "' method='POST'>
				Send mass email: (feature currently broken)<br />
				Subject: <input type='text' name='emailsub' size='20'/><br />
				<textarea cols='40' rows='5' name ='emailbody' /></textarea><br />
				<input type='submit' value='Send' />
				</form><br />";
			}
			elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
				$blogtitle = $_POST['blogtitle'];
				$blogbody = $_POST['blogbody'];
				$whichblog = $_POST['whichblog'];
				if (!empty($blogtitle) and !empty($blogbody)){
					$temp = $count->findOne(array('_id'=>'blogs'));
  					$id = $temp['users']["$whichblog"];
  					$count->update(array('_id'=>'blogs'), array('$inc'=>array("users.$whichblog"=>1)));
					$my_array = array("title"=>$blogtitle, "body"=>$blogbody, "posted_by"=>$_SESSION['username'], "id"=>$id, "timestamp"=>strftime("%Y-%m-%d %H:%M:%S"));
					$coll->update(array('userid'=>(int)$whichblog), array('$push'=> array('blog'=>$my_array)));
					die("Sucessfully posted.<br /><a href='admin.php'>Click here</a> to go back.");
				}

				$deluser = $_POST['deluser'];
				$delid = $_POST['delid'];
				if (!empty($deluser)){
					$item = $coll->findOne(array('login.username'=>$deluser));
					if ($item['login']['is_admin']){
						die("Cannot remove another admin. <br /><a href='admin.php'>Click here</a> to go back.");
					}
					else{
					$coll->remove(array('login.username'=>$deluser));
					die("Successfully removed '" . $deluser . "'. <br /><a href='admin.php'>Click here</a> to go back.");
					}
				}
				if (!empty($delid)){
					$item = $coll->findOne(array('userid'=>(int)$delid));
					if ($item['login']['is_admin']){
						die("Cannot remove another admin. <br /><a href='admin.php'>Click here</a> to go back.");
					}
					else{
					$coll->remove(array('userid'=>(int)$delid));
					die("Successfully removed. <br /><a href='admin.php'>Click here</a> to go back.");
					}					
				}
				$emailsub = $_POST['emailsub'];
				$emailbody = $_POST['emailbody'];
				if (!empty($emailbody)){
					if (empty($emailsub)){
						$emailsub = "[HW] No Subject";
					}
					else{
						$emailsub = "[HW] $emailsub"; 
					}
					$pearmail = Mail::factory("smtp", array("host"=>"localhost", "auth"=>false));

  					$htmlversion = $emailbody;
 					$textonly = filter_var($htmlversion,FILTER_SANITIZE_SPECIAL_CHARS);
					$crlf = "\n";

  					$mime = new Mail_mime($crlf);
  					$mime->setTXTBody($textonly);
  					$mime->setHTMLBody($htmlversion);
  					$bodyemail = $mime->get();

  					$cursor = $coll->find(array('login.email'=>array('$exists'=>true)))->sort(array('userid'=>1));
					foreach ($cursor as $doc){
						$mime = new Mail_mime($crlf);
  						$mime->setTXTBody($textonly);
  						$mime->setHTMLBody($htmlversion);
  						$mongoemail = $doc['login']['email'];
  						$headers = $mime->headers(array("From" => "HELLOworld! <hwadmin@sgre.co>", "Subject" => $emailsub, "To"=>$mongoemail));
  						$bodyemail = $mime->get();	
  						$pearmail->send($email, $headers, $bodyemail) or die("ERROR: cannot send mass email to $email.");
					}
					die("Email successfully sent!<br /><a href='admin.php'>Click here</a> to go back.");
				}
			}
		}
		else {
			echo "You do not have access to this site.";
		}
	}


	else{
		echo "Log in below. <br />";
		echo "<form action='login.php' method='post' name='login_form'>
		Username: <input type='text' name='username' /><br />
		Password: <input type='password' name='password' id='password'/><br />
		<input type='submit' value='Login' />
		<input type='button' value='Create Account' onclick=\"window.location.href='http://sgre.co/grecomania/create.html'\"/>
		</form>";
	}

	?>
</body>
</html>