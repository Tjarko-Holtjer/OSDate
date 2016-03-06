<?php

if ( !defined( 'SMARTY_DIR' ) ) {
	include_once( 'init.php' );
}

include('sessioninc.php');


$rm_days = ($_SESSION['security']['message_keep_days'] > 0)? $_SESSION['security']['message_keep_days']+1 : $config['message_days_old']+1;

$allowed_count = ($_SESSION['security']['message_keep_cnt'] > 0)? $_SESSION['security']['message_keep_cnt'] : $config['message_count'];

$removetime = time() - ($rm_days * 24*60*60);

$warntime = $removetime + ($config['message_warn_days'] * (24*60*60));

$db->query('delete from ! where sendtime < ? and owner = ?', array(MAILBOX_TABLE, $removetime, $_SESSION['UserId']) );

$selflag = (isset($_REQUEST['selflag'])&&$_REQUEST['selflag'] != '') ? $_REQUEST['selflag'] : 'A';

$folder =  (isset($_REQUEST['folder'])) ? $_REQUEST['folder'] : 'inbox';

if (isset($_REQUEST['msgaction']) && $_REQUEST['msgaction'] != '') {

	$msgaction = $_REQUEST['msgaction'];

	switch ($msgaction) {

		case get_lang('delete'):

			$sql = 'update ! set flagdelete = ?, folder=? where id = ? and owner=?';

			$db->query( $sql, array( MAILBOX_TABLE, 1, 'trashcan', $_REQUEST['id'], $_SESSION['UserId'] ) );

			$_REQUEST['view'] = 0;

			$grpmsg='msg_deleted';

			break;

		case get_lang('flag'):

			$sql = 'update ! set flag = ? where id = ? and owner=?';

			$db->query( $sql, array( MAILBOX_TABLE, 1, $_REQUEST['id'] , $_SESSION['UserId'])  );

			$grpmsg='msg_flagged';

			break;

		case get_lang('unflag'):

			$sql = 'update ! set flag = ? where id = ? and owner = ?';

			$db->query( $sql, array( MAILBOX_TABLE, 0, $_REQUEST['id'], $_SESSION['UserId'] ) );

			$grpmsg='msg_unflagged';

			break;
	}
}

if( $_REQUEST['frm'] == 'frmGroupMail' ) {

	$arr = $_REQUEST['txtcheck'];

	$grpmsg = '';

	if( is_array( $_REQUEST['txtcheck'] ) AND count( $_REQUEST['txtcheck'] ) > 0 ) {

		if( $_REQUEST['groupaction'] == get_lang('delete') ) {

			foreach( $arr as $id) {

				if ($folder == 'trashcan') {
					/* remove this message from system */
					$db->query('delete from ! where id = ? and owner=?', array( MAILBOX_TABLE, $id, $_SESSION['UserId'] ) );

				} else {
					/* Mark as deleted and move to trashcan folder */
					$sql = 'update ! set flagdelete = ?, folder=? where id = ? and owner=?';

					$db->query( $sql, array( MAILBOX_TABLE, 1, 'trashcan', $id, $_SESSION['UserId'] ) );
				}
			}

			$grpmsg='sel_msgs_deleted';

		} elseif( $_REQUEST['groupaction'] == get_lang('undelete') ) {

			foreach( $arr as $id ) {

				$sql = 'update ! set flagdelete = ?, folder = ? where id = ? and owner=?';

				$msg = $db->getRow('select owner, senderid, recipientid from ! where id = ? and owner=?', array(MAILBOX_TABLE, $id, $_SESSION['UserId']) );

				if ($msg['senderid'] == $msg['owner']) {

					$fldr = 'sent';

				} else {

					$fldr = 'inbox';

				}

				$db->query( $sql, array( MAILBOX_TABLE, 0, $fldr, $id, $_SESSION['UserId'] ) );
			}

			$grpmsg='sel_msgs_undeleted';

		} elseif( $_REQUEST['groupaction'] == get_lang('read') ) {

			foreach( $arr as $id ) {

				$sql = 'update ! set flagread = ? where id = ? and owner=?';

				$db->query( $sql, array( MAILBOX_TABLE, 1, $id, $_SESSION['UserId'] ) );
			}

			$grpmsg='sel_msgs_read';

		} elseif( $_REQUEST['groupaction'] == get_lang('unread') ) {

			foreach( $arr as $id ) {

				$sql = 'update ! set flagread = ? where id = ? and owner=?';

				$db->query( $sql, array( MAILBOX_TABLE, 0, $id, $_SESSION['UserId'] ) );
			}

			$grpmsg='sel_msgs_unread';

		} elseif( $_REQUEST['groupaction'] == get_lang('flag') ) {

			foreach( $arr as $id ) {

				$sql = 'update ! set flag = ? where id = ? and owner=? ';

				$db->query( $sql, array( MAILBOX_TABLE, 1, $id, $_SESSION['UserId'] ) );
			}

			$grpmsg='sel_msgs_flagged';

		} elseif( $_REQUEST['groupaction'] == get_lang('unflag') ) {

			foreach( $arr as $id ) {

				$sql = 'update ! set flag = ? where id = ? and owner=? ';

				$db->query( $sql, array( MAILBOX_TABLE, 0, $id, $_SESSION['UserId'] ) );
			}

			$grpmsg='sel_msgs_unflagged';

		}
	}

}

$msgcounts = $db->getAll('select folder, count(id) as cnt from ! where owner = ? group by folder', array(MAILBOX_TABLE, $_SESSION['UserId']) );

$msg_counts = array();

