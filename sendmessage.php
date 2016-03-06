<?php
if ( !defined( 'SMARTY_DIR' ) ) {
	include_once( 'init.php' );
}
include('sessioninc.php');


if( $_POST['frm'] == 'frmCompose' ){

	if ( $_SESSION['UserId'] != '' ) {

		$sql = 'select id, firstname, lastname, email from ! where username = ?';

		$row = $db->getRow( $sql, array( USER_TABLE, $_POST['txtusername'] ) );

		if( $row['id'] ) {

			$sql = 'insert into ! ( senderid, recipientid, subject, message, sendtime ) values ( ?, ?, ?, ?, ? )';

			$db->query( $sql, array( MAILBOX_TABLE, $_SESSION['UserId'], $row['id'], strip_tags($_POST['txtsubject']), strip_tags($_POST['txtmessage']), time() ) );

			$sql = 'insert into ! ( senderid, recipientid, subject, message, sendtime, folder ) values ( ?, ?, ?, ?, ?, ? )';

			$db->query( $sql, array( MAILBOX_TABLE, $_SESSION['UserId'], $row['id'], strip_tags($_POST['txtsubject']), strip_tags($_POST['txtmessage']), time(), 'sent_item' ) );

			if ($config['letter_messagereceived'] == 'Y' && ($config['nomail_for_onlineuser'] != 'Y' or ($config['nomail_for_onlineuser'] == 'Y' && !getOnlineStats($_POST['txtrecipient']) )) ) {

			/* Send email about the received message to the receiver */

				$sendername = $db->getOne('select username from ! where id = ?', array(USER_TABLE, $_SESSION['UserId']) );

				$Subject = $config['site_name'].' - Intimation mail';

				$From = $config['admin_email'];

				$To = $row['email'];

				$message = get_lang('message_received');

				$message = str_replace('#RealName#', $row['firstname'].' '.$row['lastname'] ,$message);

				$message = str_replace('#SenderName#', $sendername, $message);

				$message = str_replace('#siteName#', $config['site_name'], $message);

				$success = mailSender($From, $To, $To, $Subject, $message);

			}
		} else {
			$err = INVALID_USERNAME;	// change to constant later
			$t->assign( 'err', $err );
		}

	} else {

		$err = NOT_LOGGED_IN;	// change to constant later
		$t->assign( 'err', $err );
	}

	$t->assign( 'lang', $lang );

	$t->assign('rendered_page', $t->fetch('sendmessage.tpl') );

	$t->display( 'index.tpl' );

} else {

	$sql = 'select count(*) from ! where ( ( recipientid = ? and folder = ? ) or ( senderid = ? and folder = ? ) ) and flagdelete = ?';

	$delmsg = $db->getOne( $sql, array( MAILBOX_TABLE, $_SESSION['UserId'], 'inbox', $_SESSION['UserId'], 'send_item', 1 ) );

	$t->assign( 'deletemsg', $row['delmsg'] );

	$t->assign( 'lang', $lang );

	$t->assign('rendered_page', $t->fetch('sendmessage.tpl') );

	$t->display( 'index.tpl' );
}

?>