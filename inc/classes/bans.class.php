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
 * Bans Class
 *
 * Assorted banning-related functions placed into class format
 *
 * @package kusaba
 */

class Bans {

	/* Perform a check for a ban record for a specified IP address */
	function BanCheck($ip) {
		global $tc_db;

		if (!isset($_COOKIE['tc_previousip']))
			$_COOKIE['tc_previousip'] = '';

		$bans = Array();
		$results = $tc_db->GetAll("SELECT * FROM `".KU_DBPREFIX."banlist` WHERE ((`type` = '0' AND ( `ipmd5` = '" . md5($ip) . "' OR `ipmd5` = '". md5($_COOKIE['tc_previousip']) . "' )) OR `type` = '1') AND (`expired` = 0)" );
		if (count($results)>0) {
			foreach($results AS $line) {
				if(($line['type'] == 1 && strpos($ip, md5_decrypt($line['ip'], KU_RANDOMSEED)) === 0) || $line['type'] == 0) {
					if ($line['until'] != 0 && $line['until'] < time()){
						$tc_db->Execute("UPDATE `".KU_DBPREFIX."banlist` SET `expired` = 1 WHERE `id` = ".$line['id']);
						$line['expired'] = 1;
					}
					$bans[] = $line;
				}
			}
		}
		if(count($bans) > 0){
			$tc_db->Execute("END TRANSACTION");
			die("You are currently banned.<br/>Sorry.");
		}
		return true;
	}
	
	/* Add a ip/ip range ban */
	function BanUser($ip, $modname, $duration, $reason, $staffnote) {
		global $tc_db;
		
		if ($duration>0)
			$ban_until = time()+$duration;
		else
			$ban_until = '0';

		$tc_db->Execute("INSERT INTO `".KU_DBPREFIX."banlist` ( `ip` , `ipmd5` , `by` , `at` , `until` , `reason`, `staffnote` ) VALUES ( ".$tc_db->qstr(md5_encrypt($ip, KU_RANDOMSEED))." , ".$tc_db->qstr(md5($ip))." , ".$tc_db->qstr($modname)." , ".time()." , ".intval($ban_until)." , ".$tc_db->qstr($reason)." , ".$tc_db->qstr($staffnote).") ");

		return true;
	}
}

?>
