<?php
if(!isset($_SERVER['HTTPS']) && KU_RSECURE)
	die("Secure reporting only!");

require 'config.php';
require KU_ROOTDIR . 'lib/dwoo.php';
require KU_ROOTDIR . 'inc/functions.php';
require KU_ROOTDIR . 'inc/classes/bans.class.php';
require KU_ROOTDIR . 'lib/recaptchalib.php';

$ban_class = new Bans();
$ban_class->BanCheck($_SERVER['REMOTE_HOST']);

if(!empty($_GET)){
	if(isset($_GET['postid']) && is_numeric($_GET['postid'])){
		if(isset($_GET['board']) && in_array($_GET['board'], $boards)){
			$body .='<form action=\'user_report.php\' method="POST">
					<table width="100%">
						<tr>
							<td><fieldset><legend>Report type</legend><input type="radio" name="cat" id="cat1" value="illegal"> <label for="cat1">Illegal content</label><br/><input type="radio" name="cat" id="cat2" value="spam"> <label for="cat2">Spam/advertising/flooding</label></fieldset></td>
							<td><script>var RecaptchaOptions = { theme : \'clean\' };</script>'.recaptcha_get_html($publickey).'</td></tr></table>
						</tr>
						<table width="100%"><tr><td width="240px"></td><td><input type="submit" value="Submit">You are reporting post <b>'.$_GET['postid'].'</b> on /'.$_GET['board'].'/.<input type="hidden" name="board" value="'.$_GET['board'].'"><input type="hidden" name="postid" value="'.$_GET['postid'].'"></td></tr></table>
					</table>
					</form>
					<br>
					<div class=\'rules\'><u>Note</u>: When reporting, make sure that the post in question contains content illegal in the Netherlands. Also you are reporting this post on the archive, NOT 4chan.</div>';
			$dwoo_data->assign('post', $_GET['postid']);
			$dwoo_data->assign('body', $body);
			$dwoo->output(KU_TEMPLATEDIR . '/report.tpl', $dwoo_data);
		}
		else{
			exitWithErrorPage("Board not set.");
		}
	}
	else{
		exitWithErrorPage("Post ID not set.");
	}
}
else if(!empty($_POST)){
	$recap = recaptcha_check_answer($privatekey, $_SERVER["REMOTE_ADDR"], $_POST["recaptcha_challenge_field"], $_POST["recaptcha_response_field"]);
	if(!$recap->is_valid){
		exitWithErrorPage("The reCAPTCHA wasn't entered correctly. Go back and try it again.");
	}
	else{
		if(isset($_POST['cat']) && isset($_POST['board']) && isset($_POST['postid']) && is_numeric($_POST['postid']) && in_array($_POST['board'], $boards) && in_array($_POST['cat'], $cats)){
			$find_post = $tc_db->GetAll("SELECT `num` FROM `".KU_FUUKADB."`.`".$_POST['board']."` WHERE `num` ='".$_POST['postid']."'"); //make sure the post exists
			if(count($find_post) == 0){
				exitWithErrorPage("Something went wrong... Go back and try again.");
				die("There's no post matching that ID.");
			}
			else{
				$tc_db->Execute("INSERT INTO `".KU_DBPREFIX."reports` (`board`, `postid`, `reason`, `when`, `ip`, `cleared`) VALUES ('".$_POST['board']."', '".$_POST['postid']."', '".$_POST['cat']."', '".time()."', '".$_SERVER['REMOTE_ADDR']."', '0')");
				$body .='<h3><font color="#FF0000">Report submitted! This window will close in 5 seconds...</font></h3><script language="JavaScript">setTimeout("self.close()", 5000);</script><br><a href="javascript:self.close()">Close</a>';
				$dwoo_data->assign('body', $body);
				$dwoo->output(KU_TEMPLATEDIR . '/report.tpl', $dwoo_data);
			}
		}
		else{
			exitWithErrorPage("Something went wrong... Go back and try again.");
		}
	}
}
else{
	exitWithErrorPage("What are you doing here?");
}
?>