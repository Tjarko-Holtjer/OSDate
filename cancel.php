<?php
if ( !defined( 'SMARTY_DIR' ) ) {
	include_once( 'init.php' );
}

include( 'sessioninc.php' );


if ($_POST['action'] == '' or !$_POST['action']) {

	$t->assign('step','1');

} else {

	if ($_POST['action'] == get_lang('cancel_opt01')) {
	/* Cancel membership */

		$username = $db->getOne('select username from ! where id = ?',array( USER_TABLE, $_SESSION['UserId'] ) ) ;

		$sql = 'update ! set status=?, active=?, regdate = ? where id = ?';

		$db->query($sql, array( USER_TABLE, 'cancel', 0, time(), $_SESSION['UserId'] ) );

		if ($config['forum_installed'] != '' && $config['forum_installed'] != 'None') {

			forum_cancel($username);
		}
		$sql = 'DELETE FROM ! WHERE userid = ?';

		$db->query( $sql, array( ONLINE_USERS_TABLE, $_SESSION['UserId'] ) );

		/* Delete from Shoutbox and Entries */
		$sql = 'DELETE FROM ! WHERE from_user = ?';

		$db->query( $sql, array( SHOUTBOX_TABLE, $_SESSION['UserId'] ) );

		session_destroy();

		session_start();

		$t->assign('step','2');

	} else {

		$t->assign('step','3');

	}

}

$t->assign('lang',$lang);

$t->assign( 'rendered_page', $t->fetch( 'cancel.tpl' ) );

$t->display('index.tpl');

?>