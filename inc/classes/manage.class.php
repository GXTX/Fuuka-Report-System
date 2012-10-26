<?php
/*
 * This file is part of kusaba.
 *
 * kusaba is free software; you can redistribute it and/or modify it under the
 * terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the License, or (at your option) any later
 * version.
 *
 * kusaba is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * kusaba; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
 * +------------------------------------------------------------------------------+
 * Manage Class
 * +------------------------------------------------------------------------------+
 * Manage functions, along with the pages available
 * +------------------------------------------------------------------------------+
 */
class Manage {

	/* Show the header of the manage page */
	function Header() {
		global $dwoo_data, $tpl_page;

		if (is_file(KU_ROOTDIR . 'inc/pages/modheader.html')) {
			$tpl_includeheader = file_get_contents(KU_ROOTDIR . 'inc/pages/modheader.html');
		} else {
			$tpl_includeheader = '';
		}

		$dwoo_data->assign('includeheader', $tpl_includeheader);
	}

	/* Show the footer of the manage page */
	function Footer() {
		global $dwoo_data, $dwoo, $tpl_page;

		$dwoo_data->assign('page', $tpl_page);

		#$board_class = new Board('');

		$dwoo->output(KU_TEMPLATEDIR . '/manage.tpl', $dwoo_data);
	}

	// Creates a salt to be used for passwords
	function CreateSalt() {
		$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
		$salt = '';

		for ($i = 0; $i < 3; ++$i) {
			$salt .= $chars[mt_rand(0, strlen($chars) - 1)];
		}
		return $salt;
	}

	/* Validate the current session */
	function ValidateSession($is_menu = false) {
		global $tc_db, $tpl_page;

		if (isset($_SESSION['manageusername']) && isset($_SESSION['managepassword'])) {
			$results = $tc_db->GetAll("SELECT HIGH_PRIORITY `username` FROM `" . KU_DBPREFIX . "staff` WHERE `username` = " . $tc_db->qstr($_SESSION['manageusername']) . " AND `password` = " . $tc_db->qstr($_SESSION['managepassword']) . " LIMIT 1");
			if (count($results) == 0) {
				session_destroy();
				exitWithErrorPage(_gettext('Invalid session.'), '<a href="manage_page.php">'. _gettext('Log in again.') . '</a>');
			}

			$tc_db->Execute("UPDATE `" . KU_DBPREFIX . "staff` SET `lastactive` = " . time() . " WHERE `username` = " . $tc_db->qstr($_SESSION['manageusername']));

			return true;
		} else {
			if (!$is_menu) {
				$this->LoginForm();
				die($tpl_page);
			} else {
				return false;
			}
		}
	}

	/* Show the login form and halt execution */
	function LoginForm() {
		global $tc_db, $tpl_page;

		if (file_exists(KU_ROOTDIR . 'inc/pages/manage_login.html')) {
			$tpl_page .= file_get_contents(KU_ROOTDIR . 'inc/pages/manage_login.html');
		}
	}

	/* Check login names and create session if user/pass is correct */
	function CheckLogin() {
		global $tc_db, $action;

		$tc_db->Execute("DELETE FROM `" . KU_DBPREFIX . "loginattempts` WHERE `timestamp` < '" . (time() - 1200) . "'");
		$results = $tc_db->GetAll("SELECT HIGH_PRIORITY `ip` FROM `" . KU_DBPREFIX . "loginattempts` WHERE `ip` = '" . $_SERVER['REMOTE_ADDR'] . "' LIMIT 6");
		if (count($results) > 5) {
			exitWithErrorPage(_gettext('System lockout'), _gettext('Sorry, because of your numerous failed logins, you have been locked out from logging in for 20 minutes. Please wait and then try again.'));
		} else {
			$results = $tc_db->GetAll("SELECT HIGH_PRIORITY `username`, `password`, `salt` FROM `" . KU_DBPREFIX . "staff` WHERE `username` = " . $tc_db->qstr($_POST['username']) . " AND `type` != 3 LIMIT 1");
			if (count($results) > 0) {
				if (empty($results[0]['salt'])) {
					if (md5($_POST['password']) == $results[0]['password']) {
						$salt = $this->CreateSalt();
						$tc_db->Execute("UPDATE `" .KU_DBPREFIX. "staff` SET salt = '" .$salt. "' WHERE username = " .$tc_db->qstr($_POST['username']));
						$newpass = md5($_POST['password'] . $salt);
						$tc_db->Execute("UPDATE `" .KU_DBPREFIX. "staff` SET password = '" .$newpass. "' WHERE username = " .$tc_db->qstr($_POST['username']));
						$_SESSION['manageusername'] = $_POST['username'];
						$_SESSION['managepassword'] = $newpass;
            $_SESSION['token'] = md5($_SESSION['manageusername'] . $_SESSION['managepassword'] . rand(0,100));
						$this->SetModerationCookies();
						$tc_db->Execute("DELETE FROM `" . KU_DBPREFIX . "loginattempts` WHERE `ip` < '" . $_SERVER['REMOTE_ADDR'] . "'");
						$action = 'posting_rates';
						management_addlogentry(_gettext('Logged in'), 1);
						die('<script type="text/javascript">top.location.href = \''. KU_CGIPATH .'/manage.php\';</script>');
					} else {
						$tc_db->Execute("INSERT HIGH_PRIORITY INTO `" . KU_DBPREFIX . "loginattempts` ( `username` , `ip` , `timestamp` ) VALUES ( " . $tc_db->qstr($_POST['username']) . " , '" . $_SERVER['REMOTE_ADDR'] . "' , '" . time() . "' )");
						exitWithErrorPage(_gettext('Incorrect username/password.'));
					}
				} else {
					if (md5($_POST['password'] . $results[0]['salt']) == $results[0]['password']) {
						$_SESSION['manageusername'] = $_POST['username'];
						$_SESSION['managepassword'] = md5($_POST['password'] . $results[0]['salt']);
            $_SESSION['token'] = md5($_SESSION['manageusername'] . $_SESSION['managepassword'] . rand(0,100));
						$this->SetModerationCookies();
						$action = 'posting_rates';
						management_addlogentry(_gettext('Logged in'), 1);
						die('<script type="text/javascript">top.location.href = \''. KU_CGIPATH .'/manage.php\';</script>');
					} else {
						$tc_db->Execute("INSERT HIGH_PRIORITY INTO `" . KU_DBPREFIX . "loginattempts` ( `username` , `ip` , `timestamp` ) VALUES ( " . $tc_db->qstr($_POST['username']) . " , '" . $_SERVER['REMOTE_ADDR'] . "' , '" . time() . "' )");
						exitWithErrorPage(_gettext('Incorrect username/password.'));
					}
				}
			} else {
				$tc_db->Execute("INSERT HIGH_PRIORITY INTO `" . KU_DBPREFIX . "loginattempts` ( `username` , `ip` , `timestamp` ) VALUES ( " . $tc_db->qstr($_POST['username']) . " , '" . $_SERVER['REMOTE_ADDR'] . "' , '" . time() . "' )");
				exitWithErrorPage(_gettext('Incorrect username/password.'));
			}
		}
	}

