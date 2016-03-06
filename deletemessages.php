<?php
if ( !defined( 'SMARTY_DIR' ) ) {
	include_once( 'init.php' );
}
include('sessioninc.php');


// if group action has been requested by the user
if( isset( $_POST['frm'] ) && $_POST['frm'] == 'frmGroupMail' ){

	$arr = $_POST['txtcheck'];

	if( $_POST['groupaction'] == get_lang('restore') ){

		foreach( $arr as $id){

			$sql = 'UPDATE ! SET flagdelete = ? WHERE id = ?';

			$rs=$db->query( $sql, array( MAILBOX_TABLE, '0', $id ) );

		}
	} elseif ( $_POST['groupaction'] == get_lang('delete') ){

if( is_array( $_POST['txtcheck'] ) AND count( $_POST['txtcheck'] ) ) {
		foreach( $arr as $id){

			$sql = 'DELETE FROM ! WHERE id = ? ';

			$rs=$db->query( $sql, array( MAILBOX_TABLE, $id ) );

		}
	   }
	}

	header( 'location: deletemessages.php?sort='. $_GET['sort'] . '&type=' . $_GET['type'] );
	exit;
}

$sqlselect = 'user.username, user.firstname, user.lastname, msg.*';

$sqlfrom = '! user INNER JOIN ! msg ON user.id = msg.recipientid';

$sqlwhere = '((recipientid = ? and folder = ? ) OR (senderid = ? and folder = ?))  and flagdelete = ? ';

$sqlorder = findSortBy('username');

$sql = 'SELECT ' . $sqlselect .
	' FROM ' . $sqlfrom .
	' WHERE ' . $sqlwhere .
	' ORDER BY '. $sqlorder;

$rs=$db->getAll( $sql, array( USER_TABLE, MAILBOX_TABLE, $_SESSION['UserId'], 'inbox',  $_SESSION['UserId'], 'sent_item', '1' ) );

$data = array();

foreach ( $rs as $row ) {

		$data[] = $row;
}

$t->assign( 'deletemsg', $row['delmsg'] );

$t->assign( 'lang', $lang );

$t->assign( 'sort_type', checkSortType( $_GET['type'] ) );

$t->assign( 'data', $data );

$t->assign('rendered_page', $t->fetch('deletemessages.tpl') );

$t->display ( 'index.tpl' );

?>