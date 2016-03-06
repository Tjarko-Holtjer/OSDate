<?php

require_once(dirname(__FILE__).'/init.php');

if (!isset($_REQUEST['a']) || empty($_REQUEST['a']) ) return '';

switch (trim($_REQUEST['a'])) {

	case 'sendSBMsg':

		$text = str_replace('|amp|','&amp;',strip_tags($_REQUEST['msg']));
		$text = nl2br($text);
		// Smilies, Mail, Web Filter
		$replacetext = "-- Adress Removed --";

		$text = str_replace(":-)", "<img src=\"".DOC_ROOT."images/smilies/lucky.gif\"\/>", $text);
		$text = str_replace(":)", "<img src=\"".DOC_ROOT."images/smilies/lucky.gif\"\/>", $text);
		$text = str_replace(";)", "<img src=\"".DOC_ROOT."images/smilies/wink.gif\"\/>", $text);
		$text = str_replace(";-)", "<img src=\"".DOC_ROOT."images/smilies/wink.gif\"\/>", $text);
		$text = str_replace("8-)", "<img src=\"".DOC_ROOT."images/smilies/cool.gif\"\/>", $text);
		$text = str_replace("8)", "<img src=\"".DOC_ROOT."images/smilies/cool.gif\"\/>", $text);
		$text = str_replace(":(", "<img src=\"".DOC_ROOT."images/smilies/worse.gif\"\/>", $text);
		$text = str_replace(":-(", "<img src=\"".DOC_ROOT."images/smilies/worse.gif\"\/>", $text);
		$text = str_replace(":-P", "<img src=\"".DOC_ROOT."images/smilies/p.gif\"\/>", $text);
		$text = str_replace(":P", "<img src=\"".DOC_ROOT."images/smilies/p.gif\"\/>", $text);

		$text = preg_replace( "/[A-Z0-9._%-]+@[A-Z0-9._%-]+\.[A-Z]{2,6}/i", $replacetext, $text );
		$text = preg_replace( "/[A-Z0-9._%-]+ at [A-Z0-9._%-]+ dot [A-Z]{2,6}/i", $replacetext, $text );
		$text = preg_replace( "/[A-Z0-9._%-]+\.[A-Z0-9._%-]+\.[A-Z]{2,6}/i", $replacetext, $text );
		$userid = ($_SESSION['UserId'] != '')?$_SESSION['UserId']:'-1';
		$username = ($userid == '-1')?'Visitor':$_SESSION['UserName'];
		$sql = 'INSERT INTO ! ( from_user, username, act_time, message) values( ?, ?, ?, ?)';

		$db->query( $sql, array( SHOUTBOX_TABLE, $userid, $username, time(), $text ) );

		$msg = $db->getAll('select id from ! order by act_time', array(SHOUTBOX_TABLE));

		$max_cnt = ($config['shoutbox_msg_maxcnt']>0)?$config['shoutbox_msg_maxcnt']:200;

		if (count($msg) >= $max_cnt) {
			$db->query('delete from ! where id = ?', array(SHOUTBOX_TABLE, $msg[0]['id']) );
		}

		print '|||SBmsgArea|:|'.getSBMsg();
		break;

	case 'ping':
		print '|||SBmsgArea|:|'.getSBMsg();
		break;

	default : return ''; break;
}

function getSBMsg() {
	/* Get Messages for this user */
	global $db, $config;
	$ret = '';

	$msg_cnt = ($config['shoutbox_msg_dispcnt']>0)?$config['shoutbox_msg_dispcnt']:20;


	$sql = 'select * from ! order by act_time desc ';

	if ($_REQUEST['cnt'] == '0' or $_REQUEST['cnt'] == '' or !isset($_REQUEST['cnt'])) $sql.= ' limit 0,'.$msg_cnt;

	$messages = $db->getAll($sql, array(SHOUTBOX_TABLE) );

	if (count($messages) <= 0) return '';

	$ret='<table border="0" cellpadding="0" cellspacing="0" width="90%" ><tr><td height="5"></td></tr>';

	foreach ($messages as $msg) {

		$ret .= '<tr><td width="90%" >';
		if ($msg['from_user'] != '-1') {
			/* Not visitor */
			$ret.='<a href="javascript:popUpScrollWindow2(\''.DOC_ROOT;
			if ($config['enable_mode_rewrite'] == 'Y') {
				/* Mode rewrite SEO friendly tags*/
				if ($config['seo_username'] == 'Y') {
					/* Username tag */
					$ret.=$msg['username'];
				} else {
					$ret.=$msg['from_user'].'.htm';
				}
			} else {
				$ret.= 'showprofile.php?';
				if ($config['seo_username'] == 'Y') {
					$ret.='username='.$msg['username'];
				}else{
					$ret.='id='.$msg['from_user'];
				}
			}
			$ret.="','top',650,600)\">";
			$ret.=$msg['username']."</a>";
		} else{
			$ret.=$msg['username'];
		}
		$ret.='&nbsp;&nbsp;&nbsp;';
		$ret.= date(SHOUTBOX_TIME_FORMAT, $msg['act_time']);
		$ret.='</td></tr><tr><td >'.stripslashes($msg['message']).'</td></tr>';
		$ret.='<tr><td height="4"></td></tr>';
	}
	$ret.='</table>';
	return $ret;
}
?>