	/* Set mod cookies for boards */
	function SetModerationCookies() {
		global $tc_db, $tpl_page;

		/*if (isset($_SESSION['manageusername'])) {
			$results = $tc_db->GetAll("SELECT HIGH_PRIORITY `boards` FROM `" . KU_DBPREFIX . "staff` WHERE `username` = " . $tc_db->qstr($_SESSION['manageusername']) . " LIMIT 1");
			if ($this->CurrentUserIsAdministrator() || $results[0][0] == 'allboards') {
				setcookie("kumod", "allboards", time() + 3600, KU_BOARDSFOLDER, KU_DOMAIN);
			}
			else {
				if ($results[0][0] != '') {
					setcookie("kumod", $results[0][0], time() + 3600, KU_BOARDSFOLDER, KU_DOMAIN);
				}
			}
		}*/
	}

  function CheckToken($posttoken) {
    if ($posttoken != $_SESSION['token']) {
      // Something is strange
      session_destroy();
      exitWithErrorPage(_gettext('Invalid Token'));
    }
  }

	/* Log current user out */
	function Logout() {
		global $tc_db, $tpl_page;

		setcookie('kumod', '', time() - 3600, KU_BOARDSFOLDER, KU_DOMAIN);

		session_destroy();
		unset($_SESSION['manageusername']);
		unset($_SESSION['managepassword']);
		unset($_SESSION['token']);
		die('<script type="text/javascript">top.location.href = \''. KU_CGIPATH .'/manage.php\';</script>');
	}

		/* If the user logged in isn't an admin, kill the script */
	function AdministratorsOnly() {
		global $tc_db, $tpl_page;

		if (!$this->CurrentUserIsAdministrator()) {
			exitWithErrorPage('That page is for admins only.');
		}
	}

	/* If the user logged in isn't an moderator or higher, kill the script */
	function ModeratorsOnly() {
		global $tc_db, $tpl_page;

		if ($this->CurrentUserIsAdministrator()) {
			return true;
		}
		else {
			$results = $tc_db->GetAll("SELECT HIGH_PRIORITY `type` FROM `" . KU_DBPREFIX . "staff` WHERE `username` = '" . $_SESSION['manageusername'] . "' AND `password` = '" . $_SESSION['managepassword'] . "' LIMIT 1");
			foreach ($results as $line) {
				if ($line['type'] != 2) {
					exitWithErrorPage(_gettext('That page is for moderators and administrators only.'));
				}
			}
		}
	}

	/* See if the user logged in is an admin */
	function CurrentUserIsAdministrator() {
		global $tc_db, $tpl_page;

		if ($_SESSION['manageusername'] == '' || $_SESSION['managepassword'] == '' || $_SESSION['token'] == '') {
			$_SESSION['manageusername'] = '';
			$_SESSION['managepassword'] = '';
			$_SESSION['token'] = '';
			return false;
		}

		$results = $tc_db->GetAll("SELECT HIGH_PRIORITY `type` FROM `" . KU_DBPREFIX . "staff` WHERE `username` = '" . $_SESSION['manageusername'] . "' AND `password` = '" . $_SESSION['managepassword'] . "' LIMIT 1");
		foreach ($results as $line) {
			if ($line['type'] == 1) {
				return true;
			} else {
				return false;
			}
		}

		/* If the function reaches this point, something is fishy. Kill their session */
		session_destroy();
		exitWithErrorPage(_gettext('Invalid session, please log in again.'));
	}

