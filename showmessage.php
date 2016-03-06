<?php

if ( !defined( 'SMARTY_DIR' ) ) {
	include_once( 'init.php' );
}

$msg = $_GET['messages'];

if( $msg != 'sent'  && $msg != 'inbox' ){

	// put in template later

	echo 'Parameters not valid';
	exit;
}

$sqlselect = 'msg.id, user.username, user.firstname, user.lastname, msg.senderid, msg.subject, msg.message, msg.flagread, msg.sendtime, msg.flagdelete, msg.recipientid, msg.notifysender ';

$sqlfrom = '! user INNER JOIN ! msg ON user.id = msg.senderid';

if ( $msg == 'sent' ) {
	$sqlfrom = '! user INNER JOIN ! msg ON user.id = msg.recipientid';
}

$sqlwhere = 'msg.id = ?';

$sql = 'SELECT ' . $sqlselect .
	' FROM ' . $sqlfrom .
	' WHERE ' . $sqlwhere;

$rs = $db->query( $sql, array( USER_TABLE, MAILBOX_TABLE, $_GET['id'] ));

$err = 0;

if ( $rs->numRows() > 0 ) {
	$row = $rs->fetchRow();
} else {
	$err = NO_MESSAGE;
}

if ( $msg == 'inbox' && $row['recipientid'] != $_SESSION['UserId'] ) {
	$err = NOT_ACTIVE;
} elseif ( $msg == 'sent' && $row['senderid'] != $_SESSION['UserId'] ) {
	$err= NOT_ACTIVE;
}

if ( $err != 0 ) {

	$t->assign( 'error', get_lang('errormsgs',$err) );

} else {

	if ( $msg != 'sent' ) {

		$sql = 'UPDATE ! SET flagread = ? WHERE id = ?';

		$db->query( $sql, array( MAILBOX_TABLE, 1, $_GET['id'] ) );

		if ($row['notifysender'] == 1) {

			$senderid = $row['senderid'];
			$recipientid = $row['recipientid'];

			$sql = 'select * from ! where id = ?';

			$row2 = $db->getRow( $sql, array( USER_TABLE, $senderid ) );

			$recipientname = $db->getOne('select username from ! where id = ?', array(USER_TABLE, $recipientid) );

			$Subject = $config['site_name'].' - Mail read';

			$From = $config['admin_email'];

			$To = $row2['email'];

			$message = get_lang('message_read');

			$message = str_replace('#FirstName#', $row2['firstname'] ,$message);

			$message = str_replace('#RecipientName#', $recipientname, $message);

			$success = mailSender($From, $To, $To, $Subject, $message);

		}
	}
	$data = $row;
}

$t->assign( 'data', $data );

$t->assign( 'lang', $lang );

$t->assign('rendered_page', $t->fetch('showmessage.tpl') );


$t->display ( 'index.tpl' );

?>