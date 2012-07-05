<?php
/*  Fuuka Report System
 *  ------------------------------------------
 *  Author: wutno (#/g/tv - Rizon)
 *
 *
 *  GNU License Agreement
 *  ---------------------
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License version 2 as
 *  published by the Free Software Foundation.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
 *
 *  http://www.gnu.org/licenses/gpl-2.0.txt
 */

require_once('libs/recaptchalib.php');
require_once("config/sql_conf.php");

$privatekey = "";
$publickey  = "";
$ip = $_SERVER['REMOTE_ADDR']; //ip
$boards = array("g", "sci", "diy");
$cats = array("illegal", "spam");

//I can make this work, but I don't want to have to start a new MySQL connection unless something was posted.
function banned_ips($ip){
        global $db;
        $query = $db->query("SELECT `end_time` FROM `user_reports_ban` WHERE `ipv4` ='".$ip."' AND `end_time` >'".time()."' LIMIT 0,1");
        $aretheybanned = $query->fetch_assoc();
        if(!empty($aretheybanned)){
                return $aretheybanned['end_time'];
        }
}
//if (grab_rbanned_ip($ip)) die("You are currently banned from reporting until: ");
#preg_replace("/[^a-z]/", "", $_POST['board']) == $_POST['board']

if(!empty($_GET)){
        if(isset($_GET['postid']) && is_numeric($_GET['postid'])){
                if(isset($_GET['board']) && in_array($_GET['board'], $boards)){
?>
<!DOCTYPE html>
<html>
        <head>
                <title>Report Post #<?=$_GET['postid'];?></title>
                <meta charset="UTF-8" />
                <link rel="stylesheet" type="text/css" href="http://archive.installgentoo.net/media/fuuka.css" title="Fuuka" />
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
        $recap = recaptcha_check_answer($privatekey, $_SERVER["REMOTE_ADDR"], $_POST["recaptcha_challenge_field"], $_POST["recaptcha_response_field"]);
        if (!$recap->is_valid){
                die("The reCAPTCHA wasn't entered correctly. Go back and try it again.");
        }
        else{
                $db = new mysqli("localhost", $username, $password, "fuuka");
                if($db->connect_errno){ die("Cannot connect to MySQL"); }

                //$banned_ip = grab_rbanned_ip($ip); //impliment this evenutally
                //if($banned_ip){ die("You are currently banned from reporting until: ".$banned_ip); }

                if(isset($_POST['cat']) && isset($_POST['board']) && isset($_POST['postid']) && is_numeric($_POST['postid']) && in_array($_POST['board'], $boards) && in_array($_POST['cat'], $cats)){
                        $find_post = $db->query("SELECT `num` FROM `".$_POST['board']."` WHERE `num` ='".$_POST['postid']."'"); //make sure the post exists
                        if($find_post->num_rows == 0){
                                die("There's no post matching that ID.");
                        }
                        else{
                                $db->query("INSERT INTO `user_reports` (`board_id`, `post_id`, `category`, `report_time`, `ipv4`, `action`) VALUES ('".$_POST['board']."', '".$_POST['postid']."', '".$_POST['cat']."', '".time()."', '".$ip."', 'new')") or die (mysql_error());
                                $db->close();
                                echo '
                                        <!DOCTYPE html>
                                        <html>
                                                <head>
                                                        <title>Report Post</title>
                                                        <meta charset="UTF-8" />
                                                        <link rel="stylesheet" type="text/css" href="http://archive.installgentoo.net/media/fuuka.css" title="Fuuka" />
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