	/* See if the user logged in is a moderator */
	function CurrentUserIsModerator() {
		global $tc_db, $tpl_page;

		if ($_SESSION['manageusername'] == '' || $_SESSION['managepassword'] == '' || $_SESSION['token'] == '') {
			$_SESSION['manageusername'] = '';
			$_SESSION['managepassword'] = '';
      $_SESSION['token'] = '';
			return false;
		}

		$results = $tc_db->GetAll("SELECT HIGH_PRIORITY `type` FROM `" . KU_DBPREFIX . "staff` WHERE `username` = '" . $_SESSION['manageusername'] . "' AND `password` = '" . $_SESSION['managepassword'] . "' LIMIT 1");
		foreach ($results as $line) {
			if ($line['type'] == 2) {
				return true;
			} else {
				return false;
			}
		}

		/* If the function reaches this point, something is fishy. Kill their session */
		session_destroy();
		exitWithErrorPage(_gettext('Invalid session, please log in again.'));
	}

	/* Find the thumbnail or image directory */
	function findImageDir($num){
		preg_match('/(\d+?)(\d{2})\d{0,3}$/', $num, $matches);

		if(!isset($matches[1]))
			$matches[1] = '';

		if(!isset($matches[2]))
			$matches[2] = '';

		$dir1 = str_pad($matches[1], 4, "0", STR_PAD_LEFT);
		$dir2 = str_pad($matches[2], 2, "0", STR_PAD_LEFT);

		return $dir1.'/'.$dir2;
	}

	/*
	* +------------------------------------------------------------------------------+
	* User Pages
	* +------------------------------------------------------------------------------+
	*/

	function announcements() {
		global $tc_db, $tpl_page;
		$this->ModeratorsOnly();

		$tpl_page .= '<h1><center>'. _gettext('Announcements') .'</center></h1>'. "\n";
		$tpl_page .= 'boop.'. "\n";
	}

	function changepwd() {
		global $tc_db, $tpl_page;

		$tpl_page .= '<h2>'. _gettext('Change account password') . '</h2><br />';
		if (isset($_POST['oldpwd']) && isset($_POST['newpwd']) && isset($_POST['newpwd2'])) {
      $this->CheckToken($_POST['token']);
			if ($_POST['oldpwd'] != '' && $_POST['newpwd'] != '' && $_POST['newpwd2'] != '') {
				if ($_POST['newpwd'] == $_POST['newpwd2']) {
					$results = $tc_db->GetAll("SELECT HIGH_PRIORITY * FROM `" . KU_DBPREFIX . "staff` WHERE `username` = " . $tc_db->qstr($_SESSION['manageusername']) . "");
					foreach ($results as $line) {
						$staff_passwordenc = $line['password'];
						$staff_salt = $line['salt'];
					}
					if (md5($_POST['oldpwd'].$staff_salt) == $staff_passwordenc) {
						$tc_db->Execute("UPDATE `" . KU_DBPREFIX . "staff` SET `password` = '" . md5($_POST['newpwd'].$staff_salt) . "' WHERE `username` = " . $tc_db->qstr($_SESSION['manageusername']) . "");
						$_SESSION['managepassword'] = md5($_POST['newpwd'].$staff_salt);
						$tpl_page .= _gettext('Password successfully changed.');
					} else {
						$tpl_page .= _gettext('The old password you provided did not match the current one.');
					}
				} else {
					$tpl_page .= _gettext('The second password did not match the first.');
				}
			} else {
				$tpl_page .= _gettext('Please fill in all required fields.');
			}
			$tpl_page .= '<hr />';
		}
		$tpl_page .= '<form action="manage_page.php?action=changepwd" method="post">
    <input type="hidden" name="token" value="' . $_SESSION['token'] . '" />
		<label for="oldpwd">'. _gettext('Old password') . ':</label>
		<input type="password" name="oldpwd" /><br />

		<label for="newpwd">'. _gettext('New password') . ':</label>
		<input type="password" name="newpwd" /><br />

		<label for="newpwd2">'. _gettext('New password again') . ':</label>
		<input type="password" name="newpwd2" /><br />

		<input type="submit" value="' ._gettext('Change account password') . '" />

		</form>';
	}

	/*
	* +------------------------------------------------------------------------------+
	* Site Administration Pages
	* +------------------------------------------------------------------------------+
	*/


