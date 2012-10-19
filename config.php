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
 * A PARTICULAR PURPOSE.  See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * kusaba; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */
/**
 * Script configuration
 *
 * Tells the script what to call itself, where the database and other things are
 * located, along with define what features to enable.
 *
 * @package kusaba
 */
/*
To enable a feature, change the value to true:
	define('KU_INSTANTREDIRECT', true);
To disable a feature, change the value to false:
	define('KU_INSTANTREDIRECT', false;

To change the text value of a configuration, edit the text in the single quotes:
	define('KU_NAME', 'kusaba');
Becomes:
	define('KU_NAME', 'Mychan');
Warning: Do not insert single quotes in the value yourself, or else you will cause problems.  To overcome this, you use what is called escaping, which is the process of adding a backslash before the single quote, to show it is part of the string:
	define('KU_NAME', 'Jason\'s chan');

*/
// Sets error reporting to hide notices.
error_reporting(E_ALL ^ E_NOTICE);
if (!headers_sent()) {
	header('Content-Type: text/html; charset=utf-8');
}

$cf = array();
		$cf['KU_RSECURE'] = true;
	// Database
		$cf['KU_DBTYPE']          = 'mysqli';	// Database type. Valid values are mysql and mysqli (reccomended for mysql).
							// PostgreSQL is also supported. Supported values are postgres64, postgres7 and postgres8. Only postgres8 is tested.
							// SQLite is also supported. Set to sqlite to use. SQLite will not use any database software, only a single file.
		$cf['KU_DBHOST']          = 'localhost'; // Database hostname. On SQLite this has no effect.
		$cf['KU_DBDATABASE']      = ''; // Database... database. On SQLite this will be the path to your database file. Secure this file.
		$cf['KU_DBUSERNAME']      = ''; // Database username. On SQLite this has no effect.
		$cf['KU_DBPASSWORD']      = ''; // Database password. On SQLite this has no effect.
		$cf['KU_DBPREFIX']        = ''; // Database table prefix
		$cf['KU_DBUSEPERSISTENT'] = false; // Use persistent connection to database
		$cf['KU_FUUKADB'] 		  = 'fuuka';

	// Paths and URLs
		// Main installation directory
			$cf['KU_ROOTDIR']   = realpath(dirname(__FILE__))."/"; // Full system path of the folder containing kusaba.php, with trailing slash. The default value set here should be OK.. If you need to change it, you should already know what the full path is anyway.
			$cf['KU_WEBFOLDER'] = '/admin/'; // The path from the domain of the board to the folder which kusaba is in, including the trailing slash.  Example: "http://www.yoursite.com/misc/kusaba/" would have a $cf['KU_WEBFOLDER'] of "/misc/kusaba/"
			$cf['KU_WEBPATH']   = 'https://archive.installgentoo.net/admin/'; // The path to the index folder of kusaba, without trailing slash. Example: http://www.yoursite.com
			$cf['KU_DOMAIN']    = '.installgentoo.net'; // Used in cookies for the domain parameter.  Should be a period and then the top level domain, which will allow the cookies to be set for all subdomains.  For http://www.randomchan.org, the domain would be .randomchan.org; http://zachchan.freehost.com would be zach.freehost.com
			$cf['KU_ARCHIVEPATH']  = 'https://archive.installgentoo.net/';
			$cf['KU_IMAGEDIR']     = '/var/www/archive.installgentoo.net/board/';

		// Board subdomain/alternate directory (optional, change to enable)
			// DO NOT CHANGE THESE IF YOU DO NOT KNOW WHAT YOU ARE DOING!!
			$cf['KU_BOARDSDIR']    = $cf['KU_ROOTDIR'];
			$cf['KU_BOARDSFOLDER'] = $cf['KU_WEBFOLDER'];
			$cf['KU_BOARDSPATH']   = $cf['KU_WEBPATH'];

		// CGI subdomain/alternate directory (optional, change to enable)
			// DO NOT CHANGE THESE IF YOU DO NOT KNOW WHAT YOU ARE DOING!!
			$cf['KU_CGIDIR']    = $cf['KU_BOARDSDIR'];
			$cf['KU_CGIFOLDER'] = $cf['KU_BOARDSFOLDER'];
			$cf['KU_CGIPATH']   = $cf['KU_BOARDSPATH'];

	// Templates
		$cf['KU_TEMPLATEDIR']       = $cf['KU_ROOTDIR'] . 'dwoo/templates'; // Dwoo templates directory
		$cf['KU_CACHEDTEMPLATEDIR'] = $cf['KU_ROOTDIR'] . 'dwoo/templates_c'; // Dwoo compiled templates directory.  This folder MUST be writable (you may need to chmod it to 755).  Set to '' to disable template caching
	// Misc config
		$cf['KU_MODLOGDAYS']        = 7; // Days to keep modlog entries before removing them
		$cf['KU_RANDOMSEED']        = ''; // Type a bunch of random letters/numbers here, any large amount (35+ characters) will do

	// Language / timezone / encoding
		$cf['KU_LOCALE']  = 'en'; // The locale of kusaba you would like to use.  Locales available: en, de, et, es, fi, pl, nl, nb, ro, ru, it, ja
		$cf['KU_CHARSET'] = 'UTF-8'; // The character encoding to mark the pages as.  This must be the same in the .htaccess file (AddCharset charsethere .html and AddCharset charsethere .php) to function properly.  Only UTF-8 and Shift_JIS have been tested
		putenv('TZ=US/Pacific'); // The time zone which the server resides in
		$cf['KU_DATEFORMAT'] = 'Y-F-j H:i:s';

	// Debug
		$cf['KU_DEBUG'] = false; // When enabled, debug information will be printed (Warning: all queries will be shown publicly)

	// Post-configuration actions, don't modify these
		$cf['KU_VERSION']    = '0.9.3';

		if (substr($cf['KU_WEBFOLDER'], -2) == '//') { $cf['KU_WEBFOLDER'] = substr($cf['KU_WEBFOLDER'], 0, -1); }
		if (substr($cf['KU_BOARDSFOLDER'], -2) == '//') { $cf['KU_BOARDSFOLDER'] = substr($cf['KU_BOARDSFOLDER'], 0, -1); }
		if (substr($cf['KU_CGIFOLDER'], -2) == '//') { $cf['KU_CGIFOLDER'] = substr($cf['KU_CGIFOLDER'], 0, -1); }

		$cf['KU_WEBPATH'] = trim($cf['KU_WEBPATH'], '/');
		$cf['KU_BOARDSPATH'] = trim($cf['KU_BOARDSPATH'], '/');
		$cf['KU_CGIPATH'] = trim($cf['KU_CGIPATH'], '/');

		while (list($key, $value) = each($cf)) {
			define($key, $value);
		}
		unset($cf);

