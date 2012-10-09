<?php
if(!isset($_SERVER['HTTPS']) && KU_RSECURE)
	die("Secure reporting only!");

require_once('config.php');
require_once(KU_ROOTDIR .'/lib/recaptchalib.php');

function canIReport($ip){
	global $db;
	$query = $db->query("SELECT `until` FROM `".KU_DBPREFIX."banlist` WHERE `ip` ='".$ip."' AND `until` >'".time()."' AND `expired` = '0' LIMIT 0,1") or die ($db->error);
	$amIBanned = $query->fetch_assoc();
	if(!empty($amIBanned)){
		return true;
	}
}

if(!empty($_GET)){
	if(isset($_GET['postid']) && is_numeric($_GET['postid'])){
		if(isset($_GET['board']) && in_array($_GET['board'], $boards)){
?>
<!DOCTYPE html>
<html>
	<head>
		<title>Report Post #<?=$_GET['postid'];?></title>
		<meta charset="UTF-8" /> 
		<link rel="stylesheet" type="text/css" href="https://archive.installgentoo.net/media/fuuka.css" title="Fuuka" />
		<style type="text/css"><!-- html,body { background:#eefff2; color:#002200; } img { border: none; } a { color:#34345c; } a:visited { color:#34345c; } a:hover { color:#DD0000; } .js, .js a { color:black;text-decoration:none; } .js:hover, .js a:hover { color:black;font-weight:bold;text-decoration:underline; } .thumb, .nothumb { float: left; margin: 2px 20px; } .doubledash { vertical-align:top;clear:both;float:left; } .inline { vertical-align:top; } .reply { background:#d6f0da; } .subreply { background:#cce1cf; } .highlight { background:#d6bad0; } .unkfunc{ color:#789922; } .postername { color:#117743; font-weight:bold; text-decoration: none; } .postertrip { color:#228854; text-decoration: none; } a.tooltip span, a.tooltip-red span { display:none; } --></style>
		<style>
			fieldset { margin-right: 25px; }
			.recaptchatable {background-color: transparent !important; border: none !important;}
			.recaptcha_image_cell {background-color: transparent !important;}
			#recaptcha_response_field {border: 1px solid #AAA !important;}
		</style>
	</head>
<body>
	<form action='user_report.php' method="POST">
	<table width="100%">
		<tr>
			<td>
				<fieldset>
					<legend>Report type</legend>
					<input type="radio" name="cat" id="cat1" value="illegal"> <label for="cat1">Illegal content</label><br/>
					<input type="radio" name="cat" id="cat2" value="spam"> <label for="cat2">Spam/advertising/flooding</label>
				</fieldset>
			</td>
			<td>
			<script>
				var RecaptchaOptions = {
					theme : 'clean'
				};
			</script>
			<?=recaptcha_get_html($publickey);?>
			</td>
		</tr>
	</table>
	<table width="100%">
		<tr>
			<td width="240px"></td>
			<td>
				<input type="submit" value="Submit">
				You are reporting post <b><?=$_GET['postid'];?></b> on /<?=$_GET['board'];?>/.
				<input type="hidden" name="board" value="<?=$_GET['board'];?>">
				<input type="hidden" name="postid" value="<?=$_GET['postid'];?>">
			</td>
		</tr>
	</table>
	</form>
	<br>
	<div class='rules'><u>Note</u>: When reporting, make sure that the post in question contains content illegal in the Netherlands. Also you are reporting this post on the archive, NOT 4chan.</div>
</body>
</html>
<?php
		}
		else{
			die("Board not set.");
		}
	}
	else{
		die("Post ID not set.");
	}
}
else if(!empty($_POST)){
	$db = new mysqli(KU_DBHOST, KU_DBUSERNAME, KU_DBPASSWORD, KU_DBDATABASE);
	if($db->connect_errno)
		die("Cannot connect to MySQL");
	
	//if(canIReport($_SERVER['REMOTE_ADDR']))
	//	die("You aren't allowed to report posts. DIE IN A FIRE.");


	$recap = recaptcha_check_answer($privatekey, $_SERVER["REMOTE_ADDR"], $_POST["recaptcha_challenge_field"], $_POST["recaptcha_response_field"]);
	if(!$recap->is_valid){
		die("The reCAPTCHA wasn't entered correctly. Go back and try it again.");
	}
	else{
		if(isset($_POST['cat']) && isset($_POST['board']) && isset($_POST['postid']) && is_numeric($_POST['postid']) && in_array($_POST['board'], $boards) && in_array($_POST['cat'], $cats)){
			$find_post = $db->query("SELECT `num` FROM `".KU_FUUKADB."`.`".$_POST['board']."` WHERE `num` ='".$_POST['postid']."'"); //make sure the post exists
			if($find_post->num_rows == 0){
				die("There's no post matching that ID.");
			}
			else{
				$db->query("INSERT INTO `".KU_DBPREFIX."reports` (`board`, `postid`, `reason`, `when`, `ip`, `cleared`) VALUES ('".$_POST['board']."', '".$_POST['postid']."', '".$_POST['cat']."', '".time()."', '".$_SERVER['REMOTE_ADDR']."', '0')") or die ($db->error);
				$db->close();
				echo '
					<!DOCTYPE html>
					<html>
						<head>
							<title>Report Post</title>
							<meta charset="UTF-8" />
							<link rel="stylesheet" type="text/css" href="https://archive.installgentoo.net/media/fuuka.css" title="Fuuka" />
							<style type="text/css"><!-- html,body { background:#eefff2; color:#002200; } img { border: none; } a { color:#34345c; } a:visited { color:#34345c; } a:hover { color:#DD0000; } .js, .js a { color:black;text-decoration:none; } .js:hover, .js a:hover { color:black;font-weight:bold;text-decoration:underline; } .thumb, .nothumb { float: left; margin: 2px 20px; } .doubledash { vertical-align:top;clear:both;float:left; } .inline { vertical-align:top; } .reply { background:#d6f0da; } .subreply { background:#cce1cf; } .highlight { background:#d6bad0; } .unkfunc{ color:#789922; } .postername { color:#117743; font-weight:bold; text-decoration: none; } .postertrip { color:#228854; text-decoration: none; } a.tooltip span, a.tooltip-red span { display:none; } --></style>
							<style>fieldset { margin-right: 25px; }</style>
						</head>
						<body>
							<h3><font color="#FF0000">Report submitted! This window will close in 5 seconds...</font></h3>
							<script language="JavaScript">setTimeout("self.close()", 5000);</script><br><a href="javascript:self.close()">Close</a>
						</body>
					</html>
				';
				exit();
			}
		}
		else{
			die("Something went wrong... Go back and try again.");
		}
	}
}
else{
	die("What are you doing here?");
}
?>