	/* Edit Dwoo templates */
	function templates() {
		global $tc_db, $tpl_page;
		$this->AdministratorsOnly();

		$files = array();

		$tpl_page .= '<h2>'. _gettext('Template editor') .'</h2><br />';
		if ($dh = opendir(KU_TEMPLATEDIR)) {
			while (($file = readdir($dh)) !== false) {
				if($file != '.' && $file != '..')
				$files[] = $file;
			}
			closedir($dh);
		}
		sort($files);

		if(isset($_POST['templatedata']) && isset($_POST['template'])) {
			$this->CheckToken($_POST['token']);
			$file = basename($_POST['template']);
			if (in_array($file, $files)) {
				if(file_exists(KU_TEMPLATEDIR . '/'. $file)) {
					file_put_contents(KU_TEMPLATEDIR . '/'. $file, $_POST['templatedata']);
					$tpl_page .= '<hr /><h3>'. _gettext('Template edited') .'</h3><hr />';
					/*if (isset($_POST['rebuild'])) {
						$this->rebuildall();
					}*/
					unset($_POST['template']);
					unset($_POST['templatedata']);
				}
			}
		}

		if(!isset($_POST['templatedata']) && !isset($_POST['template'])) {
			$tpl_page .= '<form method="post" action="?action=templates">
			<label for="template">' ._gettext('Template'). ':</label>
			<select name="template" id="template">';
			foreach($files as $template) {
				$tpl_page .='<option name="'. $template .'">'. $template . '</option>';
			}
			$tpl_page .= '</select>';

		}

			if(!isset($_POST['templatedata']) && isset($_POST['template'])) {
			$file = basename($_POST['template']);
			if (in_array($file, $files)) {
				if(file_exists(KU_TEMPLATEDIR . '/'. $file)) {
								$tpl_page .= '<form method="post" action="?action=templates">
          <input type="hidden" name="token" value="' . $_SESSION['token'] . '" />
					<input type="hidden" name="template" value="'. $file .'" />
					<textarea wrap=off rows=40 cols=100 name="templatedata">'. htmlspecialchars(file_get_contents(KU_TEMPLATEDIR . '/'. $file)) . '</textarea>
					<!--<label for="rebuild">'. _gettext('Rebuild HTML after edit?') .'</label>
					<input type="checkbox" name="rebuild" />--><br /><br />
					<div class="desc">'. _gettext('Visit <a href="http://wiki.dwoo.org/">http://wiki.dwoo.org/</a> for syntax information.') . '</div>
					<div class="desc">'. sprintf(_gettext('To access Kusaba variables, use {%%KU_VARNAME}, for example {%%KU_BOARDSPATH} would be replaced with %s'), KU_BOARDSPATH) . '</div>
					<div class="desc">'. _gettext('Enclose text in {t}{/t} blocks to allow them to be translated for different languages.') . '</div><br /><br />';
				}
			}
				}

		$tpl_page .= '<input type="submit" value="' ._gettext('Edit') . '" /></form>';
	}


