<?php
if ( !defined( 'SMARTY_DIR' ) ) {
	include_once( 'init.php' );
}

if ( isset( $_GET['txtconfcode'] ) && $_GET['txtconfcode'] ) {
	$confcode = $_GET['txtconfcode'];
} else {
	$confcode = $_GET['confcode'];
}

$sql = 'SELECT id, username, firstname, lastname, level FROM ! WHERE  actkey = ?';

$row = $db->getRow( $sql, array( USER_TABLE, $confcode ) );

if ( $row ) {

	$activedays = $db->getOne('select activedays from ! where roleid = ?', array( MEMBERSHIP_TABLE, $row['level'] ) );

	if ($config['default_active_status'] == 'Y') {

		$levelend = strtotime("+$activedays day",time());
		// $status = get_lang('status_enum','active');
		$status = 'active';

	} else {

		// $status = get_lang('status_enum','approval');
		$status = 'approval';

	}

	$sql = 'UPDATE ! SET active=?, status = ? , levelend = ? , actkey=? WHERE id = ?';

	$db->query( $sql, array( USER_TABLE, '1', $status, $levelend, 'Confirmed', $row['id'] ) );

	$sql = 'DELETE FROM ! WHERE userid = ?';

	$db->query( $sql, array( ONLINE_USERS_TABLE, $row['id'] ) );

	$sql = 'INSERT INTO ! ( userid, lastactivitytime , session_id ) VALUES ( ?, ?, ?)';

	$db->query( $sql, array( ONLINE_USERS_TABLE, $row['id'], time(), session_id() ) );

	$sql = 'UPDATE ! SET lastvisit = ? WHERE id = ?';

	$db->query( $sql, array( USER_TABLE, time(), $row['id'] ) );

/*
if ($config['forum_installed'] != '' && $config['forum_installed'] != 'None') {



	    forum_completereg($row['username']);

	}
*/

	session_destroy();

	header('location: index.php?page=login&err='.REGN_COMPLETED);

} else {

	header( 'location: confirmreg.php?errid='.INVALID_ACTIVATION_CODE );

}

exit;
?>