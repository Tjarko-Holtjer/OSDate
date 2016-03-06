<?php

if ( !defined( 'SMARTY_DIR' ) ) {
	include_once( 'init.php' );
}

include('sessioninc.php');


// if group action has been requested by the user
if( $_POST['frm'] == 'frmGroupMail' ){

	$arr = $_POST['txtcheck'];

	if( $_POST['groupaction'] == get_lang('delete') ){
if( is_array( $_POST['txtcheck'] ) AND count( $_POST['txtcheck'] ) ) {

		foreach( $arr as $id){

			$sql = 'UPDATE ! SET flagdelete = ? WHERE id = ? and senderid = ? and folder = ?';

			$db->query( $sql, array( MAILBOX_TABLE, '1', $id, $_SESSION['UserId'], 'sent_item' ) );
		}
	 }
	}

	header('location: sentmessages.php?sort='. $_GET['sort'] . '&type=' . $_GET['type'] );
	exit;

}

$sqlselect = 'user.username, user.firstname, user.lastname, msg.*';

$sqlfrom = ' ! user INNER JOIN ! msg ON user.id=msg.recipientid ';

$sqlwhere = ' msg.senderid= ? and flagdelete=? and folder=?';

$sqlorder = findSortBy('username');

$sql = 'SELECT ' . $sqlselect .
	' FROM ' . $sqlfrom .
	' WHERE ' . $sqlwhere .
	' ORDER BY '. $sqlorder;

$rs=$db->getAll( $sql, array(USER_TABLE, MAILBOX_TABLE, $_SESSION['UserId'], '0', 'sent_item') );

$data = array();

foreach ( $rs as $row ) {
		$data[] = $row;
}
//count number of mails in trans can
$sql = 'SELECT COUNT(*) as delmsg FROM ! WHERE ((recipientid=? and folder=?) OR (senderid=? and folder=?))  and flagdelete=?';

$row=$db->getRow( $sql, array(MAILBOX_TABLE, $_SESSION['UserId'], 'inbox', $_SESSION['UserId'], 'sent_item', '1' ) );

$t->assign( 'deletemsg', $row['delmsg'] );

$t->assign( 'lang', $lang );

$t->assign( 'sort_type', checkSortType( $_GET['type'] ) );

$t->assign( 'data', $data );

$t->assign('rendered_page', $t->fetch('sentmessages.tpl') );

$t->display ( 'index.tpl' );

?>