	function staff() {
		global $tc_db, $tpl_page;
		$this->AdministratorsOnly();

		$tpl_page .= '<h2>' ._gettext('Staff'). '</h2><br />';

		if(isset($_GET['add']) && !empty($_POST['username']) && !empty($_POST['password'])){
			$results = $tc_db->GetAll("SELECT HIGH_PRIORITY * FROM `" .KU_DBPREFIX. "staff` WHERE `username` = " .$tc_db->qstr($_POST['username']));
			if(count($results) == 0) {
				if($_POST['type'] < 3 && $_POST['type'] >= 0){
          $this->CheckToken($_POST['token']);
					$salt = $this->CreateSalt();
					$tc_db->Execute("INSERT HIGH_PRIORITY INTO `" .KU_DBPREFIX. "staff` ( `username` , `password` , `salt` , `type` , `addedon` ) VALUES (" .$tc_db->qstr($_POST['username']). " , '" .md5($_POST['password'] . $salt). "' , '" .$salt. "' , '" .$_POST['type']. "' , '" .time(). "' )");
					$log = _gettext('Added'). ' ';
					switch ($_POST['type']) {
						case 1:
							$log .= _gettext('Administrator');
							break;
						case 2:
							$log .= _gettext('Moderator');
							break;
					}
					$log .= ' '. $_POST['username'];
					management_addlogentry($log, 6);
					$tpl_page .= _gettext('Staff member successfully added.');
				}
				else{
					exitWithErrorPage('Invalid type');
				}
			}
			else{
				$tpl_page .= _gettext('A staff member with that ID already exists.');
			}
		}
		elseif(isset($_GET['del']) && $_GET['del'] > 0){
			$results = $tc_db->GetAll("SELECT HIGH_PRIORITY * FROM `" . KU_DBPREFIX . "staff` WHERE `id` = " . $tc_db->qstr($_GET['del']) . "");
			if(count($results) > 0){
				$username = $results[0]['username'];
				$tc_db->Execute("DELETE FROM `" . KU_DBPREFIX . "staff` WHERE `id` = " . $tc_db->qstr($_GET['del']) . "");
				$tpl_page .= _gettext('Staff successfully deleted') . '<hr />';
				management_addlogentry(_gettext('Deleted staff member') . ': '. $username, 6);
			}
			else{
				$tpl_page .= _gettext('Invalid staff ID.');
			}
		}
		elseif(isset($_GET['edit']) && $_GET['edit'] > 0){
			$results = $tc_db->GetAll("SELECT HIGH_PRIORITY * FROM `" . KU_DBPREFIX . "staff` WHERE `id` = " . $tc_db->qstr($_GET['edit']) . "");
			if (count($results) > 0){
				if (isset($_POST['submitting'])){
					$this->CheckToken($_POST['token']);
					$username = $results[0]['username'];
					$type	= $results[0]['type'];

					$logentry = _gettext('Updated staff member') . ' - ';

					if ($_POST['type'] == '1'){
						$logentry .= _gettext('Administrator');
					}
					elseif ($_POST['type'] == '2'){
						$logentry .= _gettext('Moderator');
					}
					else{
						exitWithErrorPage('Something went wrong.');
					}

					$logentry .= ': '. $username;

					$tc_db->Execute("UPDATE `" . KU_DBPREFIX . "staff` SET `type` = " .$tc_db->qstr($_POST['type']). " WHERE `id` = " . $tc_db->qstr($_GET['edit']) . "");
					management_addlogentry($logentry, 6);
					$tpl_page .= _gettext('Staff successfully updated') . '<hr />';
				}

				$results = $tc_db->GetAll("SELECT HIGH_PRIORITY * FROM `" . KU_DBPREFIX . "staff` WHERE `id` = '" . $_GET['edit'] . "'");
				$username = $results[0]['username'];
				$type	= $results[0]['type'];

				$tpl_page .= '<form action="manage_page.php?action=staff&edit=' .$_GET['edit']. '" method="post">
							<input type="hidden" name="token" value="' . $_SESSION['token'] . '" />
							<label for="username">' ._gettext('Username'). ':</label>
							<input type="text" id="username" name="username" value="' .$username. '" disabled="disabled" /><br />
							<label for="type">' ._gettext('Type'). ':</label>
							<select id="type" name="type">';
				$tpl_page .= ($type==1) ? '<option value="1" selected="selected">' ._gettext('Administrator'). '</option>' : '<option value="1">' ._gettext('Administrator'). '</option>';
				$tpl_page .= ($type==2) ? '<option value="2" selected="selected">' ._gettext('Moderator'). '</option>' : '<option value="2">' ._gettext('Moderator'). '</option>';
				$tpl_page .= '</select><br /><br />';
				$tpl_page .= '<input type="submit" value="'. _gettext('Modify staff member') . '" name="submitting" /></form><br />';
			}
		}



		#END OF ACTIONS


		$tpl_page .= '<form action="manage_page.php?action=staff&add" method="post">
          <input type="hidden" name="token" value="' . $_SESSION['token'] . '" />
					<label for="username">' ._gettext('Username'). ':</label>
					<input type="text" id="username" name="username" /><br />
					<label for="password">' ._gettext('Password'). ':</label>
					<input type="text" id="password" name="password" /><br />
					<label for="type">' ._gettext('Type'). ':</label>
					<select id="type" name="type">
						<option value="1">' ._gettext('Administrator'). '</option>
						<option value="2">' ._gettext('Moderator'). '</option>
					</select><br />

					<input type="submit" value="' ._gettext('Add staff member'). '" />
					</form>
					<hr /><br />';

		$tpl_page .= '<table border="1" width="100%"><tr><th>'. _gettext('Username') . '</th><th>'. _gettext('Added on') . '</th><th>'. _gettext('Last active') . '</th><th>&nbsp;</th></tr>'. "\n";
		$i = 1;
		while($i <= 2){
			if ($i == 1){
				$stafftype = 'Administrator';
				$numtype = 1;
			}
			elseif ($i == 2){
				$stafftype = 'Moderator';
				$numtype = 2;
			}
			$tpl_page .= '<tr><td align="center" colspan="5"><font size="+1"><strong>'. _gettext($stafftype) . '</strong></font></td></tr>'. "\n";
			$results = $tc_db->GetAll("SELECT HIGH_PRIORITY * FROM `" . KU_DBPREFIX . "staff` WHERE `type` = '" .$numtype. "' ORDER BY `username` ASC");
			if(count($results) > 0){
				foreach ($results as $line){
					$tpl_page .= '<tr><td>' .$line['username']. '</td><td>' .date("Y-F-j H:i:s", $line['addedon']). '</td><td>';
					if ($line['lastactive'] == 0){
						$tpl_page .= _gettext('Never');
					}
					elseif ((time() - $line['lastactive']) > 300){
						$tpl_page .= timeDiff($line['lastactive'], false);
					}
					else{
						$tpl_page .= _gettext('Online now');
					}
					$tpl_page .= '</td>';
					$tpl_page .= '</td><td>[<a href="?action=staff&edit='. $line['id'] . '">'. _gettext('Edit') . '</a>] [<a href="?action=staff&del='. $line['id'] . '">'. _gettext('Delete') .'</a>]</td></tr>'. "\n";
				}
			}
			else {
				$tpl_page .= '<tr><td colspan="5">'. _gettext('None') . '</td></tr>'. "\n";
			}
			$i++;
		}
		$tpl_page .= '</table>';
	}

