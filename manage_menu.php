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
 */
/**
 * Manage menu
 *
 * Loaded when a user visits manage.php
 *
 * @package kusaba
 */

session_start();

require 'config.php';
require KU_ROOTDIR . 'lib/dwoo.php';
require KU_ROOTDIR . 'inc/functions.php';
require KU_ROOTDIR . 'inc/classes/manage.class.php';

$manage_class = new Manage();
$dwoo_data->assign('styles', explode(':', KU_MENUSTYLES));

$tpl_links = '';

if(!$manage_class->ValidateSession(true)){
	$tpl_links .= '<li><a href="' . KU_WEBFOLDER . '" target="_top">' . _gettext('Home') . '</a></li>' . "\n";
	$tpl_links .= '<li><a href="manage_page.php">' . ucfirst(_gettext('log in')) . '</a></li>';
}
else {
	$tpl_links .= _gettext('Welcome') . ', <strong>' . $_SESSION['manageusername'] . '</strong>';
	$tpl_links .= '<br />' . _gettext('Staff rights') . ': <strong>';
	if ($manage_class->CurrentUserIsAdministrator()) {
		$tpl_links .= _gettext('Administrator');
	} elseif ($manage_class->CurrentUserIsModerator()) {
		$tpl_links .= _gettext('Moderator');
	}
	$tpl_links .= "</strong>";
	$tpl_links .= '<li><a href="' . KU_WEBFOLDER . '" target="_top">' . _gettext('Home') . '</a></li>' . "\n";
	$tpl_links .= '<li><a href="manage_page.php?action=logout">'._gettext('Log out').'</a></li></ul>';
	// Home
	$tpl_links .= section_html(_gettext('Home'), 'home').'<ul>';
	if ($manage_class->CurrentUserIsAdministrator() || $manage_class->CurrentUserIsModerator()) {
		$tpl_links .= '<li><a href="manage_page.php?action=changepwd">' . _gettext('Change account password') . '</a></li>';
	}
	
	if ($manage_class->CurrentUserIsAdministrator()) {
		$tpl_links .= section_html(_gettext('Site Administration'), 'siteadministration') .'<ul>' . "\n";
		$tpl_links .= '<li><a href="manage_page.php?action=templates">' . _gettext('Edit templates') . '</a></li>
		<li><a href="manage_page.php?action=staff">' . _gettext('Staff') . '</a></li>
		<li><a href="manage_page.php?action=modlog">' . _gettext('ModLog') . '</a></li></ul></div>'. "\n";
	}
	$tpl_links .= '</ul></div>';
	// Moderation
	if ($manage_class->CurrentUserIsAdministrator() || $manage_class->CurrentUserIsModerator()) {
		$open_reports = $tc_db->GetAll("SELECT HIGH_PRIORITY COUNT(*) FROM `" . KU_DBPREFIX . "reports` WHERE `cleared` = '0'");
		$tpl_links .= section_html(_gettext('Moderation'), 'moderation') .
		'<ul>
		<li><a href="manage_page.php?action=reports">' . _gettext('View Reports') . ' [' . $open_reports[0][0] . ']</a></li>
		</ul></div>';
	}
}
/*

		<li><a href="manage_page.php?action=bans">' . _gettext('View/Add/Remove report bans') . '</a></li>';
		$tpl_links .= '<li><a href="manage_page.php?action=delposts">' . _gettext('Delete all reports by IP') . '</a></li>

*/

function section_html($section, $abbreviation, $show=true) {
	return '<h2>
	<span class="plus" onclick="toggle(this, \'' . $abbreviation . '\');" title="'._gettext('Click to show/hide').'">' .
	($show ? '&minus;' : '+') . '
	</span>
	' . $section . '
	</h2>
	<div id="' . $abbreviation . '" style="' . ($show ? '' : 'display:none') . '">';
}

$dwoo_data->assign('links', $tpl_links);
$dwoo->output(KU_TEMPLATEDIR . '/manage_menu.tpl', $dwoo_data);
?>
