<?php

require_once(dirname(__FILE__).'/init.php');

if (!isset($_REQUEST['a']) || empty($_REQUEST['a']) ) return '';

function is_in_mylist($username) {
	global $db;
	$bdy = $db->getAll('select act from ! where username = ? and ref_username = ? ', array(BUDDY_BAN_TABLE, $_SESSION['UserName'], $username ) );
	return $bdy;
}

/* First update the online users table for this user being online */

$ping_time = time();

$db->query("update ! set is_online=1, last_ping=? WHERE userid=?", array(ONLINE_USERS_TABLE, $ping_time, $_SESSION['UserId'] ) );

$msg_sent = '';

switch (trim($_REQUEST['a'])) {

	case 'sendMsg':
		/* Update message table */
		$sql = 'insert into ! (senderid, recipientid, message, sendtime) values (?, ?, ?, ?)';
		$db->query($sql, array(INSTANT_MESSAGE_TABLE, $_SESSION['UserId'], $_REQUEST['refuid'], str_replace('|amp|','&amp;',$_REQUEST['msg']), $ping_time) );
		$msg = $db->getAll('select id from ! where recipientid = ? order by sendtime', array(INSTANT_MESSAGE_TABLE, $_SESSION['UserId']));
		$max_cnt = ($config['im_totmsg_count']>0)?$config['im_totmsg_count']:100;

		if (count($msg) >= $max_cnt) {
			$db->query('delete from ! where id = ?', array(INSTANT_MESSAGE_TABLE, $msg[0]['id']) );
		}

		$msg_sent = '|||newMsg|:|<textarea onFocus="javascript:clearInput(this);" style="height:100%;width:100%;overflow:auto;border:0;" name="message" id="im_msg" name="im_msg" onkeypress="keyHandler(event);" >Message sent</textarea>';
		print ($msg_sent);
		break;

	case 'ping':
		echo("step1<br>");
		print '|||userList|:|' .
				getUserList() .
				'|||msgArea|:|'.getMsg();
		break;
	default : return ''; break;
}

function getUserList() {

	global $db;

	$sql = 'select usr.id, usr.username from ! as onl, ! as usr, ! as mem where usr.id=onl.userid and onl.is_online=1 and onl.userid <> ! and mem.roleid = usr.level and mem.allowim = ? and usr.allow_viewonline = ? order by usr.username';

	$data = $db->getAll($sql, array(ONLINE_USERS_TABLE, USER_TABLE, MEMBERSHIP_TABLE, $_SESSION['UserId'], '1', '1' ) );

	if (count($data) <= 0) {
		return get_lang('noone_online').
			'<script type="text/javascript">selectedUser(" "," ");</script>';
	}
	$dataok=0;
	$ret='<table border=0 width="100%" cellspacing="0" cellpadding="0">';
	foreach ($data as $dta) {
		$lst = '';
		$bdy = is_in_mylist(trim($dta['username']));

		if (count($bdy)> 0) {
			foreach ($bdy as $ac) {
				if ($ac['act'] == 'H') {
					$lst.='&nbsp;<img src="'.DOC_ROOT.'images/hot_list.gif" height="10" width="10" alt="" align="baseline" title="User is in Hot List" />';
				} elseif ($ac['act'] == 'F') {
					$lst.='&nbsp;<img src="'.DOC_ROOT.'images/buddy_list.gif" height="10" width="10" alt="" align="baseline" title="User is in Buddy List" />';
				} elseif ($ac['act'] == 'B') {
					$lst = 'B';
				}
			}
		}
		if ($lst != 'B') {
			$dataok++;
			$ret.= '<tr><td width="75%" height="6"><a onClick="selectedUser('."'".$dta['username']."','".$dta['id']."'); im_refuid='".$dta['id'] . "';" . '">'.$dta['username'].'</a></td><td width="25%" height="6">'.$lst.'</td></tr>';
		}
	}
	if ($dataok == 0) return get_lang('noone_online').
	'<script type="text/javascript">selectedUser(""); document.getElementById("im_refuid").value="";</script>';

	$ret .= '</table>';

	return $ret;
}

function getMsg() {
	/* Get Messages for this user */
	global $db, $config;
	$ret = '';

	$msg_cnt = ($config['im_dispmsg_count']>0)?$config['im_dispmsg_count']:20;

	$sql = 'select msg.id as msgid, msg.senderid, msg.message, msg.sendtime, msg.pingflag, usr.username as sendername from ! as msg, ! as usr where usr.id = msg.senderid and msg.recipientid = ! order by sendtime desc ';
	$messages = $db->getAll($sql, array(INSTANT_MESSAGE_TABLE, USER_TABLE, $_SESSION['UserId']) );

	if (count($messages) <= 0) return $ret.=get_lang('no_im_msgs');

	$cnt = 0;

	foreach ($messages as $msg) {
		$cnt++;


		if ($cnt <= $msg_cnt) {

			$ret.= '<font class=im_msg><b><a onClick="selectedUser('."'".$msg['sendername']."','".$msg['senderid']."'); im_refuid='".$msg['senderid'] . "';" . '">'.$msg['sendername'].'</a></b>:&nbsp;'.stripslashes($msg['message']).'</font><br />';

		}

	}
	return $ret;
}
?>