	/* Display moderators and administrators actions which were logged */
	function modlog() {
		global $tc_db, $tpl_page;
		$this->AdministratorsOnly();

		$tc_db->Execute("DELETE FROM `" . KU_DBPREFIX . "modlog` WHERE `timestamp` < '" . (time() - KU_MODLOGDAYS * 86400) . "'");

		$tpl_page .= '<h2>'. ('ModLog') . '</h2><br />
		<table cellspacing="2" cellpadding="1" border="1" width="100%"><tr><th>'. _gettext('Time') .'</th><th>'. _gettext('User') .'</th><th width="100%">'. _gettext('Action') .'</th></tr>';
		$results = $tc_db->GetAll("SELECT HIGH_PRIORITY * FROM `" . KU_DBPREFIX . "modlog` ORDER BY `timestamp` DESC");
		foreach ($results as $line) {
			$tpl_page .= "<tr><td width=\"20%\">" . date("Y-F-j H:i:s", $line['timestamp']) . "</td><td>" . $line['user'] . "</td><td>" . $line['entry'] . "</td></tr>";
		}
		$tpl_page .= '</table>';
	}


	/* Ban IPs from reporting */
	/* Addition, modification, deletion, and viewing of bans */
	function bans() {
		global $tc_db, $tpl_page, $bans_class;
		$this->ModeratorsOnly();

		$reason = KU_BANREASON;
		$ban_ip = '';
		$ban_hash = '';
		$ban_parentid = 0;
		$multiban = Array();

		$tpl_page .= '<h2>'. _gettext('Bans') . '</h2><br />';
		if (((isset($_POST['ip']) || isset($_POST['seconds']) && (!empty($_POST['ip']))))) {
			if ($_POST['seconds'] >= 0) {
				$ban_ip = $_POST['ip'];
				$ban_duration = ($_POST['seconds'] == 0) ? 0 : $_POST['seconds'];
				$ban_reason = $_POST['reason'];
				$ban_note = $_POST['staffnote'];
				$ban_msg = '';

				if($bans_class->BanUser($ban_ip, $_SESSION['manageusername'], $ban_duration, $ban_reason, $ban_note)){
					$tpl_page .= _gettext('Ban successfully placed.')."<br />";	
				}
				else{
					exitWithErrorPage(_gettext('Sorry, a generic error has occurred.'));
				}

				$logentry = _gettext('Banned') . ' '. $ban_ip;
				$logentry .= ($ban_duration == 0) ? ' '. _gettext('without expiration') : ' '. _gettext('until') . ' '. date('F j, Y, g:i a', time() + $ban_duration);
				$logentry .= ' - '. _gettext('Reason') . ': '. $ban_reason . (($ban_note) ? (" (".$ban_note.")") : ("")). ' - '. _gettext('Banned from') . ': Reporting.';
				management_addlogentry($logentry, 8);
				$ban_ip = '';
				$i++;
			}
			else {
				$tpl_page .= _gettext('Please enter a positive amount of seconds, or zero for a permanent ban.');
			}
			$tpl_page .= '<hr />';
		}
		elseif (isset($_GET['delban']) && $_GET['delban'] > 0) {
			$results = $tc_db->GetAll("SELECT HIGH_PRIORITY * FROM `" . KU_DBPREFIX . "banlist` WHERE `id` = " . $tc_db->qstr($_GET['delban']) . "");
			if (count($results) > 0) {
				$unban_ip = md5_decrypt($results[0]['ip'], KU_RANDOMSEED);
				$tc_db->Execute("DELETE FROM `" . KU_DBPREFIX . "banlist` WHERE `id` = " . $tc_db->qstr($_GET['delban']) . "");
				$tpl_page .= _gettext('Ban successfully removed.');
				management_addlogentry(_gettext('Unbanned') . ' '. $unban_ip, 8);
			} else {
				$tpl_page .= _gettext('Invalid ban ID');
			}
			$tpl_page .= '<br /><hr />';
		}

		flush();

		$tpl_page .= '<form action="manage_page.php?action=bans" method="post" name="banform">';

		if (isset($_GET['ip'])){
			$ban_ip = $_GET['ip'];
		}

		$tpl_page .= '<fieldset>
		<legend>'. _gettext('Information') . '</legend>
		<label for="ip">'. _gettext('IP') . ':</label>
		<input type="text" name="ip" id="ip" value="'. $ban_ip . '" /><br/>
		
		<label for="seconds">'. _gettext('Seconds') . ':</label>
		<input type="text" name="seconds" id="seconds" />
		<div class="desc">'. _gettext('Presets') . ':&nbsp;<a href="#" onclick="document.banform.seconds.value=\'3600\';return false;">1hr</a>&nbsp;<a href="#" onclick="document.banform.seconds.value=\'86400\';return false;">1d</a>&nbsp;<a href="#" onclick="document.banform.seconds.value=\'259200\';return false;">3d</a>&nbsp;<a href="#" onclick="document.banform.seconds.value=\'604800\';return false;">1w</a>&nbsp;<a href="#" onclick="document.banform.seconds.value=\'1209600\';return false;">2w</a>&nbsp;<a href="#" onclick="document.banform.seconds.value=\'2592000\';return false;">30d</a>&nbsp;<a href="#" onclick="document.banform.seconds.value=\'31536000\';return false;">1yr</a>&nbsp;<a href="#" onclick="document.banform.seconds.value=\'0\';return false;">'. _gettext('never') .'</a></div><br />

		<label for="reason">'. _gettext('Reason') . ':</label>
		<input type="text" name="reason" id="reason" value="'. $reason . '" /><br />

		<label for="staffnote">'. _gettext('Staff Note') . '</label>
		<input type="text" name="staffnote" id="staffnote" /><br />';

		$tpl_page .= '</fieldset>
		<input type="submit" value="'. _gettext('Add ban') . '" /><img src="clear.gif" />

		</form>
		<hr /><br />';

		$i = 0;

		if (!empty($ban_ip))
			$tpl_page .= '<br /><strong>'. _gettext('Previous bans on this IP') . ':</strong><br />';
		else
			$tpl_page .= '<br /><strong>'. _gettext('Single IP Bans') . ':</strong><br />';

		if (isset($_GET['allbans'])) {
			$results = $tc_db->GetAll("SELECT HIGH_PRIORITY * FROM `" . KU_DBPREFIX . "banlist` WHERE `type` = '" . $i . "' AND `by` != 'SERVER' ORDER BY `id` DESC");
			$hiddenbans = 0;
		} elseif (isset($_GET['limit'])) {
			$results = $tc_db->GetAll("SELECT HIGH_PRIORITY * FROM `" . KU_DBPREFIX . "banlist` WHERE `type` = '" . $i . "' ORDER BY `id` DESC LIMIT ".intval($_GET['limit']));
			$hiddenbans = 0;
		} else {
			if (!empty($ban_ip) && $i == 0) {
				$results = $tc_db->GetAll("SELECT HIGH_PRIORITY * FROM `" . KU_DBPREFIX . "banlist` WHERE `ipmd5` = '" . md5($ban_ip) . "' AND `type` = '" . $i . "' AND `by` != 'SERVER' ORDER BY `id` DESC");
			} else {
				$results = $tc_db->GetAll("SELECT HIGH_PRIORITY * FROM `" . KU_DBPREFIX . "banlist` WHERE `type` = '" . $i . "' AND `by` != 'SERVER' ORDER BY `id` DESC LIMIT 15");
				// Get the number of bans in the database of this type
				$hiddenbans = $tc_db->GetAll("SELECT HIGH_PRIORITY COUNT(*) FROM `" . KU_DBPREFIX . "banlist` WHERE `type` = '" . $i . "'");
				// Subtract 15 from the count, since we only want the number not shown
				$hiddenbans = $hiddenbans[0][0] - 15;
			}
		}
		if (count($results) > 0) {
			$tpl_page .= '<table border="1" width="100%"><tr><th>';
			$tpl_page .= _gettext('IP Address');
			$tpl_page .= '</th><th>'. _gettext('Boards') . '</th><th>'. _gettext('Reason') . '</th><th>'. _gettext('Staff Note') . '</th><th>'. _gettext('Date added') . '</th><th>'. _gettext('Expires/Expired') . '</th><th>'. _gettext('Added By') . '</th><th>&nbsp;</th></tr>';
			foreach ($results as $line) {
				$tpl_page .= '<tr><td><a href="?action=bans&ip='. md5_decrypt($line['ip'], KU_RANDOMSEED) . '">'. md5_decrypt($line['ip'], KU_RANDOMSEED) . '</a></td><td>';
				$tpl_page .= '<strong>'. _gettext('All boards') . '</strong>';
				$tpl_page .= '</td><td>';
				$tpl_page .= (!empty($line['reason'])) ? htmlentities(stripslashes($line['reason'])) : '&nbsp;';
				$tpl_page .= '</td><td>';
				$tpl_page .= (!empty($line['staffnote'])) ? htmlentities(stripslashes($line['staffnote'])) : '&nbsp;';
				$tpl_page .= '</td><td>'. date("F j, Y, g:i a", $line['at']) . '</td><td>';
				$tpl_page .= ($line['until'] == 0) ? '<strong>'. _gettext('Does not expire') . '</strong>' : date("F j, Y, g:i a", $line['until']);
				$tpl_page .= '</td><td>'. $line['by'] . '</td><td>[<a href="manage_page.php?action=bans&delban='. $line['id'] . '">'. _gettext('Delete') .'</a>]</td></tr>';
			}
			$tpl_page .= '</table>';
			if ($hiddenbans > 0) {
				$tpl_page .= sprintf(_gettext('%s bans not shown.'), $hiddenbans) .' <a href="?action=bans&allbans=1">'. _gettext('View all bans') . '</a>'.' <a href="?action=bans&limit=100">View last 100 bans</a>';
			}
		} else {
			$tpl_page .= _gettext('There are currently no bans');
		}
		$tpl_page .= '</table>';
	}
	
	
	