// DO NOT MODIFY BELOW THIS LINE UNLESS YOU KNOW WHAT YOU ARE DOING OR ELSE BAD THINGS MAY HAPPEN
//reCaptcha info
$privatekey = "";
$publickey  = "";

//Reports
$boards = array("g", "sci", "diy");
$cats = array("illegal", "spam");

$required = array(KU_ROOTDIR, KU_WEBFOLDER, KU_WEBPATH);
if (in_array('CHANGEME', $required) || in_array('', $required)){
	echo 'You must set KU_ROOTDIR, KU_WEBFOLDER, and KU_WEBPATH before installation will finish!';
	die();
}
require KU_ROOTDIR . 'lib/gettext/gettext.inc.php';
require KU_ROOTDIR . 'lib/adodb/adodb.inc.php';

// Gettext
_textdomain('kusaba');
_setlocale(LC_ALL, KU_LOCALE);
_bindtextdomain('kusaba', KU_ROOTDIR . 'inc/lang');
_bind_textdomain_codeset('kusaba', KU_CHARSET);

// SQL  database
if (!isset($tc_db) && !isset($preconfig_db_unnecessary)) {
	$tc_db = &NewADOConnection(KU_DBTYPE);
	if (KU_DBUSEPERSISTENT) {
		$tc_db->PConnect(KU_DBHOST, KU_DBUSERNAME, KU_DBPASSWORD, KU_DBDATABASE) or die('SQL database connection error: ' . $tc_db->ErrorMsg());
	} else {
		$tc_db->Connect(KU_DBHOST, KU_DBUSERNAME, KU_DBPASSWORD, KU_DBDATABASE) or die('SQL database connection error: ' . $tc_db->ErrorMsg());
	}
	// SQL debug
	if (KU_DEBUG) {
		$tc_db->debug = true;
	}
}

function stripslashes_deep($value)
{
	$value = is_array($value) ?
		array_map('stripslashes_deep', $value) :
		stripslashes($value);
	return $value;
}

// Thanks Z
if (get_magic_quotes_gpc()) {
	$_POST = array_map('stripslashes_deep', $_POST);
	$_GET = array_map('stripslashes_deep', $_GET);
	$_COOKIE = array_map('stripslashes_deep', $_COOKIE);
}
if (get_magic_quotes_runtime()) {
	set_magic_quotes_runtime(0);
}

?>