$total_count = 0;
foreach ($msgcounts as $msg) {
	$total_count += $msg['cnt'];
	$msg_counts[$msg['folder']] = $msg['cnt'];
}

$t->assign('msg_counts', $msg_counts);
$t->assign('total_count', $total_count);
$t->assign('allowed_count', $allowed_count);
$t->assign('allowed_days', $rm_days - 1);

$my_timezone = $db->getOne('select timezone from ! where id = ?', array( USER_TABLE, $_SESSION['UserId']) );

if ($_REQUEST['replied'] == '1') {

	$grpmsg='replied';

}
if (isset($_REQUEST['view']) and $_REQUEST['view'] == 1 and $_REQUEST['id'] != '' ) {

	/* View one message */
	$t->assign('view', '1');

	/* Get the message */

	$data = $db->getRow('select * from ! where id = ? and owner=?', array( MAILBOX_TABLE, $_REQUEST['id'], $_SESSION['UserId'] ) );

	/* Identify the userid for from/to addressing
		and set the fldr accordingly
	*/

	if ($folder == 'inbox' or ( $data['recipientid'] == $data['owner'] and $data['folder'] == 'trashcan')) {
		$data['refuid'] = $usrid = $data['senderid'];
		$data['fldr'] = 'inbox';

	} elseif ( $folder == 'sent' or ( $data['senderid'] == $data['owner'] and $data['folder'] == 'trashcan')) {

		$data['refuid'] = $usrid = $data['recipientid'];
		$data['fldr'] = 'sent';

	}

	/* Get the user record for username and timezone */

	$usrsql = 'select username, firstname, email from ! where id = ?';

	$usrrec = $db->getRow($usrsql, array(USER_TABLE, $usrid));

	$t->assign('piccnt', $db->getOne('select count(*) from ! where userid = ? and album_id is null', array(USER_SNAP_TABLE, $usrid) ) );

	/* Now update read flag */
	$data['converted_time'] = round($data['sendtime'] + ($my_timezone * 3600) );

	$data['username'] = $usrrec['username'];

	/* Now mark the message as READ */

	$db->query('update ! set flagread = ? where id = ? and owner=?', array(MAILBOX_TABLE, 1, $_REQUEST['id'], $_SESSION['UserId']) );

/* ow update the sent box of the sender also for the read flag. */
	$db->query('update ! set flagread=? where owner=? and recipientid = ? and subject = ? and message = ? and folder = ?', array(MAILBOX_TABLE,1, $data['senderid'],$data['recipientid'], $data['subject'], $data['message'], 'sent') ) ;

	$recipient_choice = $db->getOne('select choice_value from ! where userid=? and choice_name=?', array(USER_CHOICES_TABLE, $usrid, 'email_message_read') );

	if ($recipient_choice == '1' or $recipient_choice == '' or !isset($recipient_choice) ) {

		if ($data['notifysender'] == '1' && $data['flagread'] != '1') {
			/* Intimate the sender about message read status */

			$msg = get_lang('message_read', MAIL_FORMAT);

			$msg = str_replace('#FirstName#',$usrrec['firstname'],$msg);

			$msg = str_replace('#RecipientName#',$_SESSION['UserName'],$msg);

			$From = $config['admin_email'];

			$To = $usrrec['email'];

			$Subject = str_replace('#RecipientName#',$_SESSION['UserName'],get_lang('message_read_sub')) ;

			if (MAIL_FORMAT == 'html') {

				$sql = 'SELECT *, floor((to_days(curdate())-to_days(birth_date))/365.25)  as age	FROM ! WHERE id = ?';

				$t->assign('item', $db->getRow($sql, array( USER_TABLE, $_SESSION['UserId'] ) ));

				$msg = str_replace('#smallProfile#',  $t->fetch('profile_for_html_mail.tpl'), $msg);

			}

			$success = mailSender($From, $To, $To, $Subject, $msg);

		}
	}

} else {

	$t->assign('view','0');

	$sql = 'select msg.*, usr.username, usr.timezone, if(msg.senderid=msg.owner, msg.recipientid, msg.senderid) as refuid from ! as msg, ! as usr where msg.owner = ? and msg.folder = ? and usr.id = if(msg.senderid=msg.owner, msg.recipientid, msg.senderid) ';

	/* Flagged or unflagged or all messages */
	if ($selflag == 'F') {

		$sql .= ' and flag = 1 ';

	} elseif ($selflag == 'U') {

		$sql .= ' and flag <> 1 ';
	}

	$sql .= ' order by '.findSortBy( 'username' );

	$msgs = $db->getAll( $sql, array( MAILBOX_TABLE, USER_TABLE, $_SESSION['UserId'], $folder ) );
	/* Now collect userid and other details */
	$data = array();
	if (count($msgs) > 0) {
		foreach ($msgs as $msg) {
			$msg['converted_time'] = round($msg['sendtime'] + ($my_timezone * 3600) );

			/* calculate the message deletin time. Allow 1 day more for hours and seconds issues */

			if ($msg['sendtime'] < $warntime and $msg['sendtime'] >  $removetime) $msg['warnflag'] = '1';
			$msg['message'] = nl2br($msg['message']);

			$data[]=$msg;
		}
	}
}
$t->assign('grpmsg',$grpmsg);
$t->assign('selflag', $selflag);
$t->assign('folder', $folder);
$t->assign( 'lang', $lang );
$t->assign( 'sort_type', checkSortType( $_GET['type'] ) );
$t->assign( 'data', $data );

$t->assign('rendered_page', $t->fetch('mailmessages.tpl') );

$t->display ( 'index.tpl' );

?>