	/*
	* +------------------------------------------------------------------------------+
	* Moderation Pages
	* +------------------------------------------------------------------------------+
	*/
		/* View and delete reports */
	function reports() {
		global $tc_db, $tpl_page, $boards;
		$this->ModeratorsOnly();
		
		$tpl_page .= '<h2>'. _gettext('Reports') . '</h2><br />';
		
		if(isset($_GET['clear']) && is_numeric($_GET['clear'])){
			$resultsreport = $tc_db->GetAll("SELECT `id` FROM `" . KU_DBPREFIX . "reports` WHERE `id` ='" . intval($_GET['clear']) . "'");
			if (count($resultsreport) == 1){
				$tc_db->Execute("UPDATE `" . KU_DBPREFIX . "reports` SET `cleared` ='1' WHERE `id` ='" . intval($_GET['clear']) . "'");
				$tpl_page.= _gettext('Successfully cleared report number: '.$_GET['clear']) . '<hr />';
				management_addlogentry('Cleared report id '.$_GET['clear'], 0);
			}
		}
		
		if(isset($_GET['report']) && is_numeric($_GET['report']) && isset($_GET['delete'])){
			$reportinfo = $tc_db->GetAll("SELECT * FROM `" . KU_DBPREFIX . "reports` WHERE `id` ='".(int)$_GET['report']."'");
			if(count($reportinfo) > 0){
				$postinfo = $tc_db->GetAll("SELECT HIGH_PRIORITY * FROM `".KU_FUUKADB."`.`".$reportinfo[0]['board']."` WHERE `num` = '".$reportinfo[0]['postid']."'");
				$tc_db->Execute("UPDATE `" . KU_DBPREFIX . "reports` SET `cleared` ='1' WHERE `id` ='".intval($_GET['report'])."'");
				
				$logentry = 'Updated report ('.intval($_GET['report']).') - Deleted thumbnail';
				
				if($postinfo['parent'] == 0)
					$thumb = $this->findImageDir($postinfo['num']);
				else
					$thumb = $this->findImageDir($postinfo['parent']);

				@unlink(KU_IMAGEDIR.$tc_db->qstr($reportinfo[0]['board']).'/'.$thumb.'/'.$postinfo['preview']);	

				if($_GET['delete'] == "post"){
					$tc_db->Execute("DELETE FROM `".KU_FUUKADB."`.`".$reportinfo[0]['board']."` WHERE `num` = '".$reportinfo[0]['postid']."'");
					$logentry .= ' and comment.';
				}

				management_addlogentry($logentry, 0);
			}
			else {
				exitWithErrorPage('What are you trying todo?');
			}
		}

		$query = "SELECT HIGH_PRIORITY * FROM `" . KU_DBPREFIX . "reports` WHERE `cleared` = '0' ORDER BY `when` DESC";
		$resultsreport = $tc_db->GetAll($query);
		if (count($resultsreport) > 0) {
			$tpl_page .= '<table border="1" width="100%"><tr><th>Board</th><th>Post</th><th>File</th><th>Message</th><th>Reason</th><th>Reporter IP</th><th>Action</th></tr>';
			foreach ($resultsreport as $linereport){
				$results = $tc_db->GetAll("SELECT HIGH_PRIORITY * FROM `".KU_FUUKADB."`.`".$linereport['board']."` WHERE `num` = '".$linereport['postid']."'");
				foreach ($results as $line){
						$tpl_page .= '<tr><td>/'. $linereport['board'] . '/</td><td><a href="'.KU_ARCHIVEPATH.$linereport['board'].'/post/'.$line['num'].'">'.$line['num'].'</a></td><td>';
						if($line['preview'] != NULL){

							if($line['parent'] == 0)
								$thumb = $this->findImageDir($line['num']);
							else
								$thumb = $this->findImageDir($line['parent']);
							
							$tpl_page .= "[<a href=\"https://archive.installgentoo.net/board/".$linereport['board']."/thumb/".$thumb."/".$line['preview']."\">Thumb</a>]";
						}
						$tpl_page .= '</td><td>';

						if($line['comment'] != '')
							$tpl_page .= '<p>'.nl2br($line['comment']).'</p>';
						else
							$tpl_page .= '&nbsp;';

						$tpl_page .= '</td><td>';

						if($linereport['reason'] != '')
							$tpl_page .= $linereport['reason'];
						else
							$tpl_page .= '&nbsp;';

						$tpl_page .= '</td>
						<td>'. $linereport['ip'] . '</td>
						<td>
							<a href="?action=reports&clear='.$linereport['id'].'" onclick="return confirm(\'Are you sure you want to delete this report?\');">Clear</a>&nbsp;
							&#91;
							<a href="?action=reports&delete=thumb&report='.$linereport['id'].'" title="Delete" onclick="return confirm(\'Are you sure you want to delete this image?\');">DI</a>&nbsp;
							<a href="?action=reports&delete=post&report='.$linereport['id'].'" title="Delete" onclick="return confirm(\'Are you sure you want to delete the image and post?\');">DC</a>
							&#93;
						</td>
					</tr>';
				}
			}
			$tpl_page .= '</table>';
		}
		else {
			$tpl_page .= 'No reports to show.';
		}
	}
}
